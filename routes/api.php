<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PortfolioItemController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderSearchController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\ReviewShowcaseController;
use App\Http\Controllers\WithdrawalController;

use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

// --- Public / guest ------------------------------------------------------
Route::get('/inspect-bookings', function (\Illuminate\Http\Request $request) {
    if ($request->query('secret') !== env('QUEUE_SECRET')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $bookings = \App\Models\Booking::with('payment')
        ->latest()
        ->take(10)
        ->get()
        ->map(function ($b) {
            return [
                'id'                => $b->id,
                'status'            => $b->status,
                'quoted_amount'     => $b->quoted_amount,
                'provider_payout'   => $b->provider_payout,
                'payment_status'    => $b->payment?->status,
                'payment_reference' => $b->payment?->marzpay_reference,
                'created_at'        => $b->created_at,
            ];
        });

    return response()->json([
        'total_bookings'      => \App\Models\Booking::count(),
        'completed_bookings'  => \App\Models\Booking::where('status', 'completed')->count(),
        'null_payout_count'   => \App\Models\Booking::where('status', 'completed')->whereNull('provider_payout')->count(),
        'bookings'            => $bookings,
    ]);
});
Route::get('/ping', function () {
    return response()->json(['pong' => true, 'time' => now()]);
});

Route::get('/run-backfill', function (\Illuminate\Http\Request $request) {
    Log::info('run-backfill hit', ['has_secret' => $request->has('secret')]);

    if ($request->query('secret') !== env('QUEUE_SECRET')) {
        return response()->json([
            'error'  => 'Unauthorized',
            'reason' => 'Secret mismatch or QUEUE_SECRET is not set on Render',
            'got'    => $request->query('secret'),
        ], 403);
    }

    try {
        $count = 0;
        \App\Models\Booking::where('status', 'completed')
            ->whereNull('provider_payout')
            ->each(function ($b) use (&$count) {
                $price = (float) ($b->quoted_amount ?? $b->providerProfile?->price_range_min ?? 0);
                $commission = round($price * 0.10, 2);
                $b->update([
                    'quoted_amount'     => $price,
                    'commission_amount' => $commission,
                    'provider_payout'   => round($price - $commission, 2),
                ]);
                $count++;
            });

        return response()->json(['backfilled' => $count, 'time' => now()]);
    } catch (\Throwable $e) {
        Log::error('run-backfill failed', [
            'error' => $e->getMessage(),
            'class' => get_class($e),
        ]);
        return response()->json([
            'error' => $e->getMessage(),
            'class' => get_class($e),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
        ], 500);
    }
});

Route::get('/run-queue', function (\Illuminate\Http\Request $request) {
    if ($request->query('secret') !== env('QUEUE_SECRET')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
        return response()->json([
            'status'  => 'processed',
            'output'  => \Illuminate\Support\Facades\Artisan::output(),
            'time'    => now(),
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/check-jobs', function (\Illuminate\Http\Request $request) {
    if ($request->query('secret') !== env('QUEUE_SECRET')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    return response()->json([
        'pending' => DB::table('jobs')->count(),
        'failed'  => DB::table('failed_jobs')->count(),
        'recent'  => DB::table('failed_jobs')->latest()->take(3)->get(['exception', 'failed_at']),
    ]);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/search', [ProviderSearchController::class, 'index']);
Route::get('/providers/{provider}', [ProviderProfileController::class, 'show']);
Route::get('/showcase', [ShowcaseController::class, 'index']);
Route::get('/reviews/showcase', [ReviewShowcaseController::class, 'index']);

Route::post('/webhooks/at-delivery', function (\Illuminate\Http\Request $request) {
    Log::info('AT delivery report', $request->all());
    return response()->json(['ok' => true]);
});
Route::post('/webhooks/nylonpay', [PaymentWebhookController::class, 'handle']);
Route::get('/health', fn() => response()->json(['status' => 'ok']));
// --- Authenticated (any role) ---------------------------------------------

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::middleware('role:provider')->group(function () {
        Route::get('/withdrawals', [WithdrawalController::class, 'index']);
        Route::post('/withdrawals', [WithdrawalController::class, 'store']);
    });
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);

    // marzpay:health-check
    Route::get('/system/marzpay-status', function () {
        return response()->json([
            'status'      => \Illuminate\Support\Facades\Cache::get('marzpay:health:last-state', 'unknown'),
            'checked_at'  => \Illuminate\Support\Facades\Cache::get('marzpay:health:last-state', 'never'),
        ]);
    });
    // Bookings: creation is client-only, everything else is guarded by
    // BookingPolicy (per-action, not per-route — status update/cancel/
    // complete each have different ownership + state rules).
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store']);

    // client triggers MarzPay collection once booking is accepted
    Route::post('/bookings/{booking}/pay', [PaymentController::class, 'pay']);

    Route::middleware('role:client')->group(function () {
        Route::post('/bookings', [BookingController::class, 'store']);
    });

    // Provider-only: own profile + portfolio management (Week 2, FR-03/FR-04)
    Route::middleware('role:provider')->group(function () {
        Route::put('/providers/{provider}', [ProviderProfileController::class, 'update']);
        Route::post('/providers/{provider}/portfolio', [PortfolioItemController::class, 'store']);
        Route::post('/providers/{provider}/portfolio/reorder', [PortfolioItemController::class, 'reorder']);
        Route::patch('/portfolio/{portfolioItem}', [PortfolioItemController::class, 'update']);
        Route::delete('/portfolio/{portfolioItem}', [PortfolioItemController::class, 'destroy']);
    });

    // Example of an admin-only route group (Week 4):
    // Route::middleware('role:admin')->group(function () {
    //     Route::post('/admin/providers/{provider}/verify', [AdminController::class, 'verifyProvider']);
    // });
});
