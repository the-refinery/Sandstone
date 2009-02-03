<?php

Namespace::Using("Sandstone.Spec");

class SpecPage extends BasePage
{
	protected $_testClasses;
	
	public function __construct()
	{
		parent::__construct();

		$this->_template->MasterLayoutFileName = "spec";
		$this->_template->FileName = "spec";
	}
	
	public function Generic_PreProcessor($EventParameters)
	{
		parent::Generic_PreProcessor($EventParameters);
		
		$isDevMode = Application::Registry()->IsDevMode;
		
		if ($isDevMode == false)
		{
			$this->SetResponseCode(404, $EventParameters);			
		}
		
		GLOBAL $APPLICATION_ROOT_LOCATION;
		
		$testsDirectory = $APPLICATION_ROOT_LOCATION . "tests/";
		
		$pattern = $testsDirectory . "*.test.php";
		$tempTests = glob($pattern);
		
		foreach ($tempTests as $tempTest)
		{
			require($tempTest);
			
			$testNameStart = strrpos($tempTest, "/") + 1;
			$testNameEnd = strlen($tempTest) - 9;
			$testNameLength = $testNameEnd - $testNameStart;
			$testClassName = strtolower(substr($tempTest, $testNameStart, $testNameLength)) . "test";
						
			$this->_testClasses[] = $testClassName;
		}		
		
	}
	
	public function TestCasesCallback($CurrentElement, $Template)
	{
		$Template->TestName = $CurrentElement->FriendlyTestName;
		$Template->TestResult = $CurrentElement->TestResult;

		if ($CurrentElement->IsPassed)
		{
			$Template->Filename = "testcases_item_passed";
		}
		else
		{
			$Template->Filename = "testcases_item_failed";			
		}
	}
	
	public function TestClassesCallback($CurrentElement, $Template)
	{
		$TestClassName = $CurrentElement;
		$Template->TestClassName = $CurrentElement;
		
		$testClass = new $TestClassName();
		$testClass->Run();
				
		$this->TestClasses->CurrentRepeaterItem->TestCases = new RepeaterControl();
		$this->TestClasses->CurrentRepeaterItem->TestCases->Data = $testClass->TestResults;
		$this->TestClasses->CurrentRepeaterItem->TestCases->SetCallback($this,"TestCasesCallback");
	}
	
	protected function BuildControlArray($EventParameters)
	{
		$this->TestClasses = new RepeaterControl();
		$this->TestClasses->Data = $this->_testClasses;
		$this->TestClasses->SetCallback($this, "TestClassesCallback");

		parent::BuildControlArray($EventParameters);
	}
}

?>