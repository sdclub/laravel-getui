<?php 
namespace Sdclub\IGeTui;

use Illuminate\Support\ServiceProvider;
/**
 * AWS SDK for PHP service provider for Laravel applications
 */
class IGeTuiServiceProvider extends ServiceProvider
{
    const VERSION = '4.0.1.5';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the configuration
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__).'/config/igetui.php' => config_path('igetui.php')
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('igetui', function ($app) {
            return new IGeTuiPush($app['config']);//config
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['igetui'];
    }

}
