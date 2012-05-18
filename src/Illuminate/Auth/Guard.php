<?php namespace Illuminate\Auth;

abstract class Guard {

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
	}

}