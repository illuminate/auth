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
		list($session, $provider, $request, $cookie) = $this->getMocks();
		$guard = $this->getMock('Illuminate\Auth\Guard', array('login'), array($provider, $session, $cookie, $request));
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
		list($session, $provider, $request, $cookie) = $this->getMocks();
		$mock = $this->getMock('Illuminate\Auth\Guard', array('getName'), array($provider, $session, $cookie, $request));
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
		list($session, $provider, $request, $cookie) = $this->getMocks();
		$mock = $this->getMock('Illuminate\Auth\Guard', array('user'), array($provider, $session, $cookie, $request));
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
		list($session, $provider, $request, $cookie) = $this->getMocks();
		$mock = $this->getMock('Illuminate\Auth\Guard', array('getName'), array($provider, $session, $cookie, $request));
		$user = m::mock('Illuminate\Auth\UserInterface');
		$mock->expects($this->once())->method('getName')->will($this->returnValue('foo'));
		$mock->getSession()->shouldReceive('forget')->once()->with('foo');
		$mock->setUser($user);
		$mock->logout();
		$this->assertNull($mock->getUser());
	}


	public function testLoginMethodQueuesCookieWhenRemembering()
	{
		list($session, $provider, $request, $cookie) = $this->getMocks();
		$cookie = new Illuminate\CookieCreator;
		$guard = new Illuminate\Auth\Guard($provider, $session, $cookie, $request);
		$encrypter = new Illuminate\Encrypter('MySuperSecretKey');
		$guard->setEncrypter($encrypter);
		$guard->getSession()->shouldReceive('put')->once();
		$user = m::mock('Illuminate\Auth\UserInterface');
		$user->shouldReceive('getIdentifier')->once()->andReturn('foo bar');
		$guard->login($user, true);

		$cookies = $guard->getQueuedCookies();
		$this->assertEquals(1, count($cookies));
		$this->assertEquals('foo bar', $guard->getEncrypter()->decrypt($cookies[0]->getValue()));
		$this->assertEquals($cookies[0]->getName(), $guard->getRecallerName());
	}


	protected function getGuard()
	{
		list($session, $provider, $request, $cookie) = $this->getMocks();
		return new Illuminate\Auth\Guard($provider, $session, $cookie, $request);
	}


	protected function getMocks()
	{
		return array(
			m::mock('Illuminate\Session\Store'),
			m::mock('Illuminate\Auth\UserProviderInterface'),
			m::mock('Symfony\Component\HttpFoundation\Request'),
			m::mock('Illuminate\CookieCreator'),
		);
	}

}