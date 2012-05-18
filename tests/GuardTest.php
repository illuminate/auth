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


	public function testIsAuthedReturnsTrueWhenUserIsNotNull()
	{
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock = $this->getGuard();
		$mock->setUser($user);
		$this->assertTrue($mock->isAuthed());
		$this->assertFalse($mock->isGuest());
	}


	public function testIsAuthedReturnsFalseWhenUserIsNull()
	{
		$mock = $this->getGuard('user');
		$mock->expects($this->exactly(2))->method('user')->will($this->returnValue(null));
		$this->assertFalse($mock->isAuthed());
		$this->assertTrue($mock->isGuest());
	}


	public function testUserMethodReturnsCachedUser()
	{
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock = $this->getGuard();
		$mock->setUser($user);
		$this->assertEquals($user, $mock->user());
	}


	public function testNullIsReturnedForUserIfNoUserFound()
	{
		$mock = $this->getGuard();
		$session = m::mock('Illuminate\Session\Store');
		$session->shouldReceive('get')->once()->andReturn(null);
		$mock->setSession($session);
		$this->assertNull($mock->user());
	}


	public function testUserIsSetToRetrievedUser()
	{
		$mock = $this->getGuard();
		$session = m::mock('Illuminate\Session\Store');
		$session->shouldReceive('get')->once()->andReturn(1);
		$mock->setSession($session);
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock->expects($this->once())->method('retrieveUserByID')->with($this->equalTo(1))->will($this->returnValue($user));
		$this->assertEquals($user, $mock->user());
		$this->assertEquals($user, $mock->getUser());
	}


	public function testLogoutRemovesSessionToken()
	{
		$mock = $this->getGuard('getName');
		$mock->expects($this->once())->method('getName')->will($this->returnValue('foo'));
		$session = m::mock('Illuminate\Session\Store');
		$session->shouldReceive('forget')->once()->with('foo');
		$mock->setSession($session);
		$mock->logout();
	}


	protected function getGuard($stub = array())
	{
		$stub = array_merge(array('retrieveUserByCredentials', 'retrieveUserByID'), (array) $stub);

		return $this->getMock('Illuminate\Auth\Guard', $stub);
	}

}