<?php

class BaseSpec extends DescribeBehavior
{
	protected $_generalObject;

	public function BeforeEach()
	{
		$this->_generalObject = new GeneralObject();
	}

	public function ItShouldGetAPropertyValue()
	{
		return $this->Expects($this->_generalObject->Foo)->ToBeEqualTo('foo');
	}

	public function ItShouldSetAPropertyValue()
	{
		$this->_generalObject->Foo = "bar";

		return $this->Expects($this->_generalObject->_foo)->ToBeEqualTo('bar');
	}

	public function ItShouldCheckThatAPropertyExistsForReading()
	{
		$testExists = $this->_generalObject->HasProperty('Foo');

		return $this->Expects($testExists)->ToBeTrue();
	}

	public function ItShouldCheckThatAPropertyExistsForWriting()
	{
		$testExists = $this->_generalObject->HasProperty('SetOnlyProperty');

		return $this->Expects($testExists)->ToBeTrue();
	}
}
