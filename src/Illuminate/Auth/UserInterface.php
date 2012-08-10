<?php namespace Illuminate\Auth;

interface UserInterface {

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getIdentifier();

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getPassword();

}