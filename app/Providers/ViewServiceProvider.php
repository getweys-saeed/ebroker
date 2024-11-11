<?php

namespace App\Providers;

use App\Services\CachingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        $cache = app(CachingService::class);

        /*** Main Blade File ***/
        View::composer('layouts.main', static function (\Illuminate\View\View $view) use ($cache) {
            $lang = Session::get('language');
            if ($lang) {
                $view->with('language', $lang);
            } else {
                $defaultLanguage = $cache->getDefaultLanguage();

                // Check if $defaultLanguage is not null
                if ($defaultLanguage) {
                    Session::put('language', $defaultLanguage);
                    Session::put('locale', $defaultLanguage->code);
                    Session::save();
                    app()->setLocale($defaultLanguage->code);
                    Artisan::call('cache:clear');
                    $view->with('language', $defaultLanguage);
                } else {
                    // Provide a fallback object with expected properties
                    $fallbackLanguage = (object)[
                        'code' => 'en',
                        'rtl' => false, // Assuming false; adjust based on your needs
                    ];
                    $view->with('language', $fallbackLanguage);
                }
            }
        });

        /*** Auth Login Blade File ***/
        View::composer('auth.login', static function (\Illuminate\View\View $view) use ($cache) {
            $defaultLanguage = $cache->getDefaultLanguage();

            // Check if $defaultLanguage is not null
            if ($defaultLanguage) {
                Session::put('language', $defaultLanguage);
                Session::put('locale', $defaultLanguage->code);
                Session::save();
                app()->setLocale($defaultLanguage->code);
                Artisan::call('cache:clear');
                $view->with('language', $defaultLanguage);
            } else {
                // Provide a fallback object with expected properties
                $fallbackLanguage = (object)[
                    'code' => 'en',
                    'rtl' => false,
                ];
                $view->with('language', $fallbackLanguage);
            }
        });
    }
}
