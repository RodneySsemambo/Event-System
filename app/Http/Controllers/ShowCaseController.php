<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;

class ShowcaseController extends Controller
{
    public function index()
    {
        try {
            $items = PortfolioItem::query()
                ->whereHas('providerProfile', function ($q) {
                    $q->where('verification_status', 'verified');
                })
                ->with(['providerProfile.category'])
                ->latest()
                ->take(12)
                ->get()
                ->map(function ($item) {
                    $profile = $item->providerProfile;
                    return [
                        'id'         => $item->id,
                        'image_path' => $item->image_path,
                        'media_type' => $item->media_type ?? 'image',
                        'caption'    => $item->caption,
                        'provider'   => [
                            'id'            => $profile?->id,
                            'business_name' => $profile?->business_name ?? 'Provider',
                            'location'      => $profile?->location ?? 'Uganda',
                            'category'      => $profile?->category?->name ?? 'Service',
                        ],
                    ];
                });

            return response()->json($items);
        } catch (\Throwable $e) {
            \Log::error('Showcase failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
