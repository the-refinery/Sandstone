<?php

class GeneralObject extends BasePrimitive
{
	public $_foo = 'foo';

	public function getFoo()
	{
		return $this->_foo;
	}

	public function setFoo($Value)
	{
		$this->_foo = $Value;
	}

	public function setSetOnlyProperty($Value)
	{
	}
}
