<?php

namespace App\Http\Controllers;

use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(private WithdrawalService $withdrawals) {}

    public function index(Request $request)
    {
        $provider = $request->user()->providerProfile;

        if (! $provider) {
            return response()->json(['message' => 'Only providers can view earnings.'], 403);
        }

        $balance = $this->withdrawals->balance($provider);

        return response()->json([
            'balance'   => $balance,
            'history'   => $provider->withdrawals()->latest()->take(20)->get(),
            'minimum'   => WithdrawalService::MINIMUM,
        ]);
    }

    public function store(Request $request)
    {
        $provider = $request->user()->providerProfile;

        if (! $provider) {
            return response()->json(['message' => 'Only providers can withdraw.'], 403);
        }

        $validated = $request->validate([
            'amount'       => ['required', 'numeric', 'min:' . WithdrawalService::MINIMUM],
            'phone_number' => ['required', 'string', 'regex:/^\+256[0-9]{9}$/'],
        ]);

        $withdrawal = $this->withdrawals->request(
            $provider,
            (float) $validated['amount'],
            $validated['phone_number'],
        );

        return response()->json($withdrawal, 201);
    }
}
