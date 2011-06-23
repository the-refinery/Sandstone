<?php

class SpecLoader extends Module
{
	static public function FetchTestFiles($SpecName = false)
	{
		GLOBAL $APPLICATION_ROOT_LOCATION;

		$SpecName = strtolower($SpecName);
		
		if ($SpecName && $SpecName != 'sandstone')
		{			
			$paths = explode(PATH_SEPARATOR, get_include_path());

			// Check if the spec you asked for exists in the include path, including inside a framework or sandstone
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
			if ($SpecName == 'sandstone')
			{
				$basePath = SandstoneNamespace::NamespaceEnviromentBase('sandstone');
			}
			else
			{
				$basePath = $APPLICATION_ROOT_LOCATION;
			}
			
			// Load all Specs in the current application
			$pattern = $basePath . "specs/" . $testsDirectory . "*.spec.php";			

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