<?php

Namespace::Using("Sandstone.Dingus.Database");

class DingusSpec extends SpecBase
{
	protected $_dingus;
	
	public function BeforeEach()
	{
		$this->_dingus = new Dingus('TestDingus');
	}
	
	public function ItShouldInstantiateABareDingus()
	{
		Check($this->_dingus)->ShouldBeInstanceOf('Dingus');		
	}
	
	public function ItShouldAcceptAName()
	{
		Check($this->_dingus->Stack())->ShouldContain('testdingus Initialized');
	}
	
	public function ItShouldReturnADingusWhenCallingAProperty()
	{
		Check($this->_dingus->TestProperty)->ShouldBeInstanceOf('Dingus');
	}
	
	public function ItShouldReturnADingusWhenCallingAMethod()
	{
		Check($this->_dingus->TestMethod())->ShouldBeInstanceOf('Dingus');
	}
	
	public function ItShouldPropertlyNameANestedDingus()
	{
		$this->_dingus->TestMethod();
		
		Check($this->_dingus->TestMethod()->Stack())->ShouldContain('testdingus->testmethod Initialized');
	}
	
	public function ItShouldAllowNestingOfDinguses()
	{
		Check($this->_dingus->TestProperty->FooMethod()->AnotherTestProperty)->ShouldBeInstanceOf('Dingus');
	}
	
	public function ItShouldKeepALogOfCalls()
	{
		$this->_dingus->TestMethod();
		$this->_dingus->AnotherMethod();
		$this->_dingus->FooBar('a', 'b', 'c');
		
		Check($this->_dingus->Stack())->ShouldContain("foobar()");
	}
	
	public function ItShouldReturnACustomReturnValueForAMethod()
	{
		$this->_dingus->SetReturnValue('TestCustom()', true);

		Check($this->_dingus->TestCustom())->ShouldBeTrue();
	}

	public function ItShouldReturnACustomReturnValueForAProperty()
	{
		$this->_dingus->SetReturnValue('TestCustomProperty', true);

		Check($this->_dingus->TestCustomProperty)->ShouldBeTrue();
	}	
}

?>