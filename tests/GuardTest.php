<?php

class GuardTest extends PHPUnit_Framework_TestCase {

	public function testAttemptCallsRetrieveByCredentials()
	{
		$mock = $this->getMock('Illuminate\Auth\Guard', array('retrieveUserByCredentials'));
		$mock->expects($this->once())->method('retrieveUserByCredentials')->with($this->equalTo(array('foo')));
		$mock->attempt(array('foo'));
	}

}