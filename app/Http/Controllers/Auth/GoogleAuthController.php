<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // stateless() would break Sanctum session auth — keep state on
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $frontend = config('app.frontend_url', 'http://localhost:5173');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth Failed', [
                'message' => $e->getMessage(),
                'class'   => get_class($e)
            ]);
            return redirect("{$frontend}/login?error=google_failed");
        }
        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            // First-time Google user — create a client account
            $user = User::create([
                'google_id' => $googleUser->getId(),
                'name'      => $googleUser->getName() ?? 'Google user',
                'email'     => $googleUser->getEmail(),
                'avatar'    => $googleUser->getAvatar(),
                'password'  => null,
                'role'      => 'client',
                'status'    => 'active',
            ]);

            ClientProfile::create([
                'user_id'          => $user->id,
                'phone'            => null,
                'default_location' => null,
            ]);
        } elseif (! $user->google_id) {
            // Existing email/password account — link it to Google
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar() ?? $user->avatar,
            ]);
        }

        // This is the key line: log them into the SAME session Sanctum uses
        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect("{$frontend}/dashboard");
    }
}
