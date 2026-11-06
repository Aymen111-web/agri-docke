<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\User;
use App\Models\UserCapability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $farmer;
    protected User $buyer;
    protected Category $category;
    protected Listing $listing;
    protected Order $order;
    protected OrderFulfillment $fulfillment;
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'first_name'     => 'Admin',
            'second_name'    => 'User',
            'phone'          => '+251700000001',
            'password'       => 'password',
            'is_admin'       => true,
            'account_status' => 'active',
        ]);

        $this->farmer = User::create([
            'first_name'     => 'Farmer',
            'second_name'    => 'Joe',
            'phone'          => '+251911111112',
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
            'first_name'     => 'Buyer',
            'second_name'    => 'Jane',
            'phone'          => '+251922222223',
            'password'       => 'password',
            'is_admin'       => false,
            'account_status' => 'active',
        ]);

        $this->category = Category::firstOrCreate(
            ['name' => 'Grains'],
            ['slug' => 'grains']
        );

        $this->listing = Listing::create([
            'farmer_id'          => $this->farmer->id,
            'category_id'        => $this->category->id,
            'title'              => 'Red Teff Batch',
            'unit'               => 'kg',
            'price_per_unit'     => 100.00,
            'quantity_available' => 500.000,
            'status'             => 'active',
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-TEST-1001',
            'buyer_id'     => $this->buyer->id,
            'total_amount' => 1000.00,
            'status'       => 'pending_payment',
            'delivery_pin' => '123456',
        ]);

        $this->fulfillment = OrderFulfillment::create([
            'order_id'        => $this->order->id,
            'farmer_id'       => $this->farmer->id,
            'subtotal_amount' => 1000.00,
            'status'          => 'accepted',
        ]);

        $this->payment = Payment::create([
            'order_id'     => $this->order->id,
            'chapa_tx_ref' => 'TX-ORDER-1001-XYZ123',
            'amount'       => 1000.00,
            'currency'     => 'ETB',
            'status'       => 'pending',
        ]);
    }

    public function test_verify_payment_does_not_autoconfirm_unverified_tx_ref()
    {
        // Calling verify for an unverified TX ref should NOT confirm payment
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson("/api/payments/verify/{$this->payment->chapa_tx_ref}");

        // Without secret key or gateway confirmation, response should fail/be pending
        $this->payment->refresh();
        $this->assertNotEquals('confirmed', $this->payment->status);
    }

    public function test_farmer_payouts_summary_returns_valid_data()
    {
        Payout::create([
            'farmer_id'            => $this->farmer->id,
            'order_fulfillment_id' => $this->fulfillment->id,
            'amount'               => 1000.00,
            'status'               => 'processed',
            'reference'            => 'PO-REF-001',
            'processed_at'         => now(),
        ]);

        $response = $this->actingAs($this->farmer, 'sanctum')
            ->getJson('/api/payouts/summary');

        $response->assertStatus(200)
            ->assertJsonPath('summary.total_paid_out', 1000)
            ->assertJsonPath('summary.currency', 'ETB');
    }

    public function test_farmer_can_list_payouts()
    {
        Payout::create([
            'farmer_id'            => $this->farmer->id,
            'order_fulfillment_id' => $this->fulfillment->id,
            'amount'               => 1000.00,
            'status'               => 'pending',
            'reference'            => 'PO-REF-002',
        ]);

        $response = $this->actingAs($this->farmer, 'sanctum')
            ->getJson('/api/payouts');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_can_update_payout_status()
    {
        $payout = Payout::create([
            'farmer_id'            => $this->farmer->id,
            'order_fulfillment_id' => $this->fulfillment->id,
            'amount'               => 1000.00,
            'status'               => 'pending',
            'reference'            => 'PO-REF-003',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/payouts/{$payout->id}/status", [
                'status' => 'processed',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('payout.status', 'processed');

        $this->assertEquals('processed', $payout->fresh()->status);
        $this->assertNotNull($payout->fresh()->processed_at);
    }
}
