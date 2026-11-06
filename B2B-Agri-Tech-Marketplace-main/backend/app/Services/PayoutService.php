<?php

namespace App\Services;

use App\Models\OrderFulfillment;
use App\Models\Payout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayoutService
{
    /**
     * Transfer funds from the marketplace Escrow Chapa Wallet directly into the Farmer's Bank Account.
     * 
     * POST https://api.chapa.co/v1/transfers
     */
    public function releaseEscrow(OrderFulfillment $fulfillment): array
    {
        // 1. Guard Checks
        if ($fulfillment->payout_status === 'released' || $fulfillment->payout_status === 'processing') {
            return [
                'success' => false,
                'message' => 'Escrow payout is already released or processing.',
            ];
        }

        if (!in_array($fulfillment->status, ['completed', 'buyer_received'])) {
            return [
                'success' => false,
                'message' => 'Fulfillment must be completed or received before releasing escrow.',
            ];
        }

        $farmer = $fulfillment->farmer;
        
        // Ensure farmer has bank details mapped
        if (!$farmer->bank_code || !$farmer->account_number) {
            return [
                'success' => false,
                'message' => "Farmer {$farmer->name} is missing banking details. Payout impossible.",
            ];
        }

        $amount = (float) $fulfillment->farmer_net_payout;
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid payout amount.',
            ];
        }

        // 2. Register Pending Payout record
        $payout = Payout::create([
            'farmer_id'            => $farmer->id,
            'order_fulfillment_id' => $fulfillment->id,
            'amount'               => $amount,
            'status'               => 'pending',
            'reference'            => 'PAYOUT-' . $fulfillment->id . '-' . Str::random(8),
        ]);

        $fulfillment->update(['payout_status' => 'processing']);

        // 3. Initiate Transfer Request via Chapa
        $secretKey = config('services.chapa.secret_key');
        if (!$secretKey) {
            $payout->update(['status' => 'failed', 'reference' => 'MISSING_API_KEY']);
            return [
                'success' => false,
                'message' => 'Chapa secret key missing on server.',
            ];
        }

        $payload = [
            'account_name'   => $farmer->account_name ?: "{$farmer->first_name} {$farmer->second_name}",
            'account_number' => $farmer->account_number,
            'amount'         => $amount,
            'currency'       => 'ETB',
            'beneficiary_name'=> "{$farmer->first_name} {$farmer->second_name}",
            'reference'      => $payout->reference,
            'bank_code'      => $farmer->bank_code,
            // (Optional) 'title' => "AgriGate Payout"
        ];

        try {
            $response = Http::withToken($secretKey)
                ->post('https://api.chapa.co/v1/transfers', $payload);

            if ($response->successful()) {
                $payout->update([
                    'status'       => 'processed',
                    'processed_at' => now(),
                ]);
                
                $fulfillment->update(['payout_status' => 'released']);

                return [
                    'success' => true,
                    'message' => 'Chapa transfer processing successful.',
                    'payout'  => $payout,
                ];
            } else {
                Log::warning('Chapa Payout Failed', [
                    'fulfillment' => $fulfillment->id,
                    'status'      => $response->status(),
                    'body'        => $response->body()
                ]);

                $payout->update(['status' => 'failed']);
                $fulfillment->update(['payout_status' => 'locked']);

                return [
                    'success' => false,
                    'message' => 'Chapa transfer request rejected: ' . ($response->json('message') ?? 'Unknown error'),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Chapa Payout Exception', [
                'fulfillment' => $fulfillment->id,
                'message'     => $e->getMessage()
            ]);

            $payout->update(['status' => 'failed']);
            $fulfillment->update(['payout_status' => 'locked']);

            return [
                'success' => false,
                'message' => 'Error communicating with Chapa API.',
            ];
        }
    }
}
