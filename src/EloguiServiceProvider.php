<?php

namespace Webcore\Elogui;

use Illuminate\Support\ServiceProvider;

class EloguiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        require __DIR__.'/routes.php';

        //$this->loadViewsFrom(__DIR__.'/views', 'elogui');

        $this->publishes([
            __DIR__.'/views' => storage_path('app/public/elogui'),
        ], 'views');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
