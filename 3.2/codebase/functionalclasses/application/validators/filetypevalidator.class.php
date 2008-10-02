<?php
/*
FileType Validator Class File

@package Sandstone
@subpackage Application
*/

class FileTypeValidator extends Module
{

	public function IsCSV($Control)
	{
		$validCSVtypes[] = "text/csv";
		$validCSVtypes[] = "application/csv";
		$validCSVtypes[] = "application/vnd.ms-excel";
		$validCSVtypes[] = "application/x-filler";
		$validCSVtypes[] = "text/comma-separated-values";
		$validCSVtypes[] = "text/plain";

		if (in_array($Control->FileType, $validCSVtypes) == false)
		{
			$returnValue = $this->GenerateNamedMessage($Control, "is not a CSV file!");
		}

		return $returnValue;
	}

	protected function GenerateNamedMessage($Control, $MessageBody)
	{

		if (is_set($Control->OriginalFileName))
		{
			$name = $Control->OriginalFileName;
		}
		else
		{
			$name = "File";
		}


		$returnValue = "{$name} {$MessageBody}";

		return  $returnValue;
	}

}

?>