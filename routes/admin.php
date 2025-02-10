<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\DigitalProductController;
use App\Http\Controllers\FlashDealController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PickupPointController;
use App\Http\Controllers\ProductBulkUploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductQueryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerWithdrawRequestController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ZoneController;

/*
  |--------------------------------------------------------------------------
  | Admin Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register admin routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
//Update Routes
Route::controller(UpdateController::class)->group(function () {
    Route::post('/update', 'step0')->name('update');
    Route::get('/update/step1', 'step1')->name('update.step1');
    Route::get('/update/step2', 'step2')->name('update.step2');
});

Route::get('/admin', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard')->middleware(['auth', 'admin']);
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {


    // website setting
    Route::group(['prefix' => 'website'], function () {

        // Staff Roles
//        Route::resource('roles', RoleController::class);
//        Route::controller(RoleController::class)->group(function () {
//            Route::get('/roles/edit/{id}', 'edit')->name('roles.edit');
//            Route::get('/roles/destroy/{id}', 'destroy')->name('roles.destroy');
//
//            // Add Permissiom
//            Route::post('/roles/add_permission', 'add_permission')->name('roles.permission');
//        });

        // Staff
//        Route::resource('staffs', StaffController::class);
//        Route::get('/staffs/destroy/{id}', [StaffController::class, 'destroy'])->name('staffs.destroy');


        Route::view('/system/update', 'backend.system.update')->name('system_update');
        Route::view('/system/server-status', 'backend.system.server_status')->name('system_server');

        // uploaded files
        Route::resource('/uploaded-files', AizUploadController::class)->except('destroy');
        Route::controller(AizUploadController::class)->group(function () {
            Route::any('/uploaded-files/file-info', 'file_info')->name('uploaded-files.info');
            Route::get('/uploaded-files/destroy/{id}', 'destroy')->name('uploaded-files.destroy');
        });


        Route::get('/clear-cache', [AdminController::class, 'clearCache'])->name('cache.clear');

//        Route::get('/admin-permissions', [RoleController::class, 'create_admin_permissions']);
    });
});
