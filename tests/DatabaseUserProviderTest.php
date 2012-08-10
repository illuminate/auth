<?php

use Mockery as m;

class DatabaseUserProviderTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testRetrieveByIDReturnsUserWhenUserIsFound()
	{
		$conn = m::mock('Illuminate\Database\Connection');
		$conn->shouldReceive('table')->once()->with('foo')->andReturn($conn);
		$conn->shouldReceive('find')->once()->with(1)->andReturn(array('id' => 1, 'name' => 'Dayle'));
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$provider = new Illuminate\Auth\DatabaseUserProvider($conn, $hasher, 'foo');
		$user = $provider->retrieveByID(1);

		$this->assertInstanceOf('Illuminate\Auth\GenericUser', $user);
		$this->assertEquals(1, $user->getIdentifier());
		$this->assertEquals('Dayle', $user->name);
	}


	public function testRetrieveByIDReturnsNullWhenUserIsNotFound()
	{
		$conn = m::mock('Illuminate\Database\Connection');
		$conn->shouldReceive('table')->once()->with('foo')->andReturn($conn);
		$conn->shouldReceive('find')->once()->with(1)->andReturn(null);
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$provider = new Illuminate\Auth\DatabaseUserProvider($conn, $hasher, 'foo');
		$user = $provider->retrieveByID(1);

		$this->assertNull($user);
	}

}