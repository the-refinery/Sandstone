<?php

class ComponentSpec extends SpecBase
{
	protected $_instance;

	public function BeforeEach()
	{
		$this->_instance = new TestClass();
	}
	
	public function ItShouldCheckIfAPropertyExists()
	{
		Check($this->_instance->HasProperty('FirstName'))->ShouldBeTrue();
	}

	public function ItShouldReadAndWriteAProperty()
	{
		$this->_instance->FirstName = 'Josh';

		Check($this->_instance->FirstName)->ShouldBeEqualTo('Josh');
	}
}

class TestClass extends Component
{
	protected $_firstName;
	
	public function getFirstName()
	{
		return $this->_firstName;
	}

	public function setFirstName($Value)
	{
		$this->_firstName = $Value;
	}
}

?>
