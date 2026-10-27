<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function NileSquad\NylonPay\verifyWebhookSignature;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private PaymentService $payments,
        private WithdrawalService $withdrawals
    ) {}

    public function handle(Request $request)
    {
        $signature = $request->header('X-NYLON-SIGNATURE', '');
        $secret    = config('services.nylonpay.webhook_secret');

        $valid = verifyWebhookSignature([
            'payload'   => $request->getContent(),
            'signature' => $signature,
            'secret'    => $secret,
        ]);

        if (! $valid) {
            Log::warning('Nylon Pay webhook failed signature check', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $this->payments->handleWebhook($request->all());
        $this->withdrawals->handleWebhook($request->all());

        return response()->json(['message' => 'Received']);
    }
}
