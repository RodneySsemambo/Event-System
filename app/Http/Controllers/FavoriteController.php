<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user()->clientProfile;

        // If the user is a provider, they don't have a client profile
        if (!$client) {
            return response()->json([]);
        }

        $favs = $client->favorites()->with('providerProfile.category')->get();
        return response()->json($favs);
    }
    public function toggle(Request $request)
    {
        $validated = $request->validate(['provider_profile_id' => 'required|exists:provider_profiles,id']);

        $client = $request->user()->clientProfile;

        // If the user is a provider, they cannot save favorites
        if (!$client) {
            return response()->json(['message' => 'Only clients can save favorites.'], 403);
        }

        $fav = $client->favorites()->where('provider_profile_id', $validated['provider_profile_id'])->first();

        if ($fav) {
            $fav->delete();
            return response()->json(['status' => 'removed']);
        }

        $client->favorites()->create($validated);
        return response()->json(['status' => 'added']);
    }
}
