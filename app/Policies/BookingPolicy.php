<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    // Allow super admins to do everything in the admin panel
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Booking') || true; // Allow all to view their own list
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->clientProfile?->user_id
            || $user->id === $booking->providerProfile?->user_id
            || $user->can('View:Booking');
    }

    public function create(User $user): bool
    {
        return $user->role === 'client' || $user->can('Create:Booking');
    }

    /** Only the target provider may accept/decline, and only while pending. */
    public function updateStatus(User $user, Booking $booking): bool
    {
        if ($user->can('Update:Booking')) return true;

        return $user->id === $booking->providerProfile?->user_id
            && $booking->status === 'pending';
    }

    /** Only the requesting client may cancel, and only while pending. */
    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->can('Update:Booking')) return true;

        return $user->id === $booking->clientProfile?->user_id
            && $booking->status === 'pending';
    }

    /** Only the provider may mark a booking complete, and only once accepted. */
    public function complete(User $user, Booking $booking): bool
    {
        if ($user->can('Update:Booking')) return true;

        return $user->id === $booking->providerProfile?->user_id
            && $booking->status === 'accepted';
    }

    /** Only the requesting client may pay, and only once the provider has accepted. */
    public function pay(User $user, Booking $booking): bool
    {
        if ($user->can('Update:Booking')) return true;

        return $user->id === $booking->clientProfile?->user_id
            && $booking->status === 'accepted';
    }

    /** Only the requesting client may review, and only their own booking. */
    public function review(User $user, Booking $booking): bool
    {
        if ($user->can('Update:Booking')) return true;

        return $user->id === $booking->clientProfile?->user_id;
    }
}
