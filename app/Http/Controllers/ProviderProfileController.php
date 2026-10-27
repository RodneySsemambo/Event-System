<?php

namespace App\Http\Controllers;

use App\Http\Requests\Provider\UpdateProviderProfileRequest;
use App\Models\ProviderProfile;
use App\Services\ProviderProfileService;
use Illuminate\Http\JsonResponse;

class ProviderProfileController extends Controller
{
    public function __construct(private ProviderProfileService $profiles) {}

    public function show(ProviderProfile $provider): JsonResponse
    {
        $provider->load([
            'category',
            'portfolioItems',
            'reviews' => fn($q) => $q->latest()->with('clientProfile.user:id,name'),
        ]);

        $provider->loadAvg('reviews', 'rating');
        $provider->loadCount('reviews');

        return response()->json($provider);
    }

    public function update(UpdateProviderProfileRequest $request, ProviderProfile $provider): JsonResponse
    {
        $this->authorize('update', $provider);

        $data = collect($request->validated())
            ->except(['profile_photo', 'banner'])
            ->toArray();

        $updated = $this->profiles->update(
            $provider,
            $data,
            $request->file('profile_photo'),
            $request->file('banner'),
        );

        return response()->json($updated);
    }
}
