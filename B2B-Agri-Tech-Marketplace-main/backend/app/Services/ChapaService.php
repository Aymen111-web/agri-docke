<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChapaService
{
    /**
     * Format phone number to Chapa's required 10-digit format (09xxxxxxxx or 07xxxxxxxx).
     */
    public function formatPhoneNumber(?string $phone): string
    {
        if (! $phone) {
            return '0912345678';
        }

        // Remove non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Handle Ethiopian international prefix 2519... or 2517...
        if (str_starts_with($cleaned, '251') && strlen($cleaned) >= 12) {
            $cleaned = '0' . substr($cleaned, 3);
        }

        // Ensure 10 digits starting with 09 or 07
        if (preg_match('/^(09|07)[0-9]{8}$/', $cleaned)) {
            return $cleaned;
        }

        return '0912345678';
    }

    /**
     * Build invoice line items for Chapa payment receipt meta section.
     */
    public function buildInvoicesMeta($orderOrFulfillment): array
    {
        $invoices = [];

        $items = match (true) {
            $orderOrFulfillment instanceof Order => $orderOrFulfillment->items,
            $orderOrFulfillment instanceof OrderFulfillment => $orderOrFulfillment->items ?? $orderOrFulfillment->order?->items ?? [],
            default => [],
        };

        foreach ($items as $item) {
            $title = $item->listing?->title ?? 'Produce Item';
            $qty   = $item->quantity ?? 1;
            $unit  = $item->listing?->unit ?? 'pcs';
            $invoices[] = [
                'key'   => substr((string) $title, 0, 40),
                'value' => "{$qty} {$unit}",
            ];
        }

        if (empty($invoices)) {
            $invoices[] = [
                'key'   => 'Agricultural Produce',
                'value' => '1 order',
            ];
        }

        return ['invoices' => $invoices];
    }

    /**
     * Create or update a Chapa Subaccount for a farmer.
     *
     * POST https://api.chapa.co/v1/subaccount
     */
    public function createSubaccount(User $user, array $paymentDetails): ?string
    {
        $secretKey = config('services.chapa.secret_key');
        $businessName = trim("{$user->first_name} {$user->second_name}");

        $payload = [
            'business_name'  => $businessName ?: 'Farmer Merchant',
            'account_name'   => $paymentDetails['account_name'],
            'bank_code'      => $paymentDetails['bank_code'],
            'account_number' => $paymentDetails['account_number'],
            'split_type'     => 'percentage',
            'split_value'    => 0, // 100% direct settlement to farmer subaccount
        ];

        if ($secretKey) {
            try {
                $response = Http::withToken($secretKey)
                    ->post('https://api.chapa.co/v1/subaccount', $payload);

                if ($response->successful()) {
                    $subId = $response->json('data.subaccount_id') ?? $response->json('data.id');

                    if ($subId) {
                        return (string) $subId;
                    }
                } else {
                    Log::warning('Chapa subaccount creation API response failure:', [
                        'user_id' => $user->id,
                        'status'  => $response->status(),
                        'body'    => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Chapa subaccount creation API exception:', [
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $user->chapa_subaccount_id;
    }

    /**
     * Initialize payment for an Order via Chapa hosted checkout.
     *
     * POST https://api.chapa.co/v1/transaction/initialize
     */
    public function initializeOrderPayment(Order $order, User $user, string $txRef, ?float $amountOverride = null): array
    {
        $secretKey = config('services.chapa.secret_key');
        $phone = $this->formatPhoneNumber($user->phone);

        $email = filter_var($user->email, FILTER_VALIDATE_EMAIL) ? $user->email : 'buyer@gmail.com';
        $orderNumClean = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $order->order_number);
        $amount = $amountOverride !== null ? (float) $amountOverride : (float) $order->total_amount;
        if ($amount > 1000000) {
            if (config('app.env') !== 'production') {
                $amount = 999999.00;
            } else {
                return [
                    'success' => false,
                    'message' => 'Transaction amount (' . number_format($amount, 2) . ' ETB) exceeds Chapa single transaction limit of 1,000,000 ETB.',
                ];
            }
        }

        $payload = [
            'amount'        => $amount,
            'currency'      => $order->currency ?: 'ETB',
            'email'         => $email,
            'first_name'    => preg_replace('/[^A-Za-z0-9]/', '', $user->first_name ?: 'Buyer'),
            'last_name'     => preg_replace('/[^A-Za-z0-9]/', '', $user->second_name ?: 'User'),
            'phone_number'  => $phone,
            'tx_ref'        => $txRef,
            'callback_url'  => config('services.chapa.callback_url'),
            'customization' => [
                'title'       => 'AgriMarket ET',
                'description' => "Order Payment {$orderNumClean}",
            ],
            'meta' => $this->buildInvoicesMeta($order),
        ];

        if ($secretKey) {
            try {
                $response = Http::withToken($secretKey)
                    ->post('https://api.chapa.co/v1/transaction/initialize', $payload);

                if ($response->successful()) {
                    $data = $response->json('data');
                    if (! empty($data['checkout_url'])) {
                        return [
                            'success'      => true,
                            'checkout_url' => $data['checkout_url'],
                        ];
                    }
                }

                $errBody = $response->json();
                $errMsg = $this->parseChapaErrorMessage($errBody, $response->status());

                Log::warning('Chapa order payment initialization failed:', [
                    'order_id' => $order->id,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Chapa error: ' . $errMsg,
                ];
            } catch (\Exception $e) {
                Log::error('Chapa order payment initialization exception:', [
                    'order_id' => $order->id,
                    'message'  => $e->getMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Chapa gateway error: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Chapa secret key is not configured.',
        ];
    }

    /**
     * Initialize hosted checkout direct payment for a fulfillment to farmer subaccount.
     *
     * POST https://api.chapa.co/v1/transaction/initialize
     */
    public function initializeDirectPayment(
        OrderFulfillment $fulfillment,
        User $buyer,
        User $farmer,
        string $txRef
    ): array {
        $secretKey = config('services.chapa.secret_key');
        $phone = $this->formatPhoneNumber($buyer->phone);

        $email = filter_var($buyer->email, FILTER_VALIDATE_EMAIL) ? $buyer->email : 'buyer@gmail.com';
        $farmerNameClean = preg_replace('/[^A-Za-z0-9 ]/', '', $farmer->first_name ?: 'Farmer');

        $payload = [
            'amount'        => (float) $fulfillment->subtotal_amount + (float) $fulfillment->platform_fee,
            'currency'      => 'ETB',
            'email'         => $email,
            'first_name'    => preg_replace('/[^A-Za-z0-9]/', '', $buyer->first_name ?: 'Buyer'),
            'last_name'     => preg_replace('/[^A-Za-z0-9]/', '', $buyer->second_name ?: 'User'),
            'phone_number'  => $phone,
            'tx_ref'        => $txRef,
            'callback_url'  => config('services.chapa.callback_url'),
            'customization' => [
                'title'       => 'AgriMarket ET',
                'description' => "Order Payment {$farmerNameClean}",
            ],
        ];

        if ($secretKey) {
            try {
                $response = Http::withToken($secretKey)
                    ->post('https://api.chapa.co/v1/transaction/initialize', $payload);

                if ($response->successful()) {
                    $data = $response->json('data');
                    if (! empty($data['checkout_url'])) {
                        return [
                            'success'      => true,
                            'checkout_url' => $data['checkout_url'],
                        ];
                    }
                }

                $errBody = $response->json();
                $errMsg = $this->parseChapaErrorMessage($errBody, $response->status());

                Log::warning('Chapa direct payment initialization failure:', [
                    'fulfillment_id' => $fulfillment->id,
                    'status'         => $response->status(),
                    'body'           => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Chapa error: ' . $errMsg,
                ];
            } catch (\Exception $e) {
                Log::error('Chapa direct payment initialization exception:', [
                    'fulfillment_id' => $fulfillment->id,
                    'message'        => $e->getMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Chapa gateway error: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Chapa secret key is not configured.',
        ];
    }

    /**
     * Verify payment status using Chapa Verification API.
     *
     * GET https://api.chapa.co/v1/transaction/verify/{tx_ref}
     */
    public function verifyTransaction(string $txRef): array
    {
        $secretKey = config('services.chapa.secret_key');

        if (! $secretKey) {
            return [
                'success' => false,
                'message' => 'Chapa secret key is missing.',
            ];
        }

        try {
            $response = Http::withToken($secretKey)
                ->get("https://api.chapa.co/v1/transaction/verify/{$txRef}");

            if ($response->successful()) {
                $body = $response->json();
                $data = $body['data'] ?? [];
                $status = $data['status'] ?? ($body['status'] ?? 'unknown');

                return [
                    'success'     => strtolower($status) === 'success',
                    'status'      => strtolower($status),
                    'message'     => $body['message'] ?? 'Transaction verified.',
                    'data'        => $data,
                    'receipt_url' => ! empty($data['reference']) ? "https://chapa.link/payment-receipt/{$data['reference']}" : null,
                ];
            }

            return [
                'success' => false,
                'status'  => 'failed',
                'message' => $response->json('message') ?? 'Verification request failed.',
                'body'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Chapa transaction verification exception:', [
                'tx_ref'  => $txRef,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel an active transaction before completion.
     *
     * PUT https://api.chapa.co/v1/transaction/cancel/{tx_ref}
     */
    public function cancelTransaction(string $txRef): array
    {
        $secretKey = config('services.chapa.secret_key');

        if (! $secretKey) {
            return [
                'success' => false,
                'message' => 'Chapa secret key is missing.',
            ];
        }

        try {
            $response = Http::withToken($secretKey)
                ->put("https://api.chapa.co/v1/transaction/cancel/{$txRef}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json('message') ?? 'Transaction cancelled successfully.',
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Failed to cancel transaction.',
            ];
        } catch (\Exception $e) {
            Log::error('Chapa transaction cancel exception:', [
                'tx_ref'  => $txRef,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of supported currencies.
     *
     * GET https://api.chapa.co/v1/currency_supported
     */
    public function getSupportedCurrencies(): array
    {
        $secretKey = config('services.chapa.secret_key');

        if (! $secretKey) {
            return ['status' => 'error', 'currencies' => ['ETB']];
        }

        try {
            $response = Http::withToken($secretKey)
                ->get('https://api.chapa.co/v1/currency_supported');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Chapa supported currency check exception: ' . $e->getMessage());
        }

        return ['status' => 'error', 'currencies' => ['ETB']];
    }

    /**
     * Helper to safely parse and flatten Chapa error response messages.
     */
    protected function parseChapaErrorMessage(mixed $errBody, int $status): string
    {
        if (is_array($errBody)) {
            if (isset($errBody['message'])) {
                if (is_array($errBody['message'])) {
                    $flattened = [];
                    array_walk_recursive($errBody['message'], function ($val) use (&$flattened) {
                        if (is_string($val)) {
                            $flattened[] = $val;
                        }
                    });
                    if (! empty($flattened)) {
                        return implode(' ', array_unique($flattened));
                    }
                } elseif (is_string($errBody['message'])) {
                    return $errBody['message'];
                }
            }
        }
        return 'Chapa API request failed with status ' . $status;
    }
}
