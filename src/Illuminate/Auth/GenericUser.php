<?php namespace Illuminate\Auth;

class GenericUser implements UserInterface {

	/**
	 * All of the user's attributes.
	 *
	 * @var array
	 */
	protected $attributes;

	/**
	 * Create a new generic User object.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct(array $attributes)
	{
		$this->attributes = $attributes;
	}

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getIdentifier()
	{
		return $this->attributes['id'];
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->attributes['password'];
	}

	/**
	 * Dynamically access the user's attributes.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->attributes[$key];
	}

}