<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Property;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        LogViewer::auth(function () {
            return auth()->check(); // Allow access only if the user is authenticated
        });

        $totalActivePropertyAmount = Property::where('status', 1)->count();
        $totalNonActivePropertyAmount = Property::where('status', 0)->count();

        $totalActiveDoc = Customer::where('doc_verification_status', 1)->count();
        $totalNonActiveDoc = Customer::where('doc_verification_status', 0)->count();

        // $otpVerified = Customer::where('doc_verification_status', 1)->count();
        // $otpUnverified = Customer::where('doc_verification_status', 0)->count();



           $unseenCount = Customer::where('notification_seen', 0)->whereNotNull('user_document')->count();
           $unseenCountProperty = Property::where('notification_seen', 0)->count();



        // Share this data with all views
        view()->share(compact("unseenCount",'unseenCountProperty','totalActivePropertyAmount', 'totalNonActivePropertyAmount','totalActiveDoc',"totalNonActiveDoc"));
    }

}
