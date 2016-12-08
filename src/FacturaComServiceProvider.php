<?php

namespace Bionicmaster\FacturaCom;

use Illuminate\Support\ServiceProvider;

class FacturaComServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/facturacom.php' => config_path('facturacom.php')
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['facturas'] = $this->app->share(function ($app) {
            return new Facturas();
        });
    }
}