<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ClientProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * FR-01: Register as Client or Provider, capturing role-specific
     * profile fields at signup and creating a minimal profile row so
     * later profile-editing screens  have something to update.
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => 'active',
            ]);

            if ($data['role'] === 'client') {
                ClientProfile::create([
                    'user_id' => $user->id,
                    'phone' => $data['phone'] ?? null,
                    'default_location' => $data['default_location'] ?? null,
                ]);
            }

            if ($data['role'] === 'provider') {
                ProviderProfile::create([
                    'user_id' => $user->id,
                    'category_id' => $data['category_id'],
                    'business_name' => $data['business_name'],
                    'location' => $data['location'],
                    'verification_status' => 'unverified',
                    'availability_status' => 'available',
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload($user),
        ], 201);
    }

    /**
     * FR-02: Authenticate via session (Sanctum SPA mode — the React app
     * must have already hit GET /sanctum/csrf-cookie before this call).
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, remember: true)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload(Auth::user()),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * Returns the authenticated user + their role-specific profile,
     * used by the React app on boot to restore session state.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing([
            'clientProfile',
            'providerProfile.category',
            'providerProfile.portfolioItems',
        ]);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'client_profile' => $user->clientProfile,
            'provider_profile' => $user->providerProfile,
        ];
    }
}
