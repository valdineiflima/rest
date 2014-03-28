<?php

namespace Rdehnhardt\Rest;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class RestServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->package('rdehnhardt/rest');

        $this->app->booting(function() {
  			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
  			$loader->alias('Rest', 'Rdehnhardt\Rest\Facades\Rest');
		});
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
    	$this->app->bind('Rest', function($app) {
    		return new Rest(\Config::get('rest::config'));
    	});
    	
        $this->app['rest'] = $this->app->share(function($app) {
            return new \Rdehnhardt\Rest\Rest;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return array();
    }

}
