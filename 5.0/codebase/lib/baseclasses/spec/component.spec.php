<?php

include_once("dependencies.php");

class ComponentSpec extends DescribeBehavior
{
	public function ItShouldGetAPropertyValue()
	{
		$generalObject = new GeneralObject();
		
		return $this->Expects($generalObject->Foo)->ToBeEqualTo('foo');
	}

	public function ItShouldSetAPropertyValue()
	{
		$generalObject = new GeneralObject();

		$generalObject->Foo = "bar";

		return $this->Expects($generalObject->_foo)->ToBeEqualTo('bar');
	}
}
