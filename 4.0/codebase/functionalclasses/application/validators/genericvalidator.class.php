<?php
/*
Generic Validator Class File

@package Sandstone
@subpackage Application
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
		if (is_set($Control->Value) && is_numeric($Control->Value) == false)
		{
			$returnValue = $this->GenerateNamedMessage($Control, "is not numeric!");
		}

		return $returnValue;
	}
	 
	protected function GenerateNamedMessage($Control, $MessageBody)
	{
		if (is_set($Control->LabelText))
		{
			$name = $Control->LabelText;
		}
		else
		{
			$name = $Control->LocalName;
		}


		$returnValue = "{$name} {$MessageBody}";

		return  $returnValue;
	}

}

?>
