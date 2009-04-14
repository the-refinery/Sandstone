<?php

class FactorySpec extends SpecBase
{
	public function ItShouldInstantiateAClassWithoutParameters()
	{
		Check(Factory::Create('FooBar'))->ShouldBeInstanceOf('FooBar');
	}

	public function ItShouldInstantiateAClassWithParameters()
	{
		Check(Factory::Create('FooBar', 1, 2, 3))->ShouldBeInstanceOf('FooBar');
	}
}

class FooBar extends Module
{
	public function __construct($a = null, $b = null, $c = null)
	{
	}
}

?>
