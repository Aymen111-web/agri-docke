<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingResource;
use App\Http\Resources\OrderResource;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyerDashboardController extends Controller
{
    /**
     * Get real-time aggregated metrics, stat counters, live shipments, and featured produce for the authenticated buyer.
     *
     * GET /api/buyer/dashboard/stats
     */
    public function stats(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Active Purchase Orders count & Total Procurement Spend
        $userOrdersQuery = $user ? $user->orders() : Order::query();
        
        $activeOrdersCount = (clone $userOrdersQuery)
            ->whereIn('status', ['placed', 'confirmed', 'dispatched', 'in_transit'])
            ->count();

        $totalProcurementETB = (clone $userOrdersQuery)
            ->whereIn('status', ['placed', 'confirmed', 'dispatched', 'in_transit', 'delivered', 'completed'])
            ->sum('total_amount');



        // 2. Pending Delivery Handoffs (orders in transit awaiting PIN delivery confirmation)
        $pendingHandoffsCount = (clone $userOrdersQuery)
            ->whereIn('status', ['in_transit', 'dispatched'])
            ->count();

        // 3. Verified Co-op Farmers Count
        $verifiedFarmersCount = User::whereHas('capabilities', function ($q) {
            $q->where('capability_type', 'farmer')->where('status', 'active');
        })->count();



        // 4. Cart Items / Saved Listings Count
        $cartItemsCount = $user ? $user->cartItems()->count() : 0;

        // 5. Live Order Shipments for Transit Card
        $liveShipments = (clone $userOrdersQuery)
            ->with([
                'items.listing:id,farmer_id,title,crop_emoji,unit,price_per_unit,quality_grade',
                'fulfillments.farmer:id,first_name,second_name,region',
                'payment:id,order_id,status,tx_ref'
            ])
            ->whereIn('status', ['in_transit', 'dispatched', 'placed', 'confirmed'])
            ->orderByDesc('placed_at')
            ->take(3)
            ->get();

        // 6. Featured Produce Listings on Marketplace
        $featuredListings = Listing::with(['farmer:id,first_name,second_name,region', 'category:id,name,slug'])
            ->where('is_active', true)
            ->orderByDesc('is_verified')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return response()->json([
            'stats' => [
                'active_orders'          => (int) $activeOrdersCount,
                'total_procurement_etb'  => (float) $totalProcurementETB,
                'regional_hubs_count'    => 0,
                'primary_unions_count'   => 0,
                'verified_farmers_count' => (int) $verifiedFarmersCount,
                'pending_handoffs_count' => (int) $pendingHandoffsCount,
                'active_contracts_count' => 0,
                'cart_items_count'       => $cartItemsCount,
            ],
            'live_shipments'    => OrderResource::collection($liveShipments),
            'featured_listings' => ListingResource::collection($featuredListings),
        ]);
    }
}
