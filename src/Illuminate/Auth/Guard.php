<?php namespace Illuminate\Auth;

use Illuminate\Session\Store as SessionStore;

abstract class Guard {

	/**
	 * Retrieve a user by their unique idenetifier.
	 *
	 * @param  mixed  $identifier
	 * @return Illuminate\Auth\UserInterface|null
	 */
	abstract protected function retrieveUserByIdentifier($identifier);

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return Illuminate\Auth\UserInterface|null
	 */
	abstract protected function retrieveUserByCredentials(array $credentials);

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
	 * @param  Illuminate\Session\Store       $session
	 * @param  Illuminate\Auth\UserInterface  $user
	 * @return void
	 */
	public function login(SessionStore $session, UserInterface $user)
	{
		$session->put($this->getName(), $user->getIdentifier());

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