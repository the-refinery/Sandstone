<?php
/**
 * Exception Class File
 * @package Sandstone
 * @subpackage Exception
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class DIException extends Exception 
{

	protected $_diCallStack;
	protected $_longestClassAndFunction;
	
	public function __construct($Message, $Code = 0)
	{		
		parent::__construct($Message, $Code);
		
		//Since we probably have an output buffer in play,
		//clear anything we've added.
		ob_clean();
		
		$this->BuildDIcallStack();
		
	}
	
	public function __toString()
	{
		
		$returnValue .= 
		'
					<h2><b>Error: </b>' . $this->getMessage() . '</h2>
					<h3><b>Line: </b>' . $this->getLine() . '</h3>
					<h3><b>File: </b>' . $this->getFile() . '</h3>
		';
		
		$returnValue .= $this->DItraceAsString();
				
		return $returnValue;
	}
	
	public function DItraceAsString()
	{
		
		$returnValue .= '<div class="CallStack">
						<table><tr>
						<th colspan="3">Call Stack Summary</th>
						</tr>';
				
		$returnValue .= $this->DItraceSimpleAsString();
		$returnValue .= '</table></div>';
		
		$returnValue .= '<div class="CallStack">
						<table><tr>
						<th colspan="3">Call Stack Detail</th>
						</tr>';
				
		$returnValue .= $this->DItraceDetailAsString();
		$returnValue .= '</table></div>';

		
		return $returnValue;
		
	}
	
	public function DItraceSimpleAsString()
	{
		
		$index = 0;
		
		foreach($this->_diCallStack as $traceItem)
		{
			if (is_set($traceItem['class']))
			{
				$lineItem = "<tr>";
				$lineItem .= "<td class=\"stackorder\">#{$index}</td>";
				$lineItem .= "<td class=\"linenumber\">{$this->LineNumberAsString($traceItem['line'])}</td>";
				$lineItem .= "<td>{$traceItem['class']}{$traceItem['type']}{$traceItem['function']}()<br />
									<i>{$traceItem['file']}</i>
									</td>";
				$lineItem .= "</tr>";
			}
			else 
			{
				$lineItem = "<tr>";
				$lineItem .= "<td class=\"stackorder\">#{$index}</td>";
				$lineItem .= "<td class=\"linenumber\">{$this->LineNumberAsString($traceItem['line'])}</td>";
				$lineItem .= "<td>({$traceItem['file']})</td>";
				$lineItem .= "</tr>";
			}
			
			$returnValue .= $lineItem;
			
			$index++;
		}
				
		return $returnValue;
	}

	protected function LineNumberAsString($LineNumber)
	{
		$paddedNumber = str_pad($LineNumber, 4, " ", STR_PAD_LEFT);
		
		$returnValue = "Line {$paddedNumber}";
		
		return $returnValue;
	}
	
	public function DItraceDetailAsString()
	{
		$index = 0;
		
		foreach($this->_diCallStack as $traceItem)
		{
			if (is_set($traceItem['class']))
			{
				$lineItem = "<tr>";
				$lineItem .= "<td class=\"stackorder\">#{$index}</td>";
				$lineItem .= "<td class=\"linenumber\">{$this->LineNumberAsString($traceItem['line'])}</td>";
				$lineItem .= "<td>{$traceItem['class']}{$traceItem['type']}{$traceItem['function']}{$traceItem['fullargs']}<br />
									<i>({$traceItem['file']})</i>
									</td>";
				$lineItem .= "</tr>";
			}
			else 
			{
				$lineItem = "<tr>";
				$lineItem .= "<td class=\"stackorder\">#{$index}</td>";
				$lineItem .= "<td class=\"linenumber\">{$this->LineNumberAsString($traceItem['line'])}</td>";
				$lineItem .= "<td>{$traceItem['file']}</td>";
				$lineItem .= "</tr>";
			}

			
			$returnValue .= $lineItem;
			
			$index++;
		}
				
		return $returnValue;
	}
	
	protected function BuildDIcallStack()
	{
		$originalTraceArray = $this->getTrace();
		
		//Build the top stack item
		$tempDItraceItem = Array();
		$tempDItraceItem['class'] = $originalTraceArray[0]['class'];
		$tempDItraceItem['type'] = $originalTraceArray[0]['type'];
		$tempDItraceItem['function'] = $originalTraceArray[0]['function'];
		
		$this->SetFunctionArgsElements($tempDItraceItem, $originalTraceArray[0]['args']);
		
		$tempDItraceItem['line'] = $this->getLine();
		$tempDItraceItem['file'] = $this->getFile();
		
		$this->_diCallStack[] = $tempDItraceItem;
		$this->SetMaxClassAndFunctionLength($tempDItraceItem);
		
		//Now loop through the rest, if there are any
		if (count($originalTraceArray) > 1)
		{
			for($i=1; $i < count($originalTraceArray); $i++)
			{
				$tempDItraceItem = Array();
				$tempDItraceItem['class'] = $originalTraceArray[$i]['class'];
				$tempDItraceItem['type'] = $originalTraceArray[$i]['type'];
				$tempDItraceItem['function'] = $originalTraceArray[$i]['function'];
				
				$this->SetFunctionArgsElements($tempDItraceItem, $originalTraceArray[$i]['args']);					
				
				$tempDItraceItem['line'] = $originalTraceArray[$i - 1]['line'];
				$tempDItraceItem['file'] = $originalTraceArray[$i - 1]['file'];
				
				$this->_diCallStack[] = $tempDItraceItem;
				$this->SetMaxClassAndFunctionLength($tempDItraceItem);
				
			}
		}
		
		//Add the base page call
		$i = count($originalTraceArray) - 1;
		
		$tempDItraceItem = Array();
		$tempDItraceItem['file'] = $originalTraceArray[$i]['file'];
		$tempDItraceItem['line'] = $originalTraceArray[$i]['line'];
		
		$this->_diCallStack[] = $tempDItraceItem;
		$this->SetMaxClassAndFunctionLength($tempDItraceItem);
	}
	
	/**
	 * MaxClassAndFunctionLength property
	 * 
	 * @return 
	 */
	protected function SetMaxClassAndFunctionLength($DItraceItem)
	{
		
		if (is_set($DItraceItem['class']))
		{
			//Length of Class->Method() string
			$totalLength = strlen($DItraceItem['class']) + strlen($DItraceItem['type']) + strlen($DItraceItem['function']) + 2;			
		}
		else 
		{
			//Length of the file name
			$totalLength = strlen($DItraceItem['file']);
		}
		
		//Save it if it's longer.
		if ($totalLength > $this->_longestClassAndFunction)
		{
			$this->_longestClassAndFunction = $totalLength;
		}
		
	}
	
	/**
	 * FunctionArgsElements property
	 * 
	 * @return 
	 */
	protected function SetFunctionArgsElements(&$DItraceItem, $ArguementsArray)
	{
		
		$simpleArgs = Array();
		$fullArgs = Array();
		
		if (count($ArguementsArray) > 0)
		{
			foreach($ArguementsArray as $tempArg)
			{
				if (is_array($tempArg))
				{
					$simpleArgs[] = "[Array]";
					$fullArgs[] = $this->FormatArrayArg($tempArg);
				}
				else 
				{
				
					$simpleArgs[] = $this->FormatArgValue($tempArg);
					$fullArgs[] = $this->FormatArgValue($tempArg);
				}
			}
		}
		
		$DItraceItem['args'] = "(" . implode(", ", $simpleArgs) . ")";
		$DItraceItem['fullargs'] = "(" . implode(", ", $fullArgs) . ")";
		
		return $returnValue;
	}
	
	protected function FormatArgValue($Value)
	{
		if (is_numeric($Value))
		{
			$returnValue = $Value;
		}
		else if (is_object($Value))
		{
			$returnValue = "&lt;" . get_class($Value) . "&gt;"; 	
		}
		else
		{
			$returnValue = "'" . $Value . "'";
		}
		
		return $returnValue;
	}
	
	protected function FormatArrayArg($Value)
	{
		
		$kvPairs = Array();
		
		foreach($Value as $tempKey=>$tempValue)
		{
			if (is_array($tempValue))
			{
				$kvPairs[] = $tempKey . "=>" . $this->FormatArrayArg($tempValue);
			}
			else 
			{
				$kvPairs[] = $tempKey . "=>" . $this->FormatArgValue($tempValue);	
			}
			
		}
		
		$returnValue = "[" . implode(", ", $kvPairs) . "]";
				
		return $returnValue;
		
		
	}
	
}

?>