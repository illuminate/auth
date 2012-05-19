<?php namespace Illuminate\Auth;

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
	 * The Symfony request instance.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * Create a new authentication guard.
	 *
	 * @param  Illuminate\Auth\UserProviderInterface     $provider
	 * @param  Illuminate\Session\Store                  $session
	 * @param  Symfony\Component\HttpFoundation\Request  $request
	 * @return void
	 */
	public function __construct(UserProviderInterface $provider,
                                SessionStore $session,
                                Request $request)
	{
		$this->request = $request;
		$this->session = $session;
		$this->provider = $provider;
	}

	/**
	 * Determine if the current user is authenticated.
	 *
	 * @return bool
	 */
	public function isAuthed()
	{
		return ! is_null($this->user());
	}

	/**
	 * Determine if the current user is a guest.
	 *
	 * @return bool
	 */
	public function isGuest()
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

		if ( ! is_null($id))
		{
			return $this->user = $this->provider->retrieveByID($id);
		}
	}

	/**
	 * Attempt to authenticate a user using the given credentials.
	 *
	 * @param  array  $credentials
	 * @return Illuminate\Auth\UserInterface|false
	 */
	public function attempt(array $credentials = array())
	{
		$user = $this->provider->retrieveByCredentials($credentials);

		// If an implementation of UserInterface was returned, we'll ask the provider
		// to validate the user against the given credentials, and if they are in
		// fact valid we'll log the user into the application and return true.
		if ($user instanceof UserInterface)
		{
			if ($this->provider->validateCredentials($user, $credentials))
			{
				$this->login($user);

				return true;
			}
		}

		return false;
	}

	/**
	 * Log a user into the application.
	 *
	 * @param  Illuminate\Auth\UserInterface  $user
	 * @return void
	 */
	public function login(UserInterface $user)
	{
		$this->session->put($this->getName(), $user->getIdentifier());

		$this->user = $user;
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return void
	 */
	public function logout()
	{
		$this->session->forget($this->getName());

		$this->user = null;
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
	protected function getName()
	{
		return 'login_'.md5(get_class($this));
	}

}