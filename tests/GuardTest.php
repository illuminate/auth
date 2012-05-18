<?php

class GuardTest extends PHPUnit_Framework_TestCase {

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


	protected function getGuard()
	{
		return $this->getMock('Illuminate\Auth\Guard', array('retrieveUserByCredentials'));
	}

}