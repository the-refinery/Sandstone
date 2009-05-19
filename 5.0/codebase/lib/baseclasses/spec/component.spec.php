<?php

include_once("dependencies.php");

class ComponentSpec extends DescribeBehavior
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
}
