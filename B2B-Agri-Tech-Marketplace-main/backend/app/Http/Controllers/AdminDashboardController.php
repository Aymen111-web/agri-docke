<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CapabilityApplication;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\PaymentException;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    /**
     * Get aggregated stats and real-time feeds for the Admin Dashboard.
     *
     * GET /api/admin/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        $this->authorize('viewStats', User::class);

        // GMV: sum total_amount of orders that are paid or not cancelled/expired
        $totalGmv = Order::whereNotIn('status', ['cancelled', 'expired'])->sum('total_amount');
        $totalOrders = Order::count();

        $totalFarmers = User::whereHas('capabilities', fn ($q) => $q->where('capability_type', 'farmer')->where('status', 'active'))->count();
        $totalBuyers  = User::whereHas('capabilities', fn ($q) => $q->where('capability_type', 'buyer')->where('status', 'active'))->count();

        $pendingApplicationsCount = CapabilityApplication::where('status', 'pending')->count();
        $activeListingsCount      = Listing::where('status', 'active')->count();

        $paymentExceptionsCount = PaymentException::whereIn('status', ['open', 'investigating'])->count();
        $pendingPayoutsAmount   = Payout::where('status', 'pending')->sum('amount');

        // Recent Audit Activity Feed (last 8)
        $recentActivity = AuditLog::with(['user:id,first_name,second_name,phone,is_admin'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->map(function ($log) {
                return [
                    'id'             => $log->id,
                    'action'         => $log->action,
                    'auditable_type' => class_basename($log->auditable_type),
                    'auditable_id'   => $log->auditable_id,
                    'user'           => $log->user ? [
                        'id'         => $log->user->id,
                        'name'       => trim($log->user->first_name . ' ' . $log->user->second_name),
                        'is_admin'   => (bool)$log->user->is_admin,
                    ] : null,
                    'ip_address'     => $log->ip_address,
                    'created_at'     => $log->created_at->toIso8601String(),
                ];
            });

        // Pending Applications Preview for 1-Click Approvals (top 5)
        $pendingApplicationsPreview = CapabilityApplication::with(['user:id,first_name,second_name,phone'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Category Distribution with listing counts
        $categoryDistribution = Category::withCount(['listings' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn ($cat) => [
                'id'             => $cat->id,
                'name'           => $cat->name,
                'slug'           => $cat->slug,
                'active_listings'=> $cat->listings_count,
            ]);

        return response()->json([
            'kpis' => [
                'total_gmv'                 => (float) $totalGmv,
                'total_orders'              => (int) $totalOrders,
                'total_farmers'             => (int) $totalFarmers,
                'total_buyers'              => (int) $totalBuyers,
                'pending_applications'      => (int) $pendingApplicationsCount,
                'active_listings'           => (int) $activeListingsCount,
                'payment_exceptions_count'  => (int) $paymentExceptionsCount,
                'pending_payouts_amount'    => (float) $pendingPayoutsAmount,
            ],
            'recent_activity'               => $recentActivity,
            'pending_approvals_preview'     => $pendingApplicationsPreview,
            'category_distribution'         => $categoryDistribution,
        ]);
    }
}
