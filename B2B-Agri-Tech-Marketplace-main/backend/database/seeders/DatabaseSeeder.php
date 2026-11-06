<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CapabilityApplication;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\User;
use App\Models\UserCapability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with roles, capabilities, and sample domain data.
     */
    public function run(): void
    {
        $defaultPassword = '123456';

        // 1. System Administrator — Ibrahim Admin (0921283801)
        $admin = User::updateOrCreate(
            ['phone' => '+251921283801'],
            [
                'first_name'        => 'Ibrahim',
                'second_name'       => 'Admin',
                'phone_verified_at' => now(),
                'password'          => Hash::make('123456'),
                'is_admin'          => true,
                'account_status'    => 'active',
            ]
        );

        // 2. Verified Farmer User — Aymen Farmer (0718280155)
        $farmer = User::updateOrCreate(
            ['phone' => '+251718280155'],
            [
                'first_name'        => 'Aymen',
                'second_name'       => 'Farmer',
                'phone_verified_at' => now(),
                'password'          => Hash::make('123456'),
                'is_admin'          => false,
                'account_status'    => 'active',
            ]
        );

        UserCapability::updateOrCreate(
            ['user_id' => $farmer->id, 'capability_type' => 'farmer'],
            ['status' => 'active', 'granted_at' => now(), 'granted_by' => $admin->id]
        );

        // 3. Verified Buyer User — Awol Buyer (0918982161)
        $buyer = User::updateOrCreate(
            ['phone' => '+251918982161'],
            [
                'first_name'        => 'Awol',
                'second_name'       => 'Buyer',
                'phone_verified_at' => now(),
                'password'          => Hash::make('123456'),
                'is_admin'          => false,
                'account_status'    => 'active',
            ]
        );

        UserCapability::updateOrCreate(
            ['user_id' => $buyer->id, 'capability_type' => 'buyer'],
            ['status' => 'active', 'granted_at' => now(), 'granted_by' => $admin->id]
        );

        // 4. Dual Role User (Farmer + Buyer)
        $dualUser = User::updateOrCreate(
            ['phone' => '+251933333333'],
            [
                'first_name'        => 'Tadesse',
                'second_name'       => 'Gebre',
                'phone_verified_at' => now(),
                'password'          => Hash::make($defaultPassword),
                'is_admin'          => false,
                'account_status'    => 'active',
            ]
        );

        UserCapability::updateOrCreate(
            ['user_id' => $dualUser->id, 'capability_type' => 'farmer'],
            ['status' => 'active', 'granted_at' => now(), 'granted_by' => $admin->id]
        );

        UserCapability::updateOrCreate(
            ['user_id' => $dualUser->id, 'capability_type' => 'buyer'],
            ['status' => 'active', 'granted_at' => now(), 'granted_by' => $admin->id]
        );

        // 5. Pending Capability Applicant User
        $applicant = User::updateOrCreate(
            ['phone' => '+251944444444'],
            [
                'first_name'        => 'Almaz',
                'second_name'       => 'Ayana',
                'phone_verified_at' => now(),
                'password'          => Hash::make($defaultPassword),
                'is_admin'          => false,
                'account_status'    => 'active',
            ]
        );

        CapabilityApplication::updateOrCreate(
            ['user_id' => $applicant->id, 'capability_type' => 'farmer'],
            [
                'status' => 'pending',
                'supporting_documents' => [
                    'id_card' => 'documents/almaz_national_id.pdf',
                    'land_ownership' => 'documents/almaz_farm_certificate.pdf'
                ]
            ]
        );

        // 6. Unverified / New Standard User
        User::updateOrCreate(
            ['phone' => '+251955555555'],
            [
                'first_name'        => 'Mulugeta',
                'second_name'       => 'Seretse',
                'phone_verified_at' => now(),
                'password'          => Hash::make($defaultPassword),
                'is_admin'          => false,
                'account_status'    => 'active',
            ]
        );

        // 7. Suspended Account User
        User::updateOrCreate(
            ['phone' => '+251966666666'],
            [
                'first_name'        => 'Birtukan',
                'second_name'       => 'Mamo',
                'phone_verified_at' => now(),
                'password'          => Hash::make($defaultPassword),
                'is_admin'          => false,
                'account_status'    => 'suspended',
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // Categories
        // ─────────────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Cereals & Grains', 'slug' => 'cereals-grains', 'is_active' => true],
            ['name' => 'Oilseeds', 'slug' => 'oilseeds', 'is_active' => true],
            ['name' => 'Coffee', 'slug' => 'coffee', 'is_active' => true],
            ['name' => 'Vegetables', 'slug' => 'vegetables', 'is_active' => true],
            ['name' => 'Fruits', 'slug' => 'fruits', 'is_active' => true],
            ['name' => 'Honey & Bee Products', 'slug' => 'honey-bee-products', 'is_active' => true],
            ['name' => 'Dairy Products', 'slug' => 'dairy-products', 'is_active' => true],
            ['name' => 'Other Agricultural Products', 'slug' => 'other-agricultural-products', 'is_active' => true],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // ─────────────────────────────────────────────────────────────
        // Produce Listings
        // ─────────────────────────────────────────────────────────────
        $listingTeff = Listing::updateOrCreate(
            ['batch_number' => 'TEFF-2026-001'],
            [
                'farmer_id'              => $farmer->id,
                'category_id'            => $catModels['cereals-grains']->id ?? $catModels['vegetables']->id,
                'title'                  => 'Premium Red Teff - Batch #2026-A',
                'description'            => 'Cleaned, export-grade Red Teff harvested from Ada\'a cooperative farms.',
                'unit'                   => 'quintal',
                'price_per_unit'         => 5200.00,
                'quantity_available'     => 45.000,
                'quantity_reserved'      => 5.000,
                'status'                 => 'active',
                'harvest_date'           => now()->subDays(5)->format('Y-m-d'),
                'quality_grade'          => 'Grade A Export',
                'minimum_order_quantity' => 2.000,
                'price_valid_until'      => now()->addDays(14),
            ]
        );

        $listingCoffee = Listing::updateOrCreate(
            ['batch_number' => 'COF-YIRG-04'],
            [
                'farmer_id'              => $farmer->id,
                'category_id'            => $catModels['coffee']->id ?? $catModels['vegetables']->id,
                'title'                  => 'Grade 1 Specialty Yirgacheffe Washed Coffee',
                'description'            => 'High-altitude washed coffee beans with floral notes and citrus acidity.',
                'unit'                   => 'quintal',
                'price_per_unit'         => 12500.00,
                'quantity_available'     => 100.000,
                'quantity_reserved'      => 0.000,
                'status'                 => 'active',
                'harvest_date'           => now()->subDays(10)->format('Y-m-d'),
                'quality_grade'          => 'Grade 1 Specialty',
                'minimum_order_quantity' => 5.000,
                'price_valid_until'      => now()->addDays(30),
            ]
        );

        $listingAvocado = Listing::updateOrCreate(
            ['batch_number' => 'AVO-WON-88'],
            [
                'farmer_id'              => $dualUser->id,
                'category_id'            => $catModels['fruits']->id ?? $catModels['vegetables']->id,
                'title'                  => 'Organic Hass Avocados - Wonchi Highlands',
                'description'            => 'Freshly harvested Hass avocados, rich in healthy oils, perfect for hotel chain procurement.',
                'unit'                   => 'kg',
                'price_per_unit'         => 140.00,
                'quantity_available'     => 1200.000,
                'quantity_reserved'      => 0.000,
                'status'                 => 'active',
                'harvest_date'           => now()->subDays(3)->format('Y-m-d'),
                'quality_grade'          => 'Grade A Premium',
                'minimum_order_quantity' => 50.000,
                'price_valid_until'      => now()->addDays(7),
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // Cart Items
        // ─────────────────────────────────────────────────────────────
        CartItem::updateOrCreate(
            ['buyer_id' => $buyer->id, 'listing_id' => $listingCoffee->id],
            [
                'quantity'       => 5.000,
                'price_snapshot' => 12500.00,
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // Orders, Items, Fulfillments, Payments & Payouts
        // ─────────────────────────────────────────────────────────────
        // Order 1: Active Order with 15-Minute Reservation Timer & Delivery PIN
        $order1 = Order::updateOrCreate(
            ['order_number' => 'ORD-2026-000492'],
            [
                'buyer_id'               => $buyer->id,
                'status'                 => 'pending_payment',
                'payment_status'         => 'pending',
                'delivery_status'        => 'pending',
                'inspection_status'      => 'pending',
                'payout_status'          => 'locked',
                'total_amount'           => 10400.00,
                'currency'               => 'ETB',
                'delivery_pin'           => '849201',
                'reservation_expires_at' => now()->addMinutes(12),
                'placed_at'              => now(),
            ]
        );

        $fulfillment1 = OrderFulfillment::updateOrCreate(
            ['order_id' => $order1->id, 'farmer_id' => $farmer->id],
            [
                'status'            => 'pending',
                'delivery_status'   => 'pending',
                'inspection_status' => 'pending',
                'payout_status'     => 'locked',
                'subtotal_amount'   => 10400.00,
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order1->id, 'listing_id' => $listingTeff->id],
            [
                'order_fulfillment_id' => $fulfillment1->id,
                'quantity'             => 2.000,
                'unit_price'           => 5200.00,
                'subtotal'             => 10400.00,
            ]
        );

        Payment::updateOrCreate(
            ['order_id' => $order1->id],
            [
                'chapa_tx_ref' => 'CHAPA-TX-849201-TEST',
                'amount'       => 10400.00,
                'currency'     => 'ETB',
                'status'       => 'pending',
            ]
        );

        // Order 2: Completed Order with Handoff PIN verified & Inspection Completed
        $order2 = Order::updateOrCreate(
            ['order_number' => 'ORD-2026-000381'],
            [
                'buyer_id'                 => $buyer->id,
                'status'                   => 'completed',
                'payment_status'           => 'confirmed',
                'delivery_status'          => 'delivered',
                'inspection_status'        => 'accepted',
                'payout_status'            => 'eligible',
                'total_amount'             => 28000.00,
                'currency'                 => 'ETB',
                'delivery_pin'             => '192837',
                'delivery_pin_verified_at' => now()->subDay(),
                'placed_at'                => now()->subDays(2),
            ]
        );

        $fulfillment2 = OrderFulfillment::updateOrCreate(
            ['order_id' => $order2->id, 'farmer_id' => $dualUser->id],
            [
                'status'            => 'completed',
                'delivery_status'   => 'delivered',
                'inspection_status' => 'accepted',
                'payout_status'     => 'eligible',
                'subtotal_amount'   => 28000.00,
                'accepted_quantity' => 200.000,
                'rejected_quantity' => 0.000,
                'inspection_notes'  => '100% Produce quality verified upon handoff.',
                'inspected_at'       => now()->subDay(),
                'completed_at'      => now()->subDay(),
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order2->id, 'listing_id' => $listingAvocado->id],
            [
                'order_fulfillment_id' => $fulfillment2->id,
                'quantity'             => 200.000,
                'unit_price'           => 140.00,
                'subtotal'             => 28000.00,
            ]
        );

        Payment::updateOrCreate(
            ['order_id' => $order2->id],
            [
                'chapa_tx_ref' => 'CHAPA-TX-192837-CONFIRMED',
                'amount'       => 28000.00,
                'currency'     => 'ETB',
                'status'       => 'confirmed',
                'confirmed_at' => now()->subDays(2),
            ]
        );

        Payout::updateOrCreate(
            ['order_fulfillment_id' => $fulfillment2->id],
            [
                'farmer_id'    => $dualUser->id,
                'amount'       => 28000.00,
                'status'       => 'processed',
                'reference'    => 'TEL-PAYOUT-2026-991',
                'processed_at' => now()->subDay(),
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // Audit Logs
        // ─────────────────────────────────────────────────────────────
        AuditLog::create([
            'user_id'        => $admin->id,
            'action'         => 'capability_granted',
            'auditable_type' => User::class,
            'auditable_id'   => $farmer->id,
            'new_values'     => ['capability' => 'farmer', 'status' => 'active'],
            'ip_address'     => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id'        => $buyer->id,
            'action'         => 'order_placed',
            'auditable_type' => Order::class,
            'auditable_id'   => $order1->id,
            'new_values'     => ['order_number' => $order1->order_number, 'amount' => 10400.00],
            'ip_address'     => '127.0.0.1',
        ]);
    }
}
