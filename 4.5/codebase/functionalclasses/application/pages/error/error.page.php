<?php

class ErrorPage extends BasePage
{
	protected $_isLoginRequired = false;
	protected $_requiredRoleIDs = Array();

	const FATAL = 0;
	const WARNING = 1;
	const INFO = 2;
	
	public function __construct()
	{
		parent::__construct();

		$this->_template->IsMasterLayoutUsed = false;
		$this->_template->FileName = "errorpage";
	}

	protected function GET_PreProcessor($EventParameters)
	{
		if (array_key_exists("Exception", $EventParameters))
		{
			
			// Split the name of the Exception into separate words by Capital Letter
			// Ex. InvalidPropertyException becomes Invalid Property Exception
			$className = get_class($EventParameters['Exception']);
			preg_match_all('/[A-Z][^A-Z]*/', $className, $nameWords);
			$exceptionType = implode(" ", $nameWords[0]);
			
			//$this->_template->Severity = $this->DetermineSeverity($EventParameters['Exception']->Severity());
			
			$this->_template->ExceptionType = $exceptionType;
			$this->_template->ExceptionOutput = $EventParameters['Exception']->__toString();
			
			$this->_template->ErrorMessage = $EventParameters['Exception']->getMessage();
			$this->_template->LineNumber = $EventParameters['Exception']->getLine();
			$this->_template->ErrorFile = $EventParameters['Exception']->getFile();
		}
	}
	
	protected function TERM_Processor($EventParameters)
	{
		$this->_template->IsMasterLayoutUsed = true;

		parent::TERM_Processor($EventParameters);		
	}
		
	protected function DetermineSeverity($Severity)
	{
		switch ($Severity)
		{
			case self::FATAL:
				$returnValue = "Fatal";
				break;

			case self::WARNING:
				$returnValue = "Warning";
				break;

			case self::INFO:
				$returnValue = "Info";
				break;
		}
		
		return $returnValue;
	}

}

?>