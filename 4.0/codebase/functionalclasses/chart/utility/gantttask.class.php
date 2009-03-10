<?php

class GanttTask extends Module
{

	const COMPLETE_STATUS = 0;
	const ON_TIME_STATUS = 1;
	const LATE_STATUS = 2;
	const FUTURE_STATUS = 3;
	const OTHER_STATUS = 4;

	protected $_name;
	protected $_units;
	protected $_status;

	/*
	Name property

	@return string
	@param string $Value
	 */
	public function getName()
	{
		return $this->_name;
	}

	public function setName($Value)
	{
		$this->_name = $Value;
	}

	/*
	Units property

	@return decimal
	@param decimal $Value
	 */
	public function getUnits()
	{
		return $this->_units;
	}

	public function setUnits($Value)
	{
		$this->_units = $Value;
	}

	/*
	Status property

	@return integer
	@param integer $Value
	 */
	public function getStatus()
	{
		return $this->_status;
	}

	public function setStatus($Value)
	{
		$this->_status = $Value;
	}
}
?>