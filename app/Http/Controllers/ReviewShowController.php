<?php

namespace App\Http\Controllers;

use App\Models\Review;

class ReviewShowcaseController extends Controller
{
    public function index()
    {
        $reviews = Review::query()
            ->where('rating', '>=', 4)
            ->with([
                'clientProfile.user:id,name',
                'providerProfile:id,business_name,category_id',
                'providerProfile.category:id,name',
            ])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($r) => [
                'id'      => $r->id,
                'rating'  => $r->rating,
                'comment' => $r->comment,
                'client'  => $r->clientProfile?->user?->name ?? 'Anonymous',
                'provider' => [
                    'id'            => $r->providerProfile?->id,
                    'business_name' => $r->providerProfile?->business_name,
                    'category'      => $r->providerProfile?->category?->name,
                ],
            ]);

        return response()->json($reviews);
    }
}
