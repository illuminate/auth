<?php namespace Illuminate\Auth;

use Illuminate\Encrypter;
use Illuminate\CookieJar;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Session\Store as SessionStore;

class Guard {

	/**
	 * The currently authenticated user.
	 *
	 * @var UserInterface
	 */
	protected $user;

	/**
	 * The user provider implementation.
	 *
	 * @var Illuminate\Auth\UserProviderInterface
	 */
	protected $provider;

	/**
	 * The session store used by the guard.
	 *
	 * @var Illuminate\Session\Store
	 */
	protected $session;

	/**
	 * The Illuminate cookie creator service.
	 *
	 * @var Illuminate\CookieJar
	 */
	protected $cookie;

	/**
	 * The cookies queued by the guards.
	 *
	 * @var array
	 */
	protected $queuedCookies = array();

	/**
	 * Create a new authentication guard.
	 *
	 * @param  Illuminate\Auth\UserProviderInterface  $provider
	 * @param  Illuminate\Session\Store  $session
	 * @return void
	 */
	public function __construct(UserProviderInterface $provider,
                                SessionStore $session)
	{
		$this->session = $session;
		$this->provider = $provider;
	}

	/**
	 * Determine if the current user is authenticated.
	 *
	 * @return bool
	 */
	public function check()
	{
		return ! is_null($this->user());
	}

	/**
	 * Determine if the current user is a guest.
	 *
	 * @return bool
	 */
	public function guest()
	{
		return is_null($this->user());
	}

	/**
	 * Get the currently authenticated user.
	 *
	 * @return Illuminate\Auth\UserInterface|null
	 */
	public function user()
	{
		if ( ! is_null($this->user)) return $this->user;

		$id = $this->session->get($this->getName());

		$user = null;

		// First we will try to load the user using the identifier in the session if
		// one exists. Otherwise we will check for a "remember me" cookie in this
		// request, and if one exists, attempt to retrieve the user using that.
		if ( ! is_null($id))
		{
			$user = $this->provider->retrieveByID($id);
		}

		if (is_null($user) and ! is_null($recaller = $this->getRecaller()))
		{
			$user = $this->provider->retrieveByID($recaller);
		}

		return $this->user = $user;
	}

	/**
	 * Get the decrypted recaller cookie for the request.
	 *
	 * @return string|null
	 */
	protected function getRecaller()
	{
		return $this->getCookieJar()->get($this->getRecallerName());
	}

	/**
	 * Attempt to authenticate a user using the given credentials.
	 *
	 * @param  array  $credentials
	 * @param  bool   $remember
	 * @return bool
	 */
	public function attempt(array $credentials = array(), $remember = false)
	{
		$user = $this->provider->retrieveByCredentials($credentials);

		// If an implementation of UserInterface was returned, we'll ask the provider
		// to validate the user against the given credentials, and if they are in
		// fact valid we'll log the users into the application and return true.
		if ($user instanceof UserInterface)
		{
			if ($this->provider->validateCredentials($user, $credentials))
			{
				$this->login($user, $remember);

				return true;
			}
		}

		return false;
	}

	/**
	 * Log a user into the application.
	 *
	 * @param  Illuminate\Auth\UserInterface  $user
	 * @param  bool  $remember
	 * @return void
	 */
	public function login(UserInterface $user, $remember = false)
	{
		$id = $user->getAuthIdentifier();

		$this->session->put($this->getName(), $id);

		// If the user should be permanently "remembered" by the application we will
		// queue a permament cookie that contains the encrypted copy of the user
		// identifier. We will then decrypt this later to retrieve the users.
		if ($remember)
		{
			$this->queuedCookies[] = $this->createRecaller($id);
		}

		$this->user = $user;
	}

	/**
	 * Create a remember me cookie for a given ID.
	 *
	 * @param  mixed  $id
	 * @return Symfony\Component\HttpFoundation\Cookie
	 */
	protected function createRecaller($id)
	{
		return $this->getCookieJar()->forever($this->getRecallerName(), $id);
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return void
	 */
	public function logout()
	{
		$this->session->forget($this->getName());

		$recaller = $this->getRecallerName();

		$this->queuedCookies[] = $this->getCookieJar()->forget($recaller);

		$this->user = null;
	}

	/**
	 * Get the cookie creator instance used by the guard.
	 *
	 * @return Illuminate\CookieJar
	 */
	public function getCookieJar()
	{
		if ( ! isset($this->cookie))
		{
			throw new \RuntimeException("Cookie jar has not been set.");
		}

		return $this->cookie;
	}

	/**
	 * Set the cookie creator instance used by the guard.
	 *
	 * @param  Illuminate\CookieJar  $cookie
	 * @return void
	 */
	public function setCookieJar(CookieJar $cookie)
	{
		$this->cookie = $cookie;
	}

	/**
	 * Get the session store used by the guard.
	 *
	 * @return Illuminate\Session\Store
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Get the cookies queued by the guard.
	 *
	 * @return array
	 */
	public function getQueuedCookies()
	{
		return $this->queuedCookies;
	}

	/**
	 * Get the user provider used by the guard.
	 *
	 * @return Illuminate\Auth\UserProviderInterface
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * Return the currently cached user of the application.
	 *
	 * @return Illuminate\Auth\UserInterface|null
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the current user of the application.
	 *
	 * @param  Illuminate\Auth\UserInterface  $user
	 * @return void
	 */
	public function setUser(UserInterface $user)
	{
		$this->user = $user;
	}

	/**
	 * Get a unique identifier for the auth session value.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'login_'.md5(get_class($this));
	}

	/**
	 * Get the name of the cookie used to store the "recaller".
	 *
	 * @return string
	 */
	public function getRecallerName()
	{
		return 'remember_'.md5(get_class($this));
	}

}