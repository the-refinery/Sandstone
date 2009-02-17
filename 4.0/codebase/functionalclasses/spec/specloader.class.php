<?php

class SpecLoader extends Module
{
	static public function FetchTestFiles($SpecName = false)
	{
		GLOBAL $SANDSTONE_ROOT_LOCATION;
		GLOBAL $APPLICATION_ROOT_LOCATION;
		
		if ($SpecName)
		{
			$SpecName = strtolower($SpecName);
			
			$paths = explode(PATH_SEPARATOR, get_include_path());

			// Check if the spec you asked for exists in the include path, including inside sandstone
			foreach ($paths as $path) 
			{
				$fullPath = $path . DIRECTORY_SEPARATOR . "/specs/{$SpecName}.spec.php";

				if (file_exists($fullPath)) 
				{
					require($fullPath);
					
					$returnValue[] = "{$SpecName}spec";
					break;
				}
			}
		}
		else
		{
			// Load all Specs
			$pattern = $APPLICATION_ROOT_LOCATION . "specs/" . $testsDirectory . "*.spec.php";			

			$tempTests = glob($pattern);

			foreach ($tempTests as $tempTest)
			{
				require($tempTest);

				$testNameStart = strrpos($tempTest, "/") + 1;
				$testNameEnd = strlen($tempTest) - 9;
				$testNameLength = $testNameEnd - $testNameStart;
				$testClassName = strtolower(substr($tempTest, $testNameStart, $testNameLength)) . "spec";

				$returnValue[] = $testClassName;
			}
		}
		
		return $returnValue;
	}
}


?>