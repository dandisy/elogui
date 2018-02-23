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

        $this->loadViewsFrom(__DIR__.'/views', 'elogui');

        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/webcore/elogui'),
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
