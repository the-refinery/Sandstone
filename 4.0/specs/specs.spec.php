<?php

class SpecsSpec extends SpecBase
{
	public function ItShouldAssertTrue()
	{
		$foo = new SpecAssertion(true);
		Check($foo->True())->ShouldBeTrue();		
	}

	public function ItShouldAssertEqualForIntegers()
	{
		$foo = new SpecAssertion(5);
		Check($foo->EqualTo(5))->ShouldBeTrue();
	}
	
	public function ItShouldAssertEqualForStrings()
	{
		$foo = new SpecAssertion('foobar');
		Check($foo->EqualTo('foobar'))->ShouldBeTrue();
	}

	public function ItShouldNotAssertEqualForMixedTypes()
	{
		$foo = new SpecAssertion(1);
		Check($foo->EqualTo('1'))->ShouldNotBeTrue();
	}
	
}

?>