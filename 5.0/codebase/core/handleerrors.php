<?php

function HandleError($ErrorNumber, $ErrorMessage, $ErrorFileName, $ErrorLineNumber)
{
	switch($ErrorNumber){
		case E_ERROR:               
			$errorType = "Error";
			break;
		case E_WARNING:
			$errorType = "Warning";
			break;
		case E_PARSE:
			$errorType = "Parse Error";
			break;
		case E_NOTICE:
			// Do nothing, Notice's are ignored in Sandstone
			break;
		case E_CORE_ERROR:
			$errorType = "Core Error";
			break;
		case E_CORE_WARNING:
			$errorType = "Core Warning";
			break;
		case E_COMPILE_ERROR:
			$errorType = "Compile Error";
			break;
		case E_COMPILE_WARNING:
			$errorType = "Compile Warning";
			break;
		case E_USER_ERROR:
			$errorType = "User Error";
			break;
		case E_USER_WARNING:
			$errorType = "User Warning";
			break;
		case E_USER_NOTICE:
			$errorType = "User Notice";
			break;
		case E_STRICT:
			$errorType = "Strict Notice";
			break;
		case E_RECOVERABLE_ERROR:
			$errorType = "Recoverable Error";
			break;
		default:
			$errorType = "Unknown error ($ErrorNumber)";
			break;
	}

	if ($errorType)
	{
		throw new ErrorException($errorType . ": " . $ErrorMessage, 0, $ErrorNumber, $ErrorFileName, $ErrorLineNumber);
	}

	return true;
}
