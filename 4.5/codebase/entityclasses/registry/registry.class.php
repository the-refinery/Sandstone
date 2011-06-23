<?php
/*
Registry Class

@package Sandstone
@subpackage Registry
*/

SandstoneNamespace::Using("Sandstone.Utilities.XML");

class Registry
{
	protected $_registry = array();

	public function __construct()
	{
		$this->LoadRegistry("registry");
		$this->LoadRegistry("resources/dev");
	}

	public function __get($property)
	{
		if ($property == "Keys")
		{
			$returnValue = $this->_registry;
		}
		else
		{
			$property = strtolower($property);

			if (is_set($this->_registry[$property]))
			{
				$returnValue = $this->_registry[$property];
			}
			else
			{
				$returnValue = null;
			}
		}

		return $returnValue;
	}

	public function Display()
	{

		echo "<h1>Registry</h1>";
		echo "<table style=\"border: 1px solid #000;\">";
		echo "<tr><th style=\"border: 1px solid #000;\">Key</th><th style=\"border: 1px solid #000;\">Value</th></tr>";

		foreach ($this->_registry as $key=>$value)
		{

			if (is_set($backgroundStyle))
			{
				unset($backgroundStyle);
			}
			else
			{
				$backgroundStyle = "style=\"background: #ccc;\"";
			}

			echo "<tr {$backgroundStyle}><td style=\"border: 1px solid #000;\">{$key}</td>";

			echo "<td style=\"border: 1px solid #000;\">";

			if (is_array($value))
			{
				echo "<pre>";
				print_r($value);
				echo "</pre>";
			}
			else
			{
				echo $value;
			}

			echo "</td></tr>";
		}

		echo "</table>";

	}

	protected function LoadRegistry($RegistryPath)
	{
		// Find Application Registry XML Files
		$files = $this->FindRegistryFiles($RegistryPath);

		if ($files != false)
		{
			//Loop each file found
			foreach ($files as $file)
			{
				$structuredValues = $this->LoadRegistryFile($file);
				$this->PopulateRegistry($structuredValues);
			}
		}
	}

	protected function FindRegistryFiles($Path)
	{
		// Get Filenames for all registry files
		$fullDirectorySpec = ResolveFullDirectoryPath($Path);
		$pattern = $fullDirectorySpec . "*.registry.xml";

		$returnValue = glob($pattern);

		return $returnValue;
	}

	protected function LoadRegistryFile($File)
	{
		//Load the file's contents
		$registryValues = $this->LoadRegistryFromXML($File);

		//If the file had content, merge the results with the existing registry.
		//Duplicate keys are overwritten
		if (count($registryValues) > 0)
		{
			foreach ($registryValues as $key => $value)
			{
				$key = strtolower($key);
				$returnValue[$key] = $value;
			}
		}

		return $returnValue;
	}

	protected function LoadRegistryFromXML($XMLfileSpec)
	{

		//Make sure the file exists
        if (file_exists($XMLfileSpec))
		{
			//Load it's contents
			$xml = file_get_contents($XMLfileSpec);

			//Convert to an array
			$returnValue = array_change_key_case(DIxml::XMLtoArray($xml),CASE_LOWER);

		}
		else
		{
			$returnValue = Array();
		}

		return $returnValue;

	}

	protected function PopulateRegistry($StructuredRegistryValues)
	{
		// This takes the structured values (read from XMLtoArray natively)
		// and populates $this->_registry with key => value pairs
		if (count($StructuredRegistryValues) > 0)
		{
			foreach ($StructuredRegistryValues as $key => $value)
			{
				// Set the value, and overwrite any existing key with the same name
				$this->_registry[$key] = $value;
			}
		}
	}
}

?>