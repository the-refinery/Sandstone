<?php
/*
Event Results Class File

@package Sandstone
@subpackage Application
*/

class EventResults extends Module
{

	protected $_value;
	protected $_successMessages;
	protected $_errorMessages;
	protected $_outputBuffer;

	protected $_isProcessingComplete;

	public function __construct()
	{
		$this->_isProcessingComplete = false;

		ob_start();
	}
	
	/**
	Value property
	
	@return bool
	@param bool $Value
	*/
	public function getValue()
	{
		return $this->_value;
	}

	public function setValue($Value)
	{
		$this->_value = $Value;
	}

	/*
	SuccessMessages property
	
	@return array()
	*/
	public function getSuccessMessages()
	{
		return $this->_successMessages;
	}

	/*
	ErrorMessages property
	
	@return array()
	*/
	public function getErrorMessages()
	{
		return $this->_errorMessages;
	}

	/*
	IsProcessingComplete property
	
	@return boolean
	@param boolean $Value
	*/
	public function getIsProcessingComplete()
	{
		return $this->_isProcessingComplete;
	}

	public function setIsProcessingComplete($Value)
	{
		$this->_isProcessingComplete = $Value;
	}

	public function Complete()
	{
		$this->_outputBuffer = ob_get_contents();
		ob_end_clean();
	}
	
	public function Flush()
	{
		echo $this->_outputBuffer;	
	}
	
	public function Clear()
	{
		ob_clean();
		$this->_outputBuffer = null;
	}
	
	public function AddSuccessMessage($MessageText)
	{
		$this->_successMessages[] = $MessageText;
	}
	
	public function AddErrorMessage($MessageText)
	{
		$this->_errorMessages[] = $MessageText;
	}
	
}

?>