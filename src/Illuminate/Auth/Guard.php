<?php namespace Illuminate\Auth;

use Illuminate\Session\Store as SessionStore;

abstract class Guard {

	/**
	 * Retrieve a user by their unique idenetifier.
	 *
	 * @param  mixed  $identifier
	 * @return Illuminate\Auth\UserInterface|null
	 */
	abstract protected function retrieveUserByID($identifier);

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return Illuminate\Auth\UserInterface|null
	 */
	abstract protected function retrieveUserByCredentials(array $credentials);

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

		$id = $this->getSession()->get($this->getName());

		if ( ! is_null($id))
		{
			return $this->user = $this->retrieveUserByID($id);
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
		$user = $this->retrieveUserByCredentials($credentials);

		if ($user instanceof UserInterface)
		{
			return $user;
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
		$this->getSession()->put($this->getName(), $user->getIdentifier());

		$this->user = $user;
	}

	/**
	 * Get the session store used by the guard.
	 *
	 * @return Illuminate\Session\Store
	 */
	public function getSession()
	{
		if ( ! isset($this->session))
		{
			throw new \RuntimeException("No session instance set on guard.");
		}

		return $this->session;
	}

	/**
	 * Set the session store to be used by the guard.
	 *
	 * @param  Illuminate\Session\Store
	 * @return void
	 */
	public function setSession(SessionStore $session)
	{
		$this->session = $session;
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