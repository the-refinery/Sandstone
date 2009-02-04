<?php

class SpecsSpec extends SpecBase
{
	public function ItShouldAssertTrue()
	{
		$foo = new SpecAssertion(true);
		Check($foo->BeTrue())->ShouldBeTrue();		
	}
	
	public function ItShouldDoElementaryAddition()
	{
		Check(1+1)->ShouldBeEqualTo(2);
	}

	public function ItShouldAssertEqualForIntegers()
	{
		$foo = new SpecAssertion(5);
		Check($foo->BeEqualTo(5))->ShouldBeTrue();
	}
	
	public function ItShouldAssertEqualForStrings()
	{
		$foo = new SpecAssertion('foobar');
		Check($foo->BeEqualTo('foobar'))->ShouldBeTrue();
	}

	public function ItShouldNotAssertEqualForMixedTypes()
	{
		$foo = new SpecAssertion(1);
		Check($foo->BeEqualTo('1'))->ShouldNotBeTrue();
	}
	
	// Asserts Needed
	
	// * Array Has Key
	// * Array Contains
	// * Greater Than
	// * Greater Than Or Equal
	// * Less Than
	// * Less Than Or Equal
	// * Is Null
	// * Regex Match
	// * Instance Of
	
}

?>