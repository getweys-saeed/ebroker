<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PropertController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\SeoSettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportReasonController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\OutdoorFacilityController;
use App\Http\Controllers\PropertysInquiryController;
use App\Models\Customer;
use App\Models\Property;

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



Route::get('customer-privacy-policy', [SettingController::class, 'show_privacy_policy'])->name('customer-privacy-policy');


Route::get('customer-terms-conditions', [SettingController::class, 'show_terms_conditions'])->name('customer-terms-conditions');


Auth::routes();

Route::get('privacypolicy', [HomeController::class, 'privacy_policy']);
Route::post('/webhook/razorpay', [WebhookController::class, 'razorpay']);
Route::post('/webhook/paystack', [WebhookController::class, 'paystack']);
Route::post('/webhook/paypal', [WebhookController::class, 'paypal']);
Route::post('/webhook/stripe', [WebhookController::class, 'stripe']);

Route::group(['prefix' => 'install'], static function () {
    Route::get('purchase-code', [InstallerController::class, 'purchaseCodeIndex'])->name('install.purchase-code.index');
    Route::post('purchase-code', [InstallerController::class, 'checkPurchaseCode'])->name('install.purchase-code.post');
});



Route::middleware(['language'])->group(function () {
    Route::get('/', function () {
        return view('auth.login');
    });
    Route::middleware(['auth', 'checklogin'])->group(function () {
        Route::get('render_svg', [HomeController::class, 'render_svg'])->name('render_svg');
        Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'blank_dashboard'])->name('dashboard');
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::get('about-us', [SettingController::class, 'index']);
        Route::get('privacy-policy', [SettingController::class, 'index']);
        Route::get('terms-conditions', [SettingController::class, 'index']);
        Route::get('system-settings', [SettingController::class, 'index']);
        Route::get('firebase_settings', [SettingController::class, 'index']);
        Route::get('app_settings', [SettingController::class, 'index']);
        Route::get('web_settings', [SettingController::class, 'index']);
        Route::get('system_version', [SettingController::class, 'index']);
        Route::post('firebase-settings', [SettingController::class, 'firebase_settings']);
        Route::post('app-settings', [SettingController::class, 'app_settings']);
        Route::get('system_version', [SettingController::class, 'system_version']);
        Route::post('web-settings', [SettingController::class, 'web_settings']);
        Route::get('notification-settings', [SettingController::class, 'notificationSettingIndex'])->name('notification-setting-index');
        Route::post('notification-settings', [SettingController::class, 'notificationSettingStore'])->name('notification-setting-store');

        Route::post('system_version_setting', [SettingController::class, 'system_version_setting']);

        /// START :: HOME ROUTE
        Route::get('change-password', [App\Http\Controllers\HomeController::class, 'change_password'])->name('changepassword');
        Route::post('check-password', [App\Http\Controllers\HomeController::class, 'check_password'])->name('checkpassword');
        Route::post('store-password', [App\Http\Controllers\HomeController::class, 'store_password'])->name('changepassword.store');
        Route::get('changeprofile', [HomeController::class, 'changeprofile'])->name('changeprofile');
        Route::post('updateprofile', [HomeController::class, 'update_profile'])->name('updateprofile');
        Route::post('firebase_messaging_settings', [HomeController::class, 'firebase_messaging_settings'])->name('firebase_messaging_settings');

        /// END :: HOME ROUTE

        /// START :: SETTINGS ROUTE

        Route::post('settings', [SettingController::class, 'settings']);
        Route::post('set_settings', [SettingController::class, 'system_settings']);
        /// END :: SETTINGS ROUTE

        /// START :: LANGUAGES ROUTE


        Route::resource('language', LanguageController::class);
        Route::get('language_list', [LanguageController::class, 'show']);
        Route::post('language_update', [LanguageController::class, 'update'])->name('language_update');
        Route::get('language-destory/{id}', [LanguageController::class, 'destroy'])->name('language.destroy');
        Route::get('set-language/{lang}', [LanguageController::class, 'set_language']);
        Route::get('download-panel-file', [LanguageController::class, 'downloadPanelFile'])->name('download-panel-file');
        Route::get('download-app-file', [LanguageController::class, 'downloadAppFile'])->name('download-app-file');
        Route::get('download-web-file', [LanguageController::class, 'downloadWebFile'])->name('download-web-file');

        /// END :: LANGUAGES ROUTE

        /// START :: PAYMENT ROUTE

        Route::get('getPaymentList', [PaymentController::class, 'get_payment_list']);
        Route::get('payment', [PaymentController::class, 'index']);
        /// END :: PAYMENT ROUTE

        /// START :: USER ROUTE

        Route::resource('users', UserController::class);
        Route::post('users-update', [UserController::class, 'update']);
        Route::post('users-reset-password', [UserController::class, 'resetpassword']);
        Route::get('userList', [UserController::class, 'userList']);
        Route::get('get_users_inquiries', [UserController::class, 'users_inquiries']);
        Route::get('users_inquiries', [UserController::class, function () {
            return view('users.users_inquiries');
        }]);
        Route::get('destroy_contact_request/{id}', [UserController::class, 'destroy_contact_request'])->name('destroy_contact_request');




        /// END :: PAYMENT ROUTE

        /// START :: PAYMENT ROUTE

        Route::resource('customer', CustomersController::class);

        Route::post('/bulk-delete', [CustomersController::class, 'bulkDelete'])->name('customer.bulkDelete');

        Route::get('customerList', [CustomersController::class, 'customerList']);


        Route::get('verifiedUser', [CustomersController::class, 'verifiedUser']);
        Route::get('customerListVerified', [CustomersController::class, 'customerListVerified']);

        Route::get('unverifiedUser', [CustomersController::class, 'unverifiedUser']);
        Route::get('customerListUnverified', [CustomersController::class, 'customerListUnverified']);

        Route::post('customerstatus', [CustomersController::class, 'update'])->name('customer.customerstatus');

        // In routes/web.php

        // Route For Notification
        // In routes/web.php


        Route::post('/clear-notifications', function () {
            // Update all unseen notifications to seen
            \App\Models\Customer::where('notification_seen', 0)->whereNotNull('user_document')->update(['notification_seen' => 1]);

            // Return a success response
            return response()->json(['status' => 'success']);
        })->name('clear.notifications');
        // routes/web.php

        Route::get('/notifications/count', function () {
            $unseenCount = \App\Models\Customer::where('notification_seen', 0)->whereNotNull('user_document')->count();
            return response()->json(['count' => $unseenCount]);
        })->name('notifications.count');

        // Route to fetch notification count
        Route::get('/property/notifications/count', function () {
            $unseenCountProperty = Property::where('notification_seen', 0)->count();
            return response()->json(['count' => $unseenCountProperty]);
        })->name('property.notifications.count');

        // Clear notifications
        Route::post('/property/clear-notifications', function () {
            Property::where('notification_seen', 0)->update(['notification_seen' => 1]);
            return response()->json(['status' => 'success']);
        })->name('clear.property.notifications');

        /// END :: CUSTOMER ROUTE

        /// START :: SLIDER ROUTE

        Route::resource('slider', SliderController::class);
        Route::post('slider-order', [SliderController::class, 'update'])->name('slider.slider-order');
        Route::get('slider-destroy/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');
        Route::get('get-property-by-category', [SliderController::class, 'getPropertyByCategory'])->name('slider.getpropertybycategory');
        Route::get('sliderList', [SliderController::class, 'sliderList']);


        /// END :: SLIDER ROUTE

        /// START :: ARTICLE ROUTE

        Route::resource('article', ArticleController::class);
        Route::get('article_list', [ArticleController::class, 'show'])->name('article_list');
        Route::get('add_article', [ArticleController::class, 'create'])->name('add_article');
        Route::get('article-destroy/{id}', [ArticleController::class, 'destroy'])->name('article.destroy');
        /// END :: ARTICLE ROUTE

        /// START :: ADVERTISEMENT ROUTE

        Route::resource('featured_properties', AdvertisementController::class);
        Route::get('featured_properties_list', [AdvertisementController::class, 'show']);
        Route::post('featured_properties_status', [AdvertisementController::class, 'updateStatus'])->name('featured_properties.update-advertisement-status');
        Route::post('adv-status-update', [AdvertisementController::class, 'update'])->name('adv-status-update');
        /// END :: ADVERTISEMENT ROUTE

        /// START :: PACKAGE ROUTE
        Route::resource('package', PackageController::class);
        Route::get('package_list', [PackageController::class, 'show']);
        Route::post('package-update', [PackageController::class, 'update']);
        Route::post('package-status', [PackageController::class, 'updatestatus'])->name('package.updatestatus');
        Route::get('get_user_purchased_packages', [PackageController::class, function () {
            return view('packages.users_packages');
        }]);

        Route::get('get_user_package_list', [PackageController::class, 'get_user_package_list']);

        Route::get('package_delete/{id}', [PackageController::class, 'destroy'])->name("package.delete");

        /// END :: PACKAGE ROUTE


        /// START :: CATEGORYW ROUTE
        Route::resource('categories', CategoryController::class);
        Route::get('categoriesList', [CategoryController::class, 'categoryList']);
        Route::post('categories-update', [CategoryController::class, 'update']);
        Route::post('categorystatus', [CategoryController::class, 'updateCategory'])->name('categorystatus');

        Route::get('category_delete/{id}', [CategoryController::class, 'destroy'])->name("category.delete");
        /// END :: CATEGORYW ROUTE


        /// START :: PARAMETER FACILITY ROUTE

        Route::resource('parameters', ParameterController::class);
        Route::get('parameter-list', [ParameterController::class, 'show']);
        Route::post('parameter-update', [ParameterController::class, 'update']);

        Route::get('parameter_delete/{id}', [ParameterController::class, 'destroy'])->name("parameter.delete");

        /// END :: PARAMETER FACILITY ROUTE

        /// START :: OUTDOOR FACILITY ROUTE
        Route::resource('outdoor_facilities', OutdoorFacilityController::class);
        Route::get('facility-list', [OutdoorFacilityController::class, 'show']);
        Route::post('facility-update', [OutdoorFacilityController::class, 'update']);
        Route::get('facility-delete/{id}', [OutdoorFacilityController::class, 'destroy'])->name('outdoor_facilities.destroy');
        /// END :: OUTDOOR FACILITY ROUTE



        /// START :: DOCUMENTATION VERIFICATION ROUTE
        Route::resource('document-Verification', DocumentVerificationController::class);

        // Custom routes for additional methods
        Route::get('document-verification', [DocumentVerificationController::class, 'customerdocument']);

        //verified
        Route::get('activeDocument', [DocumentVerificationController::class, 'activeDocument']);
        Route::get('verified-document', [DocumentVerificationController::class, 'verifiedDocument']);
        //unverified
        Route::get('unactiveDocument', [DocumentVerificationController::class, 'unactiveDocument']);
        Route::get('unverified-document', [DocumentVerificationController::class, 'unverifiedDocument']);


        Route::post('document-verification-update', [DocumentVerificationController::class, 'update'])->name('document.document_status');

        /// END :: DOCUMENTATION VERIFICATION ROUTE



        /// START :: PROPERTY ROUTE
        Route::get('property/export/', [PropertController::class, 'export']);
        Route::resource('property', PropertController::class);
        Route::get('getPropertyList', [PropertController::class, 'getPropertyList']);

        Route::get('activeProperty', [PropertController::class, 'activeProperty'])->name("activeProperty");
        Route::get('getPropertyListActive', [PropertController::class, 'getPropertyListActive'])->name("getPropertyListActive");

        Route::get('inactiveProperty', [PropertController::class, 'inactiveProperty'])->name("inactiveProperty");
        Route::get('getPropertyListInactive', [PropertController::class, 'getPropertyListInactive'])->name("getPropertyListInactive");

        Route::post('updatepropertystatus', [PropertController::class, 'updateStatus'])->name('updatepropertystatus');
        Route::post('property-gallery', [PropertController::class, 'removeGalleryImage'])->name('property.removeGalleryImage');
        Route::get('get-state-by-country', [PropertController::class, 'getStatesByCountry'])->name('property.getStatesByCountry');
        Route::get('property-destory/{id}', [PropertController::class, 'destroy'])->name('property.destroy');
        Route::get('getFeaturedPropertyList', [PropertController::class, 'getFeaturedPropertyList']);
        Route::post('updateaccessability', [PropertController::class, 'updateaccessability'])->name('updateaccessability');

        Route::get('updateFCMID', [UserController::class, 'updateFCMID']);
        /// END :: PROPERTY ROUTE


        /// START :: PROPERTY INQUIRY
        Route::resource('property-inquiry', PropertysInquiryController::class);
        Route::get('getPropertyInquiryList', [PropertysInquiryController::class, 'getPropertyInquiryList']);
        Route::post('property-inquiry-status', [PropertysInquiryController::class, 'updateStatus'])->name('property-inquiry.updateStatus');
        /// ENND :: PROPERTY INQUIRY

        /// START :: REPORTREASON
        Route::resource('report-reasons', ReportReasonController::class);
        Route::get('report-reasons-list', [ReportReasonController::class, 'show']);
        Route::post('report-reasons-update', [ReportReasonController::class, 'update']);
        Route::get('report-reasons-destroy/{id}', [ReportReasonController::class, 'destroy'])->name('reasons.destroy');
        Route::get('users_reports', [ReportReasonController::class, 'users_reports']);
        Route::get('user_reports_list', [ReportReasonController::class, 'user_reports_list']);
        /// END :: REPORTREASON

        Route::resource('property-inquiry', PropertysInquiryController::class);


        /// START :: CHAT ROUTE

        Route::get('get-chat-list', [ChatController::class, 'getChats'])->name('get-chat-list');
        Route::post('store_chat', [ChatController::class, 'store']);
        Route::get('getAllMessage', [ChatController::class, 'getAllMessage']);
        /// END :: CHAT ROUTE


        /// START :: NOTIFICATION
        Route::resource('notification', NotificationController::class);
        Route::get('notificationList', [NotificationController::class, 'notificationList']);
        Route::get('notification-delete', [NotificationController::class, 'destroy']);
        Route::post('notification-multiple-delete', [NotificationController::class, 'multiple_delete']);
        /// END :: NOTIFICATION

        Route::resource('project', ProjectController::class);
        Route::post('updateProjectStatus', [ProjectController::class, 'updateStatus'])->name('updateProjectStatus');

        Route::resource('seo_settings', SeoSettingsController::class);
        Route::get('seo-settings-destroy/{id}', [SeoSettingsController::class, 'destroy'])->name('seo_settings.destroy');


        Route::get('chat', function () {
            return view('chat');
        });

        Route::get('calculator', function () {
            return view('Calculator.calculator');
        });
    });
});

// Local Language Values for JS
Route::get('/js/lang', static function () {
    //    https://medium.com/@serhii.matrunchyk/using-laravel-localization-with-javascript-and-vuejs-23064d0c210e
    header('Content-Type: text/javascript');
    $labels = \Illuminate\Support\Facades\Cache::remember('lang.js', 3600, static function () {
        $lang = Session::get('locale') ?? 'en';
        $files = resource_path('lang/' . $lang . '.json');
        return File::get($files);
    });
    echo ('window.trans = ' . $labels);
    exit();
})->name('assets.lang');


// // Add New Migration Route
// Route::get('migrate', function () {
//     Artisan::call('migrate');
//     return redirect()->back();
// });

// // Rollback last step Migration Route
// Route::get('/rollback', function () {
//     Artisan::call('migrate:rollback');
//     return redirect()->back();
// });

// Clear Config
Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('view:cache');
    return redirect()->back();
});

Route::get('/add-url', function () {
    $envUpdates = [
        'APP_URL' => Request::root(),
    ];
    updateEnv($envUpdates);
})->name('add-url-in-env');
