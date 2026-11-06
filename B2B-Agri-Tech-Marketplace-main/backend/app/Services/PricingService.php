<?php

namespace App\Services;

use App\Models\Listing;

class PricingService
{
    /**
     * Validate that a order item respects MOQ and current price validity period.
     */
    public function validateItemPricing(Listing $listing, float $requestedQuantity): array
    {
        if ($listing->minimum_order_quantity && $requestedQuantity < (float) $listing->minimum_order_quantity) {
            return [
                'valid'   => false,
                'message' => "Order quantity {$requestedQuantity} is below minimum order quantity (MOQ) of {$listing->minimum_order_quantity} {$listing->unit}.",
            ];
        }

        if ($listing->price_valid_until && now()->gt($listing->price_valid_until)) {
            return [
                'valid'   => false,
                'message' => "Listing price expired on {$listing->price_valid_until->toDateTimeString()}. Fresh price update required.",
            ];
        }

        return [
            'valid'   => true,
            'message' => 'Price and MOQ valid.',
        ];
    }
}
