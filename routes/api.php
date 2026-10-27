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
// --- Public / guest ------------------------------------------------------

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
