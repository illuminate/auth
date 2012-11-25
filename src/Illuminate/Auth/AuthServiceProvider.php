<?php namespace Illuminate\Auth;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerAuthEvents();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerAuthProvider();

		$this->registerAuthFilter();
	}

	/**
	 * Register the authentication provider.
	 *
	 * @return void
	 */
	protected function registerAuthProvider()
	{
		$this->app['auth'] = $this->app->share(function($app)
		{
			// Once the authentication service has actually been requested by the developer
			// we will set a variable in the application indicating such. This helps us
			// know that we need to set any queued cookies in the after event later.
			$app['auth.loaded'] = true;

			return new AuthManager($app);
		});
	}

	/**
	 * Register the events needed for authentication.
	 *
	 * @return void
	 */
	protected function registerAuthEvents()
	{
		$app = $this->app;

		$app->after(function($request, $response) use ($app)
		{
			// If the authentication service has been used, we'll check for any cookies
			// that may be queued by the service. These cookies are all queued until
			// they are attached onto Response objects at the end of the requests.
			if (isset($app['auth.loaded']))
			{
				foreach ($app['auth']->getDrivers() as $driver)
				{
					foreach ($driver->getQueuedCookies() as $cookie)
					{
						$response->headers->setCookie($cookie);
					}
				}
			}
		});
	}

	/**
	 * Register the filter for the auth library.
	 *
	 * @return void
	 */
	protected function registerAuthFilter()
	{
		$app = $this->app;

		// The "auth" middleware provides a convenient way to verify that a given
		// user is logged into the application. If they are not, we will just
		// redirect the users to the "login" named route as a convenience.
		$app->addFilter('auth', function() use ($app)
		{
			if ($app['auth']->isGuest())
			{
				return $app['redirect']->route('login');
			}
		});
	}

}