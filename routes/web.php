<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\TextController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\Slider2Controller;
use App\Http\Controllers\WeightController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\AppUserController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ParcelCateController;
use App\Http\Controllers\ParcelOrderController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\DeliveryStatusController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PayoutRequestController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[AdminController::class, 'index']);
Route::get('login',[AdminController::class, 'index'])->name('login');
Route::post('login',[AdminController::class, 'login']);

Route::group(['middleware' => 'auth'], function(){

/*
|-----------------------------------------
|Dashboard and Account Setting & Logout
|-----------------------------------------
*/
Route::get('home',[AdminController::class, 'home']);
Route::get('setting',[AdminController::class, 'setting']);
Route::post('setting',[AdminController::class, 'update']);
Route::get('logout',[AdminController::class, 'logout']);



/*
|-----------------------------
|Manage Services
|-----------------------------
*/
Route::resource('services', ServiceController::class);
Route::get('services/delete/{id}', [ServiceController::class, 'delete']);
Route::get('serviceStatus', [ServiceController::class, 'serviceStatus']);



/*
|-----------------------------
|Manage Sliders
|-----------------------------
*/
Route::resource('sliders', SliderController::class);
Route::get('sliders/delete/{id}', [SliderController::class, 'delete']);
Route::get('sliderStatus', [SliderController::class, 'sliderStatus']);

/*
|-----------------------------
|Manage Sliders2
|-----------------------------
*/
Route::resource('sliders2', Slider2Controller::class);
Route::get('sliders2/delete/{id}', [Slider2Controller::class, 'delete']);
Route::get('slider2Status', [Slider2Controller::class, 'sliderStatus']);



/*
|-----------------------------
|Manage Tips
|-----------------------------
*/
Route::resource('tips', TipController::class);
Route::get('tips/delete/{id}', [TipController::class, 'delete']);
Route::get('tipStatus', [TipController::class, 'tipStatus']);



/*
|-----------------------------
|Manage Weights
|-----------------------------
*/
Route::resource('weights', WeightController::class);
Route::get('weights/delete/{id}', [WeightController::class, 'delete']);
Route::get('weightStatus', [WeightController::class, 'weightStatus']);



/*
|-----------------------------
|Manage Countries
|-----------------------------
*/
Route::resource('countries', CountryController::class);
Route::get('countries/delete/{id}', [CountryController::class, 'delete']);
Route::get('countryStatus', [CountryController::class, 'countryStatus']);

/*
|-----------------------------
|Manage Cities
|-----------------------------
*/
Route::resource('cities', CityController::class);
Route::get('cities/delete/{id}', [CityController::class, 'delete']);
Route::get('cityStatus', [CityController::class, 'cityStatus']);



/*
|-----------------------------
|Manage Pages
|-----------------------------
*/
Route::resource('pages', PageController::class);
Route::get('pages/delete/{id}', [PageController::class, 'delete']);
Route::get('pageStatus', [PageController::class, 'pageStatus']);


/*
|---------------------------------------
|Manage App Texts
|---------------------------------------
*/
Route::get('texts', [TextController::class, 'index']);
Route::post('texts', [TextController::class, 'save']);



/*
|-----------------------------
|Manage Parcel Categories
|-----------------------------
*/
Route::resource('parcel_cate', ParcelCateController::class);
Route::get('parcel_cate/delete/{id}', [ParcelCateController::class, 'delete']);
Route::get('parcelCateStatus', [ParcelCateController::class, 'parcelCateStatus']);


/*
|---------------------------------------
|Manage Push Notification & Reporting
|---------------------------------------
*/

Route::get('push', [PushController::class, 'index']);
Route::post('send', [PushController::class, 'send']);
Route::post('send-user/{userId}', [PushController::class, 'sendToUser']);

/*
|-------------------------------------
|App Users
|-------------------------------------
*/
Route::get('users/active', [AppUserController::class, 'active']);
Route::get('users/inactive', [AppUserController::class, 'inactive']);
Route::resource('users', AppUserController::class);
Route::get('users/delete/{id}', [AppUserController::class, 'delete']);
Route::get('userStatus', [AppUserController::class, 'userStatus']);


/*
|-------------------------------------
|Trips
|-------------------------------------
*/
Route::get('trips/one-time', [TripController::class, 'oneTime']);
Route::get('trips/frequent', [TripController::class, 'frequent']);
Route::get('trips/upcoming-routes', [TripController::class, 'upcomingRoutes'])->name('trips.upcoming.routes.view');
Route::get('trips/upcoming-country-routes', [TripController::class, 'upcomingCountryRoutes'])->name('trips.upcoming.country.routes.view');

Route::resource('trips', TripController::class);




/*
|-----------------------------
| Manage Payment Methods
|-----------------------------
*/
Route::get(
    'payment-methods',
    [PaymentMethodController::class, 'index']
)->name('payment-methods.index');

Route::get(
    'payment-methods/{id}/edit',
    [PaymentMethodController::class, 'edit']
)->name('payment-methods.edit');

Route::patch(
    'payment-methods/{id}',
    [PaymentMethodController::class, 'update']
)->name('payment-methods.update');

/*
|-------------------------------------
|Manage Parcel Orders
|-------------------------------------
*/
Route::get('parcel_order', [ParcelOrderController::class, 'index']);
Route::get('parcel_order_view', [ParcelOrderController::class, 'view']);
Route::get('parcelOrderStatus', [ParcelOrderController::class, 'status']);
Route::get('parcel_order/delete/{id}', [ParcelOrderController::class, 'delete']);
Route::get('parcel_order/notify/{id}', [ParcelOrderController::class, 'notify'])->name('parcel.notify');
Route::get('/parcel_order/{id}/edit', [ParcelOrderController::class, 'edit'])->name('parcel.edit');
Route::put('/parcel_order/{id}', [ParcelOrderController::class, 'update'])->name('parcel.update');
Route::post('/parcel_order/{id}/refund', [ParcelOrderController::class, 'refund'])->name('parcel.refund');

/*
|-----------------------------------------
| Notification Templates Management
|-----------------------------------------
*/
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationTemplateController::class, 'index'])->name('notifications.index');
    Route::get('/add', [NotificationTemplateController::class, 'add'])->name('notifications.add');
    Route::post('/', [NotificationTemplateController::class, 'store'])->name('notifications.store');
    Route::get('/{id}/edit', [NotificationTemplateController::class, 'edit'])->name('notifications.edit');
    Route::patch('/{id}', [NotificationTemplateController::class, 'update'])->name('notifications.update');
    Route::get('/delete/{id}', [NotificationTemplateController::class, 'destroy'])->name('notifications.destroy');
});


/*
|-----------------------------------------
| Email Templates Management
|-----------------------------------------
*/
Route::prefix('emails')->group(function () {
    Route::get('/', [EmailTemplateController::class, 'index'])->name('emails.index');
    Route::get('/add', [EmailTemplateController::class, 'add'])->name('emails.add');
    Route::post('/', [EmailTemplateController::class, 'store'])->name('emails.store');
    Route::get('/{id}/edit', [EmailTemplateController::class, 'edit'])->name('emails.edit');
    Route::patch('/{id}', [EmailTemplateController::class, 'update'])->name('emails.update');
    Route::get('/delete/{id}', [EmailTemplateController::class, 'destroy'])->name('emails.destroy');
});





// Carrier wallets and payout requests
Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
Route::get('wallets/{id}', [WalletController::class, 'show'])->name('wallets.show');
Route::post('wallets/{id}/adjust', [WalletController::class, 'adjust'])->name('wallets.adjust');
Route::get('payouts', [PayoutRequestController::class, 'index'])->name('payouts.index');
Route::post('payouts/{id}/paid', [PayoutRequestController::class, 'paid'])->name('payouts.paid');
Route::post('payouts/{id}/reject', [PayoutRequestController::class, 'reject'])->name('payouts.reject');

Route::get('app-settings', [AppSettingController::class, 'edit'])->name('app-settings.edit');
Route::post('app-settings', [AppSettingController::class, 'update'])->name('app-settings.update');

Route::get('versions', [VersionController::class, 'edit'])->name('edit');
Route::post('versions', [VersionController::class, 'update'])->name('update');


/*
|-----------------------------
|Manage Delivery Statuses
|-----------------------------
*/
Route::resource('delivery_statuses', DeliveryStatusController::class);
Route::get('delivery_statuses/delete/{id}', [DeliveryStatusController::class, 'delete']);
Route::get('deliveryStatusStatus', [DeliveryStatusController::class, 'deliveryStatusStatus']);




Route::get('/analytics/users-evolution', [AnalyticsController::class, 'usersEvolution'])
     ->name('analytics.users.evolution');

});