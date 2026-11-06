<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    /**
     * List payouts for the authenticated farmer.
     *
     * GET /api/payouts
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $payouts = Payout::where('farmer_id', $user->id)
            ->with(['fulfillment.order', 'fulfillment.items.listing'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => $payouts->items(),
            'meta' => [
                'current_page' => $payouts->currentPage(),
                'last_page'    => $payouts->lastPage(),
                'total'        => $payouts->total(),
            ]
        ]);
    }

    /**
     * Get summary of farmer payouts.
     *
     * GET /api/payouts/summary
     */
    public function summary(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $totalPaidOut = Payout::where('farmer_id', $user->id)
            ->where('status', 'processed')
            ->sum('amount');

        $pendingPayout = Payout::where('farmer_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $lockedEscrowSum = \App\Models\OrderFulfillment::where('farmer_id', $user->id)
            ->whereIn('payout_status', ['pending', 'locked'])
            ->whereIn('status', ['paid_in_escrow', 'dispatched', 'in_transit', 'buyer_received'])
            ->select(\Illuminate\Support\Facades\DB::raw('SUM(COALESCE(farmer_net_payout, subtotal_amount)) as total'))
            ->value('total') ?? 0;

        $totalPending = (float)$pendingPayout + (float)$lockedEscrowSum;

        return response()->json([
            'summary' => [
                'total_paid_out' => (float) $totalPaidOut,
                'pending_payout' => $totalPending,
                'currency'       => 'ETB',
            ]
        ]);
    }

    /**
     * Admin: Update payout status manually.
     *
     * PATCH /api/admin/payouts/{id}/status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processed,failed',
        ]);

        $payout = Payout::findOrFail($id);

        $oldStatus = $payout->status;
        $newStatus = $request->input('status');

        $payout->update(['status' => $newStatus]);

        if ($newStatus === 'processed' && $oldStatus !== 'processed') {
            $payout->update(['processed_at' => now()]);
            // Update associated fulfillment
            $payout->fulfillment()->update(['payout_status' => 'released']);
        }

        if ($newStatus === 'failed') {
            $payout->fulfillment()->update(['payout_status' => 'locked']);
        }

        return response()->json([
            'message' => 'Payout status updated successfully.',
            'payout'  => $payout->fresh(),
        ]);
    }
}
