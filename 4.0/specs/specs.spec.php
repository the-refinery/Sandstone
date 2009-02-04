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
	
	public function ItShouldAssertAsNull()
	{
		$foo = new SpecAssertion(null);
		Check($foo->BeNull())->ShouldBeTrue();
	}
	
	public function ItShouldAssertAsGreaterThan()
	{
		$foo = new SpecAssertion(10);
		Check($foo->BeGreaterThan(5))->ShouldBeTrue();
	}	

	public function ItShouldAssertDifferentValuesAsGreaterThanOrEqualTo()
	{
		$foo = new SpecAssertion(8);
		Check($foo->BeGreaterThanOrEqualTo(5))->ShouldBeTrue();
	}	

	public function ItShouldAssertSameValuesAsGreaterThanOrEqualTo()
	{
		$foo = new SpecAssertion(5);
		Check($foo->BeGreaterThanOrEqualTo(5))->ShouldBeTrue();
	}	

	public function ItShouldAssertAsLessThan()
	{
		$foo = new SpecAssertion(5);
		Check($foo->BeLessThan(10))->ShouldBeTrue();
	}	

	public function ItShouldAssertDifferentValuesAsLessThanOrEqualTo()
	{
		$foo = new SpecAssertion(3);
		Check($foo->BeLessThanOrEqualTo(5))->ShouldBeTrue();
	}	

	public function ItShouldAssertSameValuesAsLessThanOrEqualTo()
	{
		$foo = new SpecAssertion(5);
		Check($foo->BeLessThanOrEqualTo(5))->ShouldBeTrue();
	}
	
	public function ItShouldAssertThatKeyExistsInArray()
	{
		$foo = new SpecAssertion(array('firstname' => 'big', 'lastname' => 'boy'));
		Check($foo->HaveKey('lastname'))->ShouldBeTrue();
	}

	public function ItShouldAssertThatArrayContains()
	{
		$foo = new SpecAssertion(array('firstname' => 'big', 'lastname' => 'boy'));
		Check($foo->Contain('boy'))->ShouldBeTrue();
	}
	
	public function ItShouldAssertThatArrayIsEmpty()
	{
		$foo = new SpecAssertion(array());
		Check($foo->BeEmpty())->ShouldBeTrue();
		
	}
	
	public function ItShouldAssertThatValueMatchesRegexPattern()
	{
		$foo = new SpecAssertion('http://www.designinginteractive.com/');
		Check($foo->MatchRegex('@^(?:http://)?([^/]+)@i'))->ShouldBeTrue();
	}
	
	public function ItShouldAssertThatAStringIsAString()
	{
		$foo = new SpecAssertion('teststring');
		Check($foo->BeOfType('string'))->ShouldBeTrue();		
	}
	
	public function ItShouldAssertThatAnIntegerIsAnInteger()
	{
		$foo = new SpecAssertion(5);
		Check($foo->BeOfType('integer'))->ShouldBeTrue();				
	}

	public function ItShouldAssertThatAnArrayIsAnArray()
	{
		$foo = new SpecAssertion(array(5,4,3));
		Check($foo->BeOfType('array'))->ShouldBeTrue();						
	}
	
	public function ItShouldAssertThatObjectIsCorrectInstanceType()
	{
		$foo = new SpecAssertion(new Date('5/24/1983'));
		Check($foo->BeInstanceOf('date'))->ShouldBeTrue();				
	}
	
	public function ItShouldAssertThatAnObjectIsLoaded()
	{		
		$foo = new SpecAssertion(new User(1));
		Check($foo->BeLoaded())->ShouldBeTrue();				
	}
}

?>