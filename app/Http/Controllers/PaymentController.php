<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\InitialPaymentRequest;
use App\Models\Booking;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function pay(InitialPaymentRequest $request, Booking $booking)
    {
        $this->authorize('pay', $booking);

        $payment = $this->payments->initiateCollection(
            $booking,
            $request->validated('phone_number')
        );

        return response()->json($payment, 201);
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $payment = $booking->payments()->latest()->first();

        if (! $payment) {
            return response()->json(['status' => 'none']);
        }

        // If it's been pending for more than 10 seconds, ask MarzPay
        // directly. Webhooks can be dropped (free ngrok, network blips);
        // this makes the UI reliable regardless.
        if (
            $payment->status === 'pending'
            && $payment->created_at->lt(now()->subSeconds(10))
        ) {
            $payment = $this->payments->refreshFromProvider($payment);
        }

        return response()->json([
            'id'             => $payment->id,
            'status'         => $payment->status,
            'failure_reason' => $payment->failure_reason,
            'updated_at'     => $payment->updated_at,
        ]);
    }
}
