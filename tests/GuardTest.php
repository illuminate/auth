<?php

use Mockery as m;

class GuardTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testAttemptCallsRetrieveByCredentials()
	{
		$mock = $this->getGuard();
		$mock->expects($this->once())->method('retrieveUserByCredentials')->with($this->equalTo(array('foo')));
		$mock->attempt(array('foo'));
	}


	public function testAttemptReturnsUserInterface()
	{
		$mock = $this->getGuard();
		$user = $this->getMock('Illuminate\Auth\UserInterface');
		$mock->expects($this->once())->method('retrieveUserByCredentials')->will($this->returnValue($user));
		$this->assertEquals($user, $mock->attempt(array('foo')));
	}


	public function testAttemptReturnsFalseIfUserNotGiven()
	{
		$mock = $this->getGuard();
		$mock->expects($this->once())->method('retrieveUserByCredentials')->will($this->returnValue('foo'));
		$this->assertFalse($mock->attempt(array('foo')));
	}


	public function testLoginStoresIdentifierInSession()
	{
		$mock = $this->getGuard('getName');
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock->expects($this->once())->method('getName')->will($this->returnValue('foo'));
		$user->shouldReceive('getIdentifier')->once()->andReturn('bar');
		$session = m::mock('Illuminate\Session\Store');
		$session->shouldReceive('put')->with('foo', 'bar')->once();
		$mock->setSession($session);
		$mock->login($user);
	}


	protected function getGuard($stub = array())
	{
		$stub = array_merge(array('retrieveUserByCredentials', 'retrieveUserByID'), (array) $stub);

		return $this->getMock('Illuminate\Auth\Guard', $stub);
	}

}