<?php

class SpecLoader extends Module
{
	static public function FetchTestFiles()
	{
		GLOBAL $APPLICATION_ROOT_LOCATION;
		
		$testsDirectory = $APPLICATION_ROOT_LOCATION . "tests/";
		
		$pattern = $testsDirectory . "*.spec.php";
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
		
		return $returnValue;
	}
}


?>