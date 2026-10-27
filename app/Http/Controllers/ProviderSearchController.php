<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * GET /api/search?category_id=&location=&price_min=&price_max=&keyword=&page=
 *
 * Only verified providers appear here (FR-14) — unverified providers
 * still have a working profile URL (see ProviderProfileController::show),
 * they just don't surface through search/browse until an admin approves
 * them. This is the actual enforcement point for "verification gates
 * visibility", not the profile page itself.
 */

class ProviderSearchController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'keyword' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:rating,newest,price_low,price_high'],
        ]);

        $query = ProviderProfile::query()
            ->verified()
            ->withAvg('reviews', 'rating')
            ->with('category')
            ->withCount('reviews');

        if (! empty($validated['category_id'])) {
            $query->inCategory($validated['category_id']);
        }

        if (! empty($validated['location'])) {
            $query->inLocation($validated['location']);
        }

        if (isset($validated['price_min']) || isset($validated['price_max'])) {
            $query->priceBetween($validated['price_min'] ?? null, $validated['price_max'] ?? null);
        }

        if (! empty($validated['keyword'])) {
            $query->keyword($validated['keyword']);
        }

        match ($validated['sort'] ?? 'rating') {
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            'newest' => $query->orderByDesc('created_at'),
            'price_low' => $query->orderBy('price_range_min'),
            'price_high' => $query->orderByDesc('price_range_max'),
        };

        // Cover photo only — full portfolio is loaded on the detail page,
        // keeping the search response light (NFR-01: search under 2s).
        $providers = $query->paginate(12)->through(function ($provider) {
            return
                [
                    'id' => $provider->id,
                    'business_name' => $provider->business_name,
                    'location' => $provider->location,
                    'category' => $provider->category,
                    'price_range_min' => $provider->price_range_min,
                    'price_range_max' => $provider->price_range_max,
                    'availability_status' => $provider->availability_status,
                    'profile_photo_path' => $provider->profile_photo_path,
                    'rating_avg' => $provider->reviews_avg_rating ? round($provider->reviews_avg_rating, 1) : null,
                    'rating_count' => $provider->reviews_count,
                    'verification_status' => $provider->verification_status
                ];
        });

        return response()->json($providers);
    }
}
