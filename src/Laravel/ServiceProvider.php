<?php namespace Stolz\Assets\Laravel;

use Stolz\Assets\Manager as Assets;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
	/**
	 * Path to the default config file.
	 *
	 * @var string
	 */
	protected $configFile;

	/**
	 * Create a new service provider instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function __construct($app)
	{
		parent::__construct($app);

		$this->configFile = __DIR__ . '/config.php';
	}

	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register()
	{
		// Merge user's configuration
		$this->mergeConfigFrom($this->configFile, 'assets');

		// Bind 'stolz.assets' shared component to the IoC container
		$this->app->singleton('stolz.assets', function ($app) {
			return new Assets($app['config']['assets']);
		});

		// Bind 'stolz.assets.command.flush' component to the IoC container
		$this->app->bind('stolz.assets.command.flush', function ($app) {
			return new FlushPipelineCommand();
		});
	}

	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Register paths to be published by 'vendor:publish' artisan command
		$this->publishes([
			$this->configFile => config_path('assets.php'),
		]);

		// Add 'Assets' facade alias
		AliasLoader::getInstance()->alias('Assets', 'Stolz\Assets\Laravel\Facade');

		// Add artisan command
		$this->commands('stolz.assets.command.flush');
	}
}
