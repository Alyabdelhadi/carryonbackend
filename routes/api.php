<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\AppSettingController;

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

Route::post('signup', [ApiController::class, 'signup']);
Route::post('login', [ApiController::class, 'login']);
Route::post('verify', [ApiController::class, 'verify']);
Route::post('submitVerification', [ApiController::class, 'submitVerification']);
Route::get('userInfo', [ApiController::class, 'userInfo']);
Route::post('updateInfo', [ApiController::class, 'updateInfo']);
Route::delete('deleteUser',[ApiController::class, 'deleteUser']);
Route::post('/sendResetLink', [ApiController::class, 'sendPasswordResetLink']);
Route::post('/resetPassword', [ApiController::class, 'resetPassword']);

Route::post('rate', [ApiController::class, 'rate']);

Route::get('services', [ApiController::class, 'getServices']);

Route::get('parcelCategories', [ApiController::class, 'getParcelCategories']);

Route::get('sliders', [ApiController::class, 'getSliders']);

Route::get('sliders2', [ApiController::class, 'getSliders2']);

Route::get('getTexts',[ApiController::class, 'getTexts']);

Route::get('tips', [ApiController::class, 'getTips']);

Route::get('weights', [ApiController::class, 'getWeights']);

Route::get('pages', [ApiController::class, 'getPages']);
Route::get('page', [ApiController::class, 'getPageById']);

Route::get('getAddresses',[ApiController::class, 'getAddresses']);
Route::post('createAddress',[ApiController::class, 'createAddress']);
Route::post('updateAddress',[ApiController::class, 'updateAddress']);
Route::delete('deleteAddress',[ApiController::class, 'deleteAddress']);

Route::get('getParcelCategories',[ApiController::class, 'getParcelCategories']);

Route::get('parcelOrder', [ApiController::class, 'getParcelOrderById']);
Route::post('createParcelOrder', [ApiController::class, 'createParcelOrder']);
Route::post('updateParcelOrder', [ApiController::class, 'updateParcelOrder']);
Route::post('assignParcelOrder', [ApiController::class, 'assignParcelOrder']);
Route::post('pickupParcelOrder', [ApiController::class, 'pickupParcelOrder']);
Route::post('unassignParcelOrder', [ApiController::class, 'unassignParcelOrder']);
Route::post('deliverParcelOrder', [ApiController::class, 'deliverParcelOrder']);
Route::post('transitParcelOrder', [ApiController::class, 'transitParcelOrder']);
Route::post('cancelParcelOrder', [ApiController::class, 'cancelParcelOrder']);
Route::post('extendParcelOrder', [ApiController::class, 'extendParcelOrder']);
Route::get('unassginedParcelOrders', [ApiController::class, 'getUnassginedParcelOrders']);
Route::get('myCreatedParcelOrders', [ApiController::class, 'getMyCreatedParcelOrders']);
Route::get('myCarriedParcelOrders', [ApiController::class, 'getMyCarriedParcelOrders']);

Route::get('countries', [ApiController::class, 'getCountries']);
Route::get('/countries/{id}/cities', [ApiController::class, 'getCitiesByCountry']);



Route::post('/trips', [ApiController::class, 'createTrip']);
Route::put('/trips/{id}', [ApiController::class, 'updateTrip']);
Route::delete('/trips/{id}', [ApiController::class, 'deleteTrip']);
Route::get('/trips', [ApiController::class, 'getTrips']);
Route::get('/trips/{id}', [ApiController::class, 'getTrip']);
Route::get('/trips/carrier/{carrierId}', [ApiController::class, 'getTripsByCarrier']);
Route::get('/carriers/{carrierId}/matching-parcel-orders', [ApiController::class, 'getMatchingParcelOrdersByCarrier']);
Route::post(
    '/carriers/{carrierId}/matching-parcel-orders/mark-read',
    [ApiController::class, 'markMatchingParcelOrdersAsRead']
);

Route::get('/appVersions', [ApiController::class, 'getAppVersions']);
Route::get('/appSettings', [AppSettingController::class, 'api']);

Route::get('/analytics', [ApiController::class, 'getAnalytics']);

Route::get(
    '/payment-methods',
    [ApiController::class, 'getPaymentMethods']
);

Route::post(
    '/payments/stripe/create',
    [ApiController::class, 'createStripePayment']
);

Route::post(
    '/payments/stripe/webhook',
    [StripeWebhookController::class, 'handle']
);
