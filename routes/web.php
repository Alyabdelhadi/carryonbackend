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
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminGroupController;


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
Route::post('login',[AdminController::class, 'login'])->middleware('throttle:admin-login');

Route::group(['middleware' => ['auth', 'admin.same-origin']], function(){

/*
|-----------------------------------------
| Own account and logout: every admin
|-----------------------------------------
*/
Route::get('setting',[AdminController::class, 'setting']);
Route::post('setting',[AdminController::class, 'update']);
Route::get('logout',[AdminController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| Every page below needs its group permission (perm:<module>, see
| App\Support\AdminModules). The action (view/create/edit/delete) follows
| the controller method unless given, e.g. perm:versions,view.
|--------------------------------------------------------------------------
*/
Route::middleware('perm:dashboard')->group(function () {
    Route::get('home', [AdminController::class, 'home']);
    Route::get('/analytics/users-evolution', [AnalyticsController::class, 'usersEvolution'])->name('analytics.users.evolution');
});

Route::middleware('perm:services')->group(function () {
    Route::resource('services', ServiceController::class);
    Route::get('services/delete/{id}', [ServiceController::class, 'delete']);
    Route::get('serviceStatus', [ServiceController::class, 'serviceStatus']);
});

Route::middleware('perm:sliders')->group(function () {
    Route::resource('sliders', SliderController::class);
    Route::get('sliders/delete/{id}', [SliderController::class, 'delete']);
    Route::get('sliderStatus', [SliderController::class, 'sliderStatus']);
});

Route::middleware('perm:sliders2')->group(function () {
    Route::resource('sliders2', Slider2Controller::class);
    Route::get('sliders2/delete/{id}', [Slider2Controller::class, 'delete']);
    Route::get('slider2Status', [Slider2Controller::class, 'sliderStatus']);
});

Route::middleware('perm:tips')->group(function () {
    Route::resource('tips', TipController::class);
    Route::get('tips/delete/{id}', [TipController::class, 'delete']);
    Route::get('tipStatus', [TipController::class, 'tipStatus']);
});

Route::middleware('perm:weights')->group(function () {
    Route::resource('weights', WeightController::class);
    Route::get('weights/delete/{id}', [WeightController::class, 'delete']);
    Route::get('weightStatus', [WeightController::class, 'weightStatus']);
});

Route::middleware('perm:countries')->group(function () {
    Route::resource('countries', CountryController::class);
    Route::get('countries/delete/{id}', [CountryController::class, 'delete']);
    Route::get('countryStatus', [CountryController::class, 'countryStatus']);
});

Route::middleware('perm:cities')->group(function () {
    Route::resource('cities', CityController::class);
    Route::get('cities/delete/{id}', [CityController::class, 'delete']);
    Route::get('cityStatus', [CityController::class, 'cityStatus']);
});

Route::middleware('perm:pages')->group(function () {
    Route::resource('pages', PageController::class);
    Route::get('pages/delete/{id}', [PageController::class, 'delete']);
    Route::get('pageStatus', [PageController::class, 'pageStatus']);
});

Route::middleware('perm:texts')->group(function () {
    Route::get('texts', [TextController::class, 'index']);
    Route::post('texts', [TextController::class, 'save']);
});

Route::middleware('perm:categories')->group(function () {
    Route::resource('parcel_cate', ParcelCateController::class);
    Route::get('parcel_cate/delete/{id}', [ParcelCateController::class, 'delete']);
    Route::get('parcelCateStatus', [ParcelCateController::class, 'parcelCateStatus']);
});

// sending a push is its "create"
Route::get('push', [PushController::class, 'index'])->middleware('perm:push,view');
Route::post('send', [PushController::class, 'send'])->middleware('perm:push,create');
Route::post('send-user/{userId}', [PushController::class, 'sendToUser'])->middleware('perm:push,create');

Route::middleware('perm:users')->group(function () {
    Route::get('users/active', [AppUserController::class, 'active']);
    Route::get('users/inactive', [AppUserController::class, 'inactive']);
    Route::get('users/pending-verification', [AppUserController::class, 'pendingVerification']);
    Route::resource('users', AppUserController::class);
    Route::get('users/delete/{id}', [AppUserController::class, 'delete']);
    Route::get('userStatus', [AppUserController::class, 'userStatus']);
    Route::get('userVerification', [AppUserController::class, 'userVerification']);
    Route::get('users/{id}/identity', [AppUserController::class, 'identityFile']);
});

Route::middleware('perm:trips')->group(function () {
    Route::get('trips/one-time', [TripController::class, 'oneTime']);
    Route::get('trips/frequent', [TripController::class, 'frequent']);
    Route::get('trips/upcoming-routes', [TripController::class, 'upcomingRoutes'])->name('trips.upcoming.routes.view');
    Route::get('trips/upcoming-country-routes', [TripController::class, 'upcomingCountryRoutes'])->name('trips.upcoming.country.routes.view');
    Route::resource('trips', TripController::class);
});

Route::middleware('perm:payment_methods')->group(function () {
    Route::get('payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::get('payment-methods/{id}/edit', [PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
    Route::patch('payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
});

Route::middleware('perm:orders')->group(function () {
    Route::get('parcel_order', [ParcelOrderController::class, 'index']);
    Route::get('parcel_order_view', [ParcelOrderController::class, 'view']);
    Route::get('parcelOrderStatus', [ParcelOrderController::class, 'status']);
    Route::get('parcel_order/delete/{id}', [ParcelOrderController::class, 'delete']);
    Route::get('parcel_order/notify/{id}', [ParcelOrderController::class, 'notify'])->name('parcel.notify');
    Route::get('/parcel_order/{id}/edit', [ParcelOrderController::class, 'edit'])->name('parcel.edit');
    Route::put('/parcel_order/{id}', [ParcelOrderController::class, 'update'])->name('parcel.update');
    Route::post('/parcel_order/{id}/refund', [ParcelOrderController::class, 'refund'])->name('parcel.refund');
});

Route::prefix('notifications')->middleware('perm:notifications')->group(function () {
    Route::get('/', [NotificationTemplateController::class, 'index'])->name('notifications.index');
    Route::get('/add', [NotificationTemplateController::class, 'add'])->name('notifications.add');
    Route::post('/', [NotificationTemplateController::class, 'store'])->name('notifications.store');
    Route::get('/{id}/edit', [NotificationTemplateController::class, 'edit'])->name('notifications.edit');
    Route::patch('/{id}', [NotificationTemplateController::class, 'update'])->name('notifications.update');
    Route::get('/delete/{id}', [NotificationTemplateController::class, 'destroy'])->name('notifications.destroy');
});

Route::prefix('emails')->middleware('perm:emails')->group(function () {
    Route::get('/', [EmailTemplateController::class, 'index'])->name('emails.index');
    Route::get('/add', [EmailTemplateController::class, 'add'])->name('emails.add');
    Route::post('/', [EmailTemplateController::class, 'store'])->name('emails.store');
    Route::get('/{id}/edit', [EmailTemplateController::class, 'edit'])->name('emails.edit');
    Route::patch('/{id}', [EmailTemplateController::class, 'update'])->name('emails.update');
    Route::get('/delete/{id}', [EmailTemplateController::class, 'destroy'])->name('emails.destroy');
});

// Carrier wallets and payout requests
Route::middleware('perm:wallets')->group(function () {
    Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::post('wallets/{id}/adjust', [WalletController::class, 'adjust'])->name('wallets.adjust');
});
// a real detail page (show() elsewhere is the "Add" form)
Route::get('wallets/{id}', [WalletController::class, 'show'])->name('wallets.show')->middleware('perm:wallets,view');
Route::middleware('perm:payouts')->group(function () {
    Route::get('payouts', [PayoutRequestController::class, 'index'])->name('payouts.index');
    Route::post('payouts/{id}/paid', [PayoutRequestController::class, 'paid'])->name('payouts.paid');
    Route::post('payouts/{id}/reject', [PayoutRequestController::class, 'reject'])->name('payouts.reject');
});

// these GET pages are named edit(), but opening them only needs "view"
Route::get('app-settings', [AppSettingController::class, 'edit'])->name('app-settings.edit')->middleware('perm:app_settings,view');
Route::post('app-settings', [AppSettingController::class, 'update'])->name('app-settings.update')->middleware('perm:app_settings,edit');
Route::get('versions', [VersionController::class, 'edit'])->name('edit')->middleware('perm:versions,view');
Route::post('versions', [VersionController::class, 'update'])->name('update')->middleware('perm:versions,edit');

Route::middleware('perm:statuses')->group(function () {
    Route::resource('delivery_statuses', DeliveryStatusController::class);
    Route::get('delivery_statuses/delete/{id}', [DeliveryStatusController::class, 'delete']);
    Route::get('deliveryStatusStatus', [DeliveryStatusController::class, 'deliveryStatusStatus']);
});

// Dashboard accounts and their groups
Route::middleware('perm:admins')->group(function () {
    Route::get('admins', [AdminUserController::class, 'index'])->name('admins.index');
    Route::get('admins/create', [AdminUserController::class, 'create'])->name('admins.create');
    Route::post('admins', [AdminUserController::class, 'store'])->name('admins.store');
    Route::get('admins/{id}/edit', [AdminUserController::class, 'edit'])->name('admins.edit');
    Route::put('admins/{id}', [AdminUserController::class, 'update'])->name('admins.update');
    Route::delete('admins/{id}', [AdminUserController::class, 'destroy'])->name('admins.destroy');
});
Route::middleware('perm:admin_groups')->group(function () {
    Route::get('admin-groups', [AdminGroupController::class, 'index'])->name('admin-groups.index');
    Route::get('admin-groups/create', [AdminGroupController::class, 'create'])->name('admin-groups.create');
    Route::post('admin-groups', [AdminGroupController::class, 'store'])->name('admin-groups.store');
    Route::get('admin-groups/{id}/edit', [AdminGroupController::class, 'edit'])->name('admin-groups.edit');
    Route::put('admin-groups/{id}', [AdminGroupController::class, 'update'])->name('admin-groups.update');
    Route::delete('admin-groups/{id}', [AdminGroupController::class, 'destroy'])->name('admin-groups.destroy');
});

});