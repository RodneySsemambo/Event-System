<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

/**
 * Booking Controller processes the booking when a client tries to complete,cancel or decline a booking 
 * and show a single booking and update the status of the booking 
 * the booking is done by the client and accepted by the provider being booked 
 */

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    //------The store function creates booking once the clients input are validated 
    //and returns a 201 successful response that the booking was successfully created------------
    public function store(StoreBookingRequest $request)
    {
        $bookings = $this->bookings->createBooking($request->user(), $request->validated());
        return response()->json($bookings, 201);
    }

    //-------The index function retrieves from the database the users details and potrays 
    //them such as the clientprofile name ,user id the payment made to the provider and the providerprofile
    //and returns a json response of bookings-------------------
    public function index(Request $request)
    {
        $user = $request->user();

        $bookings = Booking::query()
            ->when($user->clientProfile, function ($q) use ($user) {
                $q->orWhere('client_profile_id', $user->clientProfile->id);
            })
            ->when($user->providerProfile, function ($q) use ($user) {
                $q->orWhere('provider_profile_id', $user->providerProfile->id);
            })
            ->with([
                'clientProfile.user:id,name',
                'providerProfile:id,business_name,user_id,whatsapp_number,price_range_min,price_range_max',
                'review',
                'payment',
            ])
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    //-------one booking is viewed and showed to the client with response of the provider profile payment and the clients name-----------
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return response()->json(
            $booking->load([
                'clientProfile.user:id,name',
                'providerProfile:id,business_name,user_id,whatsapp_number,price_range_min,price_range_max',
                'payment',
            ])
        );
    }
    //------The status is updated in this function after a provider accepting or declinig a booking------- 
    public function updateStatus(Request $request, Booking $booking)
    {
        $this->authorize('updateStatus', $booking);

        $validated = $request->validate([
            'status'        => ['required', 'in:accepted,declined'],
            'quoted_amount' => ['nullable', 'numeric', 'min:100'],
        ]);

        $updated = $validated['status'] === 'accepted'
            ? $this->bookings->acceptBooking($booking, $validated['quoted_amount'] ?? null)
            : $this->bookings->declineBooking($booking);

        return response()->json($updated);
    }

    //--------cancelling of the booking and returning response of newly bookings--------
    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);
        return response()->json($this->bookings->cancelBooking($booking));
    }

    //-------when the booking is completed the controller returns a json response with the booking 
    //being completed-------
    public function complete(Booking $booking)
    {
        $this->authorize('complete', $booking);
        return response()->json($this->bookings->completeBooking($booking));
    }
}
