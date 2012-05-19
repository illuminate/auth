<?php

use Mockery as m;

class GuardTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testAttemptCallsRetrieveByCredentials()
	{
		$guard = $this->getGuard();
		$guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(array('foo'));
		$guard->attempt(array('foo'));
	}


	public function testAttemptReturnsUserInterface()
	{
		$session = m::mock('Illuminate\Session\Store');
		$provider = m::mock('Illuminate\Auth\UserProviderInterface');
		$guard = $this->getMock('Illuminate\Auth\Guard', array('login'), array($provider, $session));
		$user = $this->getMock('Illuminate\Auth\UserInterface');
		$guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->andReturn($user);
		$guard->getProvider()->shouldReceive('validateCredentials')->with($user, array('foo'))->andReturn(true);
		$guard->expects($this->once())->method('login')->with($this->equalTo($user));
		$this->assertTrue($guard->attempt(array('foo')));
	}


	public function testAttemptReturnsFalseIfUserNotGiven()
	{
		$mock = $this->getGuard();
		$mock->getProvider()->shouldReceive('retrieveByCredentials')->once()->andReturn('foo');
		$this->assertFalse($mock->attempt(array('foo')));
	}


	public function testLoginStoresIdentifierInSession()
	{
		$provider = m::mock('Illuminate\Auth\UserProviderInterface');
		$session = m::mock('Illuminate\Session\Store');
		$mock = $this->getMock('Illuminate\Auth\Guard', array('getName'), array($provider, $session));
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock->expects($this->once())->method('getName')->will($this->returnValue('foo'));
		$user->shouldReceive('getIdentifier')->once()->andReturn('bar');
		$mock->getSession()->shouldReceive('put')->with('foo', 'bar')->once();
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
		$provider = m::mock('Illuminate\Auth\UserProviderInterface');
		$session = m::mock('Illuminate\Session\Store');
		$mock = $this->getMock('Illuminate\Auth\Guard', array('user'), array($provider, $session));
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
		$mock->getSession()->shouldReceive('get')->once()->andReturn(null);
		$this->assertNull($mock->user());
	}


	public function testUserIsSetToRetrievedUser()
	{
		$mock = $this->getGuard();
		$mock->getSession()->shouldReceive('get')->once()->andReturn(1);
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock->getProvider()->shouldReceive('retrieveByID')->once()->with(1)->andReturn($user);
		$this->assertEquals($user, $mock->user());
		$this->assertEquals($user, $mock->getUser());
	}


	public function testLogoutRemovesSessionToken()
	{
		$provider = m::mock('Illuminate\Auth\UserProviderInterface');
		$session = m::mock('Illuminate\Session\Store');
		$mock = $this->getMock('Illuminate\Auth\Guard', array('getName'), array($provider, $session));
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock->expects($this->once())->method('getName')->will($this->returnValue('foo'));
		$mock->getSession()->shouldReceive('forget')->once()->with('foo');
		$mock->setUser($user);
		$mock->logout();
		$this->assertNull($mock->getUser());
	}


	protected function getGuard()
	{
		$session = m::mock('Illuminate\Session\Store');
		$provider = m::mock('Illuminate\Auth\UserProviderInterface');
		return new Illuminate\Auth\Guard($provider, $session);
	}

}