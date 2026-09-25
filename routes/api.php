<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\IdentityController;
use App\Http\Controllers\AppAuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\WalletApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// signup and identity/verify each run a paid Shufti check: rate limited
Route::post('signup', [ApiController::class, 'signup'])->middleware('throttle:identity');
Route::post('login', [ApiController::class, 'login'])->middleware('throttle:login');

// Mobile app tokens (Sanctum access token + rotating refresh token)
Route::post('auth/refresh', [AppAuthController::class, 'refresh'])->middleware('throttle:login');
Route::post('auth/logout', [AppAuthController::class, 'logout']);
Route::get('auth/whoami', [AppAuthController::class, 'whoami'])->middleware('app.auth');
Route::post('verify', [ApiController::class, 'verify']);
Route::post('submitVerification', [ApiController::class, 'submitVerification']);
Route::get('userInfo', [ApiController::class, 'userInfo'])->middleware('app.auth'); // own profile, or a public profile of another user
Route::post('updateInfo', [ApiController::class, 'updateInfo'])->middleware('app.auth:id');
Route::delete('deleteUser',[ApiController::class, 'deleteUser'])->middleware('app.auth:user_id');
Route::post('/sendResetLink', [ApiController::class, 'sendPasswordResetLink']);
Route::post('/resetPassword', [ApiController::class, 'resetPassword']);

// Password reset by emailed 6-digit code (replaces the link flow above,
// kept for the old Ionic app)
Route::middleware('throttle:password-reset')->group(function () {
    Route::post('password/request', [PasswordResetController::class, 'request']);
    Route::post('password/verify', [PasswordResetController::class, 'verify']);
    Route::post('password/reset', [PasswordResetController::class, 'reset']);
});

// Identity verification (Shufti runs on the server; see IdentityController)
Route::post('identity/verify', [IdentityController::class, 'verify'])->middleware(['app.auth:user_id', 'throttle:identity']);
Route::get('identity/status', [IdentityController::class, 'status'])->middleware(['app.auth:user_id', 'throttle:identity-status']);
Route::post('identity/shufti/callback', [IdentityController::class, 'shuftiCallback']);

Route::post('rate', [ApiController::class, 'rate'])->middleware('app.auth'); // actor = order creator, rated = its carrier

Route::get('services', [ApiController::class, 'getServices']);

Route::get('parcelCategories', [ApiController::class, 'getParcelCategories']);

Route::get('sliders', [ApiController::class, 'getSliders']);

Route::get('sliders2', [ApiController::class, 'getSliders2']);

Route::get('getTexts',[ApiController::class, 'getTexts']);

Route::get('tips', [ApiController::class, 'getTips']);

Route::get('weights', [ApiController::class, 'getWeights']);

Route::get('pages', [ApiController::class, 'getPages']);
Route::get('page', [ApiController::class, 'getPageById']);

Route::get('getAddresses',[ApiController::class, 'getAddresses'])->middleware('app.auth:userid');
Route::post('createAddress',[ApiController::class, 'createAddress'])->middleware('app.auth:user_id');
Route::post('updateAddress',[ApiController::class, 'updateAddress'])->middleware('app.auth:user_id');
Route::delete('deleteAddress',[ApiController::class, 'deleteAddress'])->middleware('app.auth:user_id');

Route::get('getParcelCategories',[ApiController::class, 'getParcelCategories']);

Route::get('parcelOrder', [ApiController::class, 'getParcelOrderById'])->middleware('app.auth');
Route::post('createParcelOrder', [ApiController::class, 'createParcelOrder'])->middleware('app.auth:user_id');
Route::post('updateParcelOrder', [ApiController::class, 'updateParcelOrder'])->middleware('app.auth:user_id');
Route::post('assignParcelOrder', [ApiController::class, 'assignParcelOrder'])->middleware('app.auth:user_id');
Route::post('pickupParcelOrder', [ApiController::class, 'pickupParcelOrder'])->middleware('app.auth:user_id');
Route::post('unassignParcelOrder', [ApiController::class, 'unassignParcelOrder'])->middleware('app.auth'); // creator or carrier
Route::post('deliverParcelOrder', [ApiController::class, 'deliverParcelOrder'])->middleware('app.auth:user_id');
Route::post('transitParcelOrder', [ApiController::class, 'transitParcelOrder'])->middleware('app.auth:user_id');
Route::post('cancelParcelOrder', [ApiController::class, 'cancelParcelOrder'])->middleware('app.auth:user_id');
Route::post('extendParcelOrder', [ApiController::class, 'extendParcelOrder'])->middleware('app.auth:user_id');
Route::get('unassginedParcelOrders', [ApiController::class, 'getUnassginedParcelOrders'])->middleware('app.auth');
Route::get('myCreatedParcelOrders', [ApiController::class, 'getMyCreatedParcelOrders'])->middleware('app.auth:user_id');
Route::get('myCarriedParcelOrders', [ApiController::class, 'getMyCarriedParcelOrders'])->middleware('app.auth:user_id');

Route::get('countries', [ApiController::class, 'getCountries']);
Route::get('/countries/{id}/cities', [ApiController::class, 'getCitiesByCountry']);



Route::post('/trips', [ApiController::class, 'createTrip'])->middleware('app.auth:carrier_id');
Route::put('/trips/{id}', [ApiController::class, 'updateTrip'])->middleware('app.auth:carrier_id'); // + owner check
Route::delete('/trips/{id}', [ApiController::class, 'deleteTrip'])->middleware('app.auth'); // + owner check
Route::get('/trips', [ApiController::class, 'getTrips']);
Route::get('/trips/{id}', [ApiController::class, 'getTrip']);
Route::get('/trips/carrier/{carrierId}', [ApiController::class, 'getTripsByCarrier'])->middleware('app.auth:carrierId');
Route::get('/carriers/{carrierId}/matching-parcel-orders', [ApiController::class, 'getMatchingParcelOrdersByCarrier'])->middleware('app.auth:carrierId');
Route::post(
    '/carriers/{carrierId}/matching-parcel-orders/mark-read',
    [ApiController::class, 'markMatchingParcelOrdersAsRead']
)->middleware('app.auth:carrierId');

Route::get('/appVersions', [ApiController::class, 'getAppVersions']);
Route::get('/appSettings', [AppSettingController::class, 'api']);
Route::get('/stats', [AppSettingController::class, 'stats']);

// api/analytics removed: platform-wide admin counts, unused by the apps

Route::get(
    '/payment-methods',
    [ApiController::class, 'getPaymentMethods']
);

Route::post(
    '/payments/stripe/create',
    [ApiController::class, 'createStripePayment']
)->middleware('app.auth:user_id');

Route::post('/payments/stripe/sync', [ApiController::class, 'syncStripePayment'])->middleware('app.auth:user_id');

Route::post(
    '/payments/stripe/webhook',
    [StripeWebhookController::class, 'handle']
);

// Carrier wallet and payouts (card-paid orders only)
Route::get('/wallet', [WalletApiController::class, 'show'])->middleware('app.auth:user_id');
Route::get('/wallet/transactions', [WalletApiController::class, 'transactions'])->middleware('app.auth:user_id');
Route::get('/payouts', [WalletApiController::class, 'payouts'])->middleware('app.auth:user_id');
Route::post('/payouts', [WalletApiController::class, 'requestPayout'])->middleware('app.auth:user_id');
Route::post('/payouts/{id}/cancel', [WalletApiController::class, 'cancelPayout'])->middleware('app.auth:user_id');
