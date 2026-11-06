<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserCapability;
use App\Services\DeliveryService;
use App\Services\InspectionService;
use App\Services\PaymentService;
use App\Services\PricingService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $farmer;
    protected User $buyer;
    protected Category $category;
    protected Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'first_name'     => 'System',
            'second_name'    => 'Admin',
            'phone'          => '+251700000000',
            'password'       => 'password',
            'is_admin'       => true,
            'account_status' => 'active',
        ]);

        $this->farmer = User::create([
            'first_name'     => 'Abebe',
            'second_name'    => 'Bikila',
            'phone'          => '+251911111111',
            'password'       => 'password',
            'is_admin'       => false,
            'account_status' => 'active',
        ]);

        UserCapability::create([
            'user_id'         => $this->farmer->id,
            'capability_type' => 'farmer',
            'status'          => 'active',
            'granted_by'      => $this->admin->id,
        ]);

        $this->buyer = User::create([
            'first_name'     => 'Kebede',
            'second_name'    => 'Tessema',
            'phone'          => '+251922222222',
            'password'       => 'password',
            'is_admin'       => false,
            'account_status' => 'active',
        ]);

        $this->category = Category::firstOrCreate(
            ['name' => 'Vegetables'],
            ['slug' => 'vegetables']
        );

        $this->listing = Listing::create([
            'farmer_id'              => $this->farmer->id,
            'category_id'            => $this->category->id,
            'title'                  => 'Fresh Organic Tomatoes - Batch #101',
            'batch_number'           => 'TOM-2026-101',
            'unit'                   => 'kg',
            'price_per_unit'         => 500.00,
            'quantity_available'     => 100.000,
            'quantity_reserved'      => 0.000,
            'status'                 => 'active',
            'minimum_order_quantity' => 5.000,
            'harvest_date'           => now()->subDays(2)->format('Y-m-d'),
            'quality_grade'          => 'Grade A Export',
            'price_valid_until'      => now()->addDays(7),
        ]);
    }

    public function test_checkout_creates_15_minute_reservation_and_6_digit_pin()
    {
        $reservationService = new ReservationService();

        $cartItems = [
            [
                'listing_id'     => $this->listing->id,
                'quantity'       => 10.000,
                'price_snapshot' => 500.00,
            ]
        ];

        $order = $reservationService->createReservation($this->buyer->id, $cartItems);

        $this->assertEquals('pending_payment', $order->status);
        $this->assertEquals(6, strlen($order->delivery_pin));
        $this->assertNotNull($order->reservation_expires_at);

        $this->listing->refresh();
        $this->assertEquals(90.000, $this->listing->quantity_available);
        $this->assertEquals(10.000, $this->listing->quantity_reserved);
    }

    public function test_reservation_expires_automatically_and_releases_inventory()
    {
        CartItem::create([
            'buyer_id'       => $this->buyer->id,
            'listing_id'     => $this->listing->id,
            'quantity'       => 10.000,
            'price_snapshot' => 500.00,
        ]);

        $reservationService = new ReservationService();
        $order = $reservationService->createReservation($this->buyer->id, [
            ['listing_id' => $this->listing->id, 'quantity' => 10.000, 'price_snapshot' => 500.00]
        ]);

        $order->update(['reservation_expires_at' => now()->subMinute()]);

        $count = $reservationService->releaseExpiredReservations();

        $this->assertEquals(1, $count);
        $this->assertEquals('expired', $order->fresh()->status);

        $this->listing->refresh();
        $this->assertEquals(100.000, $this->listing->quantity_available);
        $this->assertEquals(0.000, $this->listing->quantity_reserved);
    }

    public function test_late_chapa_payment_on_expired_reservation_fails_and_flags_refund()
    {
        $reservationService = new ReservationService();
        $order = $reservationService->createReservation($this->buyer->id, [
            ['listing_id' => $this->listing->id, 'quantity' => 10.000, 'price_snapshot' => 500.00]
        ]);

        $payment = Payment::create([
            'order_id'     => $order->id,
            'chapa_tx_ref' => 'CHAPA-TEST-LATE-123',
            'amount'       => 5000.00,
            'currency'     => 'ETB',
            'status'       => 'pending',
        ]);

        $order->update(['status' => 'expired']);

        $paymentService = new PaymentService();
        $result = $paymentService->confirmPayment($payment, ['tx_ref' => 'CHAPA-TEST-LATE-123']);

        $this->assertEquals('refund_flagged', $result['status']);
        $this->assertEquals('failed', $payment->fresh()->status);
    }

    public function test_delivery_pin_verification_updates_delivery_status()
    {
        $reservationService = new ReservationService();
        $order = $reservationService->createReservation($this->buyer->id, [
            ['listing_id' => $this->listing->id, 'quantity' => 10.000, 'price_snapshot' => 500.00]
        ]);

        $deliveryService = new DeliveryService();
        $invalidResult = $deliveryService->verifyHandoffPin($order, '000000');
        $this->assertFalse($invalidResult);

        $validResult = $deliveryService->verifyHandoffPin($order, $order->delivery_pin);
        $this->assertTrue($validResult);
        $this->assertEquals('delivered', $order->fresh()->delivery_status);
    }

    public function test_produce_inspection_determines_payout_eligibility()
    {
        $reservationService = new ReservationService();
        $order = $reservationService->createReservation($this->buyer->id, [
            ['listing_id' => $this->listing->id, 'quantity' => 10.000, 'price_snapshot' => 500.00]
        ]);

        $fulfillment = $order->fulfillments->first();

        $inspectionService = new InspectionService();
        $fulfillment = $inspectionService->inspectFulfillment($fulfillment, 'accepted', 10.000, 0.000, 'Fresh batch verified.');

        $this->assertEquals('accepted', $fulfillment->inspection_status);
        $this->assertEquals('eligible', $fulfillment->payout_status);

        $fulfillmentPartial = $inspectionService->inspectFulfillment($fulfillment, 'partially_accepted', 7.000, 3.000, '3kg bruised in transit.');
        $this->assertEquals('eligible', $fulfillmentPartial->payout_status);

        $fulfillmentRejected = $inspectionService->inspectFulfillment($fulfillment, 'rejected', 0.000, 10.000, 'Entire batch spoiled.');
        $this->assertEquals('rejected_refund', $fulfillmentRejected->payout_status);
    }

    public function test_pricing_service_enforces_moq_and_price_validity()
    {
        $pricingService = new PricingService();

        $moqResult = $pricingService->validateItemPricing($this->listing, 2.000);
        $this->assertFalse($moqResult['valid']);

        $validResult = $pricingService->validateItemPricing($this->listing, 10.000);
        $this->assertTrue($validResult['valid']);

        $this->listing->update(['price_valid_until' => now()->subMinute()]);
        $expiredResult = $pricingService->validateItemPricing($this->listing, 10.000);
        $this->assertFalse($expiredResult['valid']);
    }

    public function test_listing_supports_custom_region_zone_process_and_multi_item_checkout()
    {
        $listing2 = Listing::create([
            'farmer_id'              => $this->farmer->id,
            'category_id'            => $this->category->id,
            'title'                  => 'Amhara White Teff Batch',
            'unit'                   => 'kg',
            'price_per_unit'         => 120.00,
            'quantity_available'     => 500.000,
            'status'                 => 'active',
            'quality_grade'          => 'Grade 1',
            'region'                 => 'Amhara',
            'zone'                   => 'Awi Zone',
            'process'                => 'Natural',
        ]);

        $this->assertEquals('Amhara', $listing2->region);
        $this->assertEquals('Awi Zone', $listing2->zone);
        $this->assertEquals('Natural', $listing2->process);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/orders/checkout', [
                'items' => [
                    ['listing_id' => $this->listing->id, 'quantity_kg' => 10],
                    ['listing_id' => $listing2->id, 'quantity_kg' => 20],
                ]
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Order placed successfully. Stock reserved for 15 minutes.');

        $this->listing->refresh();
        $listing2->refresh();

        $this->assertEquals(90.000, $this->listing->quantity_available);
        $this->assertEquals(480.000, $listing2->quantity_available);
    }
}
