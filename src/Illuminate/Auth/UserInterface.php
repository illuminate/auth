<?php namespace Illuminate\Auth;

interface UserInterface {

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getIdentifier();

}