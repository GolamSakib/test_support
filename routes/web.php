<?php

use App\Events\supportPersonAllData;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductQueryController;
use App\Http\Controllers\Payment\BkashController;
use App\Http\Controllers\Payment\NagadController;

use App\Http\Controllers\Payment\PaykuController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\DigitalProductController;
use App\Http\Controllers\Payment\IyzicoController;
use App\Http\Controllers\Payment\PaypalController;
use App\Http\Controllers\Payment\StripeController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\Payment\NgeniusController;
use App\Http\Controllers\Payment\PayhereController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\Payment\AamarpayController;
use App\Http\Controllers\Payment\PaystackController;
use App\Http\Controllers\Payment\RazorpayController;
use App\Http\Controllers\Payment\VoguepayController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Payment\InstamojoController;
use App\Http\Controllers\Payment\SslcommerzController;
use App\Http\Controllers\Payment\MercadopagoController;
use App\Http\Controllers\Payment\AuthorizenetController;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

// Route::controller(DemoController::class)->group(function () {
//     Route::get('/demo/cron_1', 'cron_1');
//     Route::get('/demo/cron_2', 'cron_2');
//     Route::get('/convert_assets', 'convert_assets');
//     Route::get('/convert_category', 'convert_category');
//     Route::get('/convert_tax', 'convertTaxes');
//     Route::get('/insert_product_variant_forcefully', 'insert_product_variant_forcefully');
//     Route::get('/update_seller_id_in_orders/{id_min}/{id_max}', 'update_seller_id_in_orders');
//     Route::get('/migrate_attribute_values', 'migrate_attribute_values');
// });

Route::get('/refresh-csrf', function () {
    return csrf_token();
});

Route::get('/test-backup', [App\Http\Controllers\BackupController::class, 'testBackup']);


Route::get('/test',function(){
    broadcast(new SupportPersonAllData('Test message for debugging'));
    return 'Event broadcasted!';
});

// AIZ Uploader
Route::controller(AizUploadController::class)->group(function () {
    Route::post('/aiz-uploader', 'show_uploader');
    Route::post('/aiz-uploader/upload', 'upload');
    Route::get('/aiz-uploader/get_uploaded_files', 'get_uploaded_files');
    Route::post('/aiz-uploader/get_file_by_ids', 'get_preview_files');
    Route::get('/aiz-uploader/download/{id}', 'attachment_download')->name('download_attachment');
});

Auth::routes(['verify' => true]);

// Login
Route::controller(LoginController::class)->group(function () {
    Route::get('/logout', 'logout');
    Route::get('/social-login/redirect/{provider}', 'redirectToProvider')->name('social.login');
    Route::get('/social-login/{provider}/callback', 'handleProviderCallback')->name('social.callback');
});

Route::controller(VerificationController::class)->group(function () {
//    Route::get('/email/resend', 'resend')->name('verification.resend');
    Route::get('/verification-confirmation/{code}', 'verification_confirmation')->name('email.verification.confirmation');
});
 Route::get('/', function(){
        return response([
            "message" => "Welcome to the API of the Support App",
        ],200);
    })->name('home');


// // Language Switch
//Route::post('/language', [LanguageController::class, 'changeLanguage'])->name('language.change');

// // Currency Switch
// Route::post('/currency', [CurrencyController::class, 'changeCurrency'])->name('currency.change');


// Route::get('/sitemap.xml', function() {
//     return base_path('sitemap.xml');
// });

// // Classified Product
// Route::controller(CustomerProductController::class)->group(function () {
//     Route::get('/customer-products', 'customer_products_listing')->name('customer.products');
//     Route::get('/customer-products?category={category_slug}', 'search')->name('customer_products.category');
//     Route::get('/customer-products?city={city_id}', 'search')->name('customer_products.city');
//     Route::get('/customer-products?q={search}', 'search')->name('customer_products.search');
//     Route::get('/customer-product/{slug}', 'customer_product')->name('customer.product');
// });

// // Search
// Route::controller(SearchController::class)->group(function () {
//     Route::get('/search', 'index')->name('search');
//     Route::get('/search?keyword={search}', 'index')->name('suggestion.search');
//     Route::post('/ajax-search', 'ajax_search')->name('search.ajax');
//     Route::get('/category/{category_slug}', 'listingByCategory')->name('products.category');
//     Route::get('/brand/{brand_slug}', 'listingByBrand')->name('products.brand');
// });

// // Cart
// Route::controller(CartController::class)->group(function () {
//     Route::get('/cart', 'index')->name('cart');
//     Route::post('/cart/show-cart-modal', 'showCartModal')->name('cart.showCartModal');
//     Route::post('/cart/addtocart', 'addToCart')->name('cart.addToCart');
//     Route::post('/cart/removeFromCart', 'removeFromCart')->name('cart.removeFromCart');
//     Route::post('/cart/updateQuantity', 'updateQuantity')->name('cart.updateQuantity');
// });

// //Paypal START
// Route::controller(PaypalController::class)->group(function () {
//     Route::get('/paypal/payment/done', 'getDone')->name('payment.done');
//     Route::get('/paypal/payment/cancel', 'getCancel')->name('payment.cancel');
// });
// //Paypal END

// //Mercadopago START
// Route::controller(MercadopagoController::class)->group(function () {
//     Route::any('/mercadopago/payment/done', 'paymentstatus')->name('mercadopago.done');
//     Route::any('/mercadopago/payment/cancel', 'callback')->name('mercadopago.cancel');
// });
// //Mercadopago

// // SSLCOMMERZ Start
// Route::controller(SslcommerzController::class)->group(function () {
//     Route::get('/sslcommerz/pay', 'index');
//     Route::POST('/sslcommerz/success', 'success');
//     Route::POST('/sslcommerz/fail', 'fail');
//     Route::POST('/sslcommerz/cancel', 'cancel');
//     Route::POST('/sslcommerz/ipn', 'ipn');
// });
// //SSLCOMMERZ END

// //Stipe Start
// Route::controller(StripeController::class)->group(function () {
//     Route::get('stripe', 'stripe');
//     Route::post('/stripe/create-checkout-session', 'create_checkout_session')->name('stripe.get_token');
//     Route::any('/stripe/payment/callback', 'callback')->name('stripe.callback');
//     Route::get('/stripe/success', 'success')->name('stripe.success');
//     Route::get('/stripe/cancel', 'cancel')->name('stripe.cancel');
// });
// //Stripe END

// // Compare
// Route::controller(CompareController::class)->group(function () {
//     Route::get('/compare', 'index')->name('compare');
//     Route::get('/compare/reset', 'reset')->name('compare.reset');
//     Route::post('/compare/addToCompare', 'addToCompare')->name('compare.addToCompare');
// });

// // Subscribe
// Route::resource('subscribers', SubscriberController::class);

// Route::group(['middleware' => ['user', 'verified', 'unbanned']], function() {

//     Route::controller(HomeController::class)->group(function () {
//         Route::get('/dashboard', 'dashboard')->name('dashboard');
//         Route::get('/profile', 'profile')->name('profile');
//         Route::post('/new-user-verification', 'new_verify')->name('user.new.verify');
//         Route::post('/new-user-email', 'update_email')->name('user.change.email');
//         Route::post('/user/update-profile', 'userProfileUpdate')->name('user.profile.update');
//     });

//     Route::get('/all-notifications', [NotificationController::class, 'index'])->name('all-notifications');

// });

// Route::group(['middleware' => ['customer', 'verified', 'unbanned']], function() {

//     // Checkout Routs
//     Route::group(['prefix' => 'checkout'], function() {
//         Route::controller(CheckoutController::class)->group(function () {
//             Route::get('/', 'get_shipping_info')->name('checkout.shipping_info');
//             Route::any('/delivery_info', 'store_shipping_info')->name('checkout.store_shipping_infostore');
//             Route::post('/payment_select', 'store_delivery_info')->name('checkout.store_delivery_info');
//             Route::get('/order-confirmed', 'order_confirmed')->name('order_confirmed');
//             Route::post('/payment', 'checkout')->name('payment.checkout');
//             Route::post('/get_pick_up_points', 'get_pick_up_points')->name('shipping_info.get_pick_up_points');
//             Route::get('/payment-select', 'get_payment_info')->name('checkout.payment_info');
//             Route::post('/apply_coupon_code', 'apply_coupon_code')->name('checkout.apply_coupon_code');
//             Route::post('/remove_coupon_code', 'remove_coupon_code')->name('checkout.remove_coupon_code');
//             //Club point
//             Route::post('/apply-club-point', 'apply_club_point')->name('checkout.apply_club_point');
//             Route::post('/remove-club-point', 'remove_club_point')->name('checkout.remove_club_point');
//         });
//     });

//     // Purchase History
//     Route::resource('purchase_history', PurchaseHistoryController::class);
//     Route::controller(PurchaseHistoryController::class)->group(function () {
//         Route::get('/purchase_history/details/{id}', 'purchase_history_details')->name('purchase_history.details');
//         Route::get('/purchase_history/destroy/{id}', 'order_cancel')->name('purchase_history.destroy');
//         Route::get('digital_purchase_history', 'digital_index')->name('digital_purchase_history.index');
//     });

//     // Wishlist
//     Route::resource('wishlists', WishlistController::class);
//     Route::post('/wishlists/remove', [WishlistController::class, 'remove'])->name('wishlists.remove');

//     // Wallet
//     Route::controller(WalletController::class)->group(function () {
//         Route::get('/wallet', 'index')->name('wallet.index');
//         Route::post('/recharge', 'recharge')->name('wallet.recharge');
//     });

//     // Support Ticket
//     Route::resource('support_ticket', SupportTicketController::class);
//     Route::post('support_ticket/reply', [SupportTicketController::class, 'seller_store'])->name('support_ticket.seller_store');

//     // Customer Package
//     Route::post('/customer_packages/purchase',[CustomerPackageController::class, 'purchase_package'])->name('customer_packages.purchase');

//     // Customer Product
//     Route::resource('customer_products', CustomerProductController::class);
//     Route::controller(CustomerProductController::class)->group(function () {
//         Route::get('/customer_products/{id}/edit', 'edit')->name('customer_products.edit');
//         Route::post('/customer_products/published', 'updatePublished')->name('customer_products.published');
//         Route::post('/customer_products/status', 'updateStatus')->name('customer_products.update.status');
//         Route::get('/customer_products/destroy/{id}', 'destroy')->name('customer_products.destroy');
//     });

//     // Product Review
//     Route::post('/product_review_modal', [ReviewController::class, 'product_review_modal'])->name('product_review_modal');

//     // Digital Product
//     Route::controller(DigitalProductController::class)->group(function () {
//         Route::get('/digital-products/download/{id}', 'download')->name('digital-products.download');
//     });

// });

// Route::group(['middleware' => ['auth']], function() {

//     Route::get('invoice/{order_id}', [InvoiceController::class, 'invoice_download'])->name('invoice.download');

//     // Reviews
//     Route::resource('/reviews', ReviewController::class);

//     // Product Conversation
//     Route::resource('conversations', ConversationController::class);
//     Route::controller(ConversationController::class)->group(function () {
//         Route::get('/conversations/destroy/{id}', 'destroy')->name('conversations.destroy');
//         Route::post('conversations/refresh', 'refresh')->name('conversations.refresh');
//     });

//     // Product Query
//     Route::resource('product-queries', ProductQueryController::class);

//     Route::resource('messages', MessageController::class);

//     //Address
//     Route::resource('addresses', AddressController::class);
//     Route::controller(AddressController::class)->group(function () {
//         Route::post('/get-states', 'getStates')->name('get-state');
//         Route::post('/get-cities', 'getCities')->name('get-city');
//         Route::post('/addresses/update/{id}', 'update')->name('addresses.update');
//         Route::get('/addresses/destroy/{id}', 'destroy')->name('addresses.destroy');
//         Route::get('/addresses/set_default/{id}', 'set_default')->name('addresses.set_default');
//     });
// });

// Route::resource('shops', ShopController::class);

// Route::get('/instamojo/payment/pay-success', [InstamojoController::class, 'success'])->name('instamojo.success');

// Route::post('rozer/payment/pay-success', [RazorpayController::class, 'payment'])->name('payment.rozer');

// Route::get('/paystack/payment/callback', [PaystackController::class, 'handleGatewayCallback']);

// Route::controller(VoguepayController::class)->group(function () {
//     Route::get('/vogue-pay', 'showForm');
//     Route::get('/vogue-pay/success/{id}', 'paymentSuccess');
//     Route::get('/vogue-pay/failure/{id}', 'paymentFailure');
// });


// //Iyzico
// Route::any('/iyzico/payment/callback/{payment_type}/{amount?}/{payment_method?}/{combined_order_id?}/{customer_package_id?}/{seller_package_id?}', [IyzicoController::class, 'callback'])->name('iyzico.callback');

// Route::get('/customer-products/admin', [IyzicoController::class, 'initPayment'])->name('profile.edit');

// //payhere below
// Route::controller(PayhereController::class)->group(function () {
//     Route::get('/payhere/checkout/testing', 'checkout_testing')->name('payhere.checkout.testing');
//     Route::get('/payhere/wallet/testing', 'wallet_testing')->name('payhere.checkout.testing');
//     Route::get('/payhere/customer_package/testing', 'customer_package_testing')->name('payhere.customer_package.testing');

//     Route::any('/payhere/checkout/notify', 'checkout_notify')->name('payhere.checkout.notify');
//     Route::any('/payhere/checkout/return', 'checkout_return')->name('payhere.checkout.return');
//     Route::any('/payhere/checkout/cancel', 'chekout_cancel')->name('payhere.checkout.cancel');

//     Route::any('/payhere/wallet/notify', 'wallet_notify')->name('payhere.wallet.notify');
//     Route::any('/payhere/wallet/return', 'wallet_return')->name('payhere.wallet.return');
//     Route::any('/payhere/wallet/cancel', 'wallet_cancel')->name('payhere.wallet.cancel');

//     Route::any('/payhere/seller_package_payment/notify', 'seller_package_notify')->name('payhere.seller_package_payment.notify');
//     Route::any('/payhere/seller_package_payment/return', 'seller_package_payment_return')->name('payhere.seller_package_payment.return');
//     Route::any('/payhere/seller_package_payment/cancel', 'seller_package_payment_cancel')->name('payhere.seller_package_payment.cancel');

//     Route::any('/payhere/customer_package_payment/notify', 'customer_package_notify')->name('payhere.customer_package_payment.notify');
//     Route::any('/payhere/customer_package_payment/return', 'customer_package_return')->name('payhere.customer_package_payment.return');
//     Route::any('/payhere/customer_package_payment/cancel', 'customer_package_cancel')->name('payhere.customer_package_payment.cancel');
// });


// //N-genius
// Route::controller(NgeniusController::class)->group(function () {
//     Route::any('ngenius/cart_payment_callback', 'cart_payment_callback')->name('ngenius.cart_payment_callback');
//     Route::any('ngenius/wallet_payment_callback', 'wallet_payment_callback')->name('ngenius.wallet_payment_callback');
//     Route::any('ngenius/customer_package_payment_callback', 'customer_package_payment_callback')->name('ngenius.customer_package_payment_callback');
//     Route::any('ngenius/seller_package_payment_callback', 'seller_package_payment_callback')->name('ngenius.seller_package_payment_callback');
// });

// //bKash
// Route::controller(BkashController::class)->group(function () {
//     Route::post('/bkash/createpayment', 'checkout')->name('bkash.checkout');
//     Route::post('/bkash/executepayment', 'excecute')->name('bkash.excecute');
//     Route::get('/bkash/success', 'success')->name('bkash.success');
// });

// //Nagad
// Route::get('/nagad/callback', [NagadController::class, 'verify'])->name('nagad.callback');

// //aamarpay
// Route::controller(AamarpayController::class)->group(function () {
//     Route::post('/aamarpay/success','success')->name('aamarpay.success');
//     Route::post('/aamarpay/fail','fail')->name('aamarpay.fail');
// });

// //Authorize-Net-Payment
// Route::post('/dopay/online', [AuthorizenetController::class, 'handleonlinepay'])->name('dopay.online');

// //payku
// Route::get('/payku/callback/{id}', [PaykuController::class, 'callback'])->name('payku.result');

// //Blog Section
// Route::controller(BlogController::class)->group(function () {
//     Route::get('/blog', 'all_blog')->name('blog');
//     Route::get('/blog/{slug}', 'blog_details')->name('blog.details');
// });

// Route::controller(PageController::class)->group(function () {
//     //mobile app balnk page for webview
//     Route::get('/mobile-page/{slug}', 'mobile_custom_page')->name('mobile.custom-pages');

//     //Custom page
//     Route::get('/{slug}', 'show_custom_page')->name('custom-pages.show_custom_page');
// });
