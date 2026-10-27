<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;

class ShowcaseController extends Controller
{
    public function index()
    {
        $items = PortfolioItem::query()
            ->whereHas('providerProfile', fn($q) => $q->verified())
            ->with([
                'providerProfile:id,business_name,category_id,location',
                'providerProfile.category:id,name',
            ])
            ->latest()
            ->take(12)
            ->get()
            ->map(fn($item) => [
                'id'         => $item->id,
                'image_path' => $item->image_path,
                'media_type' => $item->media_type ?? 'image',
                'caption'    => $item->caption,
                'provider'   => [
                    'id'            => $item->providerProfile->id,
                    'business_name' => $item->providerProfile->business_name,
                    'location'      => $item->providerProfile->location,
                    'category'      => $item->providerProfile->category?->name,
                ],
            ]);

        return response()->json($items);
    }
}
