<?php
/**
 * Generic Validator Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */

class GenericValidator extends Module
{
	public function IsRequired($Control)
	{			
		if (is_set($Control->Value) == false)
		{
			$returnValue = $this->GenerateNamedMessage($Control, "is required!");			
		}
		elseif (is_object($Control->Value) == false) 
		{
			if (strlen($Control->Value) == 0) 
			{
				$returnValue = $this->GenerateNamedMessage($Control, "is required!");	
			}
		}
		
		return $returnValue;
	}
	
	public function IsNumeric($Control)
	{
		if (! is_numeric($Control->Value))
		{
			$returnValue = $this->GenerateNamedMessage($Control, "is not numeric!");
		}
		
		return $returnValue;
	}

	protected function GenerateNamedMessage($Control, $MessageBody)
	{
		
		if (is_set($Control->Label->Text))
		{
			$name = $Control->Label->Text;
		}
		else
		{
			$name = $Control->Name;
		}
		

		$returnValue = "{$name} {$MessageBody}";
		
		return  $returnValue;
	}
}

?>