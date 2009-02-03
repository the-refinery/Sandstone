<?php

class SpecLoader extends Module
{
	static public function FetchTestFiles($SpecName = false)
	{
		GLOBAL $APPLICATION_ROOT_LOCATION;
		
		$testsDirectory = $APPLICATION_ROOT_LOCATION . "specs/";
		
		if ($SpecName)
		{
			$pattern = $testsDirectory . "{$SpecName}.spec.php";
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
		else
		{
			// Load all Specs
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
		}
		
		return $returnValue;
	}
}


?>