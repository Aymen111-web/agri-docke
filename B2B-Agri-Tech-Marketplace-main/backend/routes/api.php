<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\CapabilityApplicationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChapaWebhookController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderFulfillmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentExceptionController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
///// Authcontroller //////////////
Route::post('/auth/request-otp', [AuthController::class, 'requestOtp']);
Route::post('/auth/register',    [AuthController::class, 'register']);
Route::post('/auth/login',       [AuthController::class, 'login']);
Route::post('/auth/logout',      [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return new \App\Http\Resources\UserResource($request->user()->load(['capabilities', 'capabilityApplications']));
})->middleware('auth:sanctum');


////////// Capability Applications (authenticated users)/////

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/capability-applications',     [CapabilityApplicationController::class, 'store']);
    Route::get('/capability-applications/my',   [CapabilityApplicationController::class, 'my']);
    Route::get('/capability-applications/{id}', [CapabilityApplicationController::class, 'show']);
});

//// Admin — Capability Applications & Dashboard Analytics ///////////

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard/stats',                       [AdminDashboardController::class, 'stats']);
    Route::get('/capability-applications',              [CapabilityApplicationController::class, 'index']);
    Route::post('/capability-applications/{id}/approve', [CapabilityApplicationController::class, 'approve']);
    Route::post('/capability-applications/{id}/reject',  [CapabilityApplicationController::class, 'reject']);
    Route::get('/listings',                              [ListingController::class, 'adminIndex']);
    Route::patch('/listings/{id}/moderate',              [ListingController::class, 'moderate']);
    Route::get('/orders',                                [OrderController::class, 'adminIndex']);
});


// Listings — Farmer (authenticated, requires farmer capability)////////


Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/listings/my',        [ListingController::class, 'my']);
    Route::post('/listings',          [ListingController::class, 'store']);
    Route::put('/listings/{id}',      [ListingController::class, 'update']);
    Route::delete('/listings/{id}',   [ListingController::class, 'destroy']);
});

//////Listings — Public (browse & search)/////
Route::get('/listings',      [ListingController::class, 'index']);
Route::get('/listings/{id}', [ListingController::class, 'show']);

////// Cart — Buyer (authenticated, requires buyer capability) /////

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/cart',          [CartController::class, 'index']);
    Route::post('/cart',         [CartController::class, 'store']);
    Route::put('/cart/{id}',     [CartController::class, 'update']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::delete('/cart/{id}',  [CartController::class, 'destroy']);
});

////// Orders — Buyer (authenticated, requires buyer capability) /////

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/buyer/dashboard/stats',            [BuyerDashboardController::class, 'stats']);
    Route::get('/orders',                           [OrderController::class, 'index']);
    Route::get('/orders/{id}',                      [OrderController::class, 'show']);
    Route::post('/orders/checkout',                 [OrderController::class, 'checkout']);
    Route::post('/orders/{id}/pay',                 [PaymentController::class, 'initiate']);
    Route::post('/orders/{id}/verify-payment',      [PaymentController::class, 'verifyOrderPayments']);
    Route::post('/orders/{id}/cancel',              [OrderController::class, 'cancel']);
    Route::post('/orders/{id}/verify-delivery-pin', [OrderController::class, 'verifyDeliveryPin']);
});

////// Fulfillments — Farmer & Buyer /////

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/fulfillments',                          [OrderFulfillmentController::class, 'index']);
    Route::get('/fulfillments/{id}',                     [OrderFulfillmentController::class, 'show']);
    Route::post('/fulfillments/{id}/accept',             [OrderFulfillmentController::class, 'accept']);
    Route::post('/fulfillments/{id}/dispatch',           [OrderFulfillmentController::class, 'dispatchFulfillment']);
    Route::post('/fulfillments/{id}/reject',             [OrderFulfillmentController::class, 'reject']);
    Route::post('/fulfillments/{id}/complete',           [OrderFulfillmentController::class, 'complete']);
    Route::post('/fulfillments/{id}/confirm-received',   [OrderFulfillmentController::class, 'confirmReceived']);
});

////// Payments — Buyer (authenticated, requires buyer capability) /////

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/orders/{id}/payment',  [PaymentController::class, 'show']);
    Route::get('/payments/verify/{txRef}',  [PaymentController::class, 'verify']);
    Route::post('/payments/cancel/{txRef}', [PaymentController::class, 'cancel']);
});

////// Chapa Webhook & Callbacks (public — verified by signature / Chapa API) /////

Route::match(['get', 'post'], '/payments/callback', [PaymentController::class, 'callback']);
Route::post('/payments/webhook',                    [ChapaWebhookController::class, 'handle']);
Route::get('/payments/currencies',                  [PaymentController::class, 'currencies']);


////// Payment Exceptions — Authenticated users (buyer/farmer) /////

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/payment-exceptions',                 [PaymentExceptionController::class, 'store']);
    Route::post('/payment-exceptions/{id}/respond',    [PaymentExceptionController::class, 'respondToException']);
    Route::get('/payment-exceptions/my',               [PaymentExceptionController::class, 'my']);
    Route::get('/payment-exceptions/{id}',             [PaymentExceptionController::class, 'show']);
});

////// Notifications /////

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications',                       [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read',        [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read',            [NotificationController::class, 'markAsRead']);
});

////// Admin — Payment Exceptions /////

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/payment-exceptions',                    [PaymentExceptionController::class, 'index']);
    Route::post('/payment-exceptions/{id}/investigate',  [PaymentExceptionController::class, 'investigate']);
    Route::post('/payment-exceptions/{id}/resolve',      [PaymentExceptionController::class, 'resolve']);
    Route::post('/payment-exceptions/{id}/reject',       [PaymentExceptionController::class, 'reject']);
});

////// User Profile (authenticated) /////

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile',   [UserController::class, 'profile']);
    Route::put('/profile',   [UserController::class, 'updateProfile']);
    Route::post('/profile',  [UserController::class, 'updateProfile']); // Support multipart photo uploads
});

////// Admin — User Management /////

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users',                       [UserController::class, 'index']);
    Route::get('/users/stats',                 [UserController::class, 'stats']);
    Route::get('/users/{userId}',              [UserController::class, 'show']);
    Route::get('/users/{userId}/capabilities', [UserController::class, 'capabilities']);
    Route::post('/users/{userId}/suspend',     [UserController::class, 'suspend']);
    Route::post('/users/{userId}/activate',    [UserController::class, 'activate']);
});

////// Payouts — Farmer (authenticated, requires farmer capability) /////

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/payouts',                [PayoutController::class, 'index']);
    Route::get('/payouts/summary',        [PayoutController::class, 'summary']);
    Route::get('/payouts/pending',        [PayoutController::class, 'pending']);
    Route::get('/payouts/processed',      [PayoutController::class, 'processed']);
    Route::get('/payouts/monthly-report', [PayoutController::class, 'monthlyReport']);
    Route::get('/payouts/{payout}',       [PayoutController::class, 'show']);
});

////// Admin — Payouts /////

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/payouts',                    [PayoutController::class, 'history']);
    Route::post('/payouts',                   [PayoutController::class, 'store']);
    Route::patch('/payouts/{payout}/status',  [PayoutController::class, 'updateStatus']);
});

////// Audit Logs — Authenticated user's own activity /////

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-activity', [AuditLogController::class, 'myActivity']);
});

////// Admin — Audit Logs /////

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/audit-logs',                [AuditLogController::class, 'index']);
    Route::get('/audit-logs/resource',       [AuditLogController::class, 'forResource']);
    Route::get('/audit-logs/user/{userId}',  [AuditLogController::class, 'forUser']);
    Route::get('/audit-logs/{auditLog}',     [AuditLogController::class, 'show']);
});

////// Categories — Public (browse) /////

Route::get('/categories',                    [CategoryController::class, 'index']);
Route::get('/categories/with-listing-count', [CategoryController::class, 'withListingCount']);
Route::get('/categories/{category}',         [CategoryController::class, 'show']);

////// Admin — Categories /////

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/categories',              [CategoryController::class, 'store']);
    Route::put('/categories/{category}',    [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

////// Cart Items — Buyer (authenticated, detailed cart item management) /////

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/cart-items',              [CartItemController::class, 'index']);
    Route::post('/cart-items',             [CartItemController::class, 'store']);
    Route::get('/cart-items/grouped',      [CartItemController::class, 'grouped']);
    Route::get('/cart-items/breakdown',    [CartItemController::class, 'breakdown']);
    Route::get('/cart-items/{cartItem}',   [CartItemController::class, 'show']);
    Route::put('/cart-items/{cartItem}',   [CartItemController::class, 'update']);
    Route::delete('/cart-items/clear',     [CartItemController::class, 'clear']);
    Route::delete('/cart-items/{cartItem}',[CartItemController::class, 'destroy']);
});
