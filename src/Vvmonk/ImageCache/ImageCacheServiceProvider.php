<?php

namespace Vvmonk\ImageCache;

use Illuminate\Support\ServiceProvider;

class ImageCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('imagecache.php')
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'imagecache'
        );
    }
}