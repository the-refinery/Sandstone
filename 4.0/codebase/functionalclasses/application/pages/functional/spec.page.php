<?php

Namespace::Using("Sandstone.Spec");

class SpecPage extends BasePage
{
	protected $_specs;
	
	public function __construct()
	{
		parent::__construct();

		$this->_isLoginRequired = false;
		$this->_allowedRoleIDs = Array();
	}
			
	public function Generic_PreProcessor($EventParameters)
	{
		parent::Generic_PreProcessor($EventParameters);

		$this->_specs = SpecLoader::FetchTestFiles($EventParameters['specname']);
	}
	
	protected function HTM_Processor($EventParameters)
	{
		$this->_template->MasterLayoutFileName = "spec";
	}
	
	public function TestCasesCallback($CurrentElement, $Template)
	{		
		$Template->TestName = $CurrentElement->FriendlyTestName;
		$Template->Message = $CurrentElement->Message;

		if ($CurrentElement->TestResult == true)
		{
			$Template->Filename = "testcases_item_passed";
		}
		else
		{
			$Template->Filename = "testcases_item_failed";			
		}		
	}
	
	public function FailedTestsCallback($CurrentElement, $Template)
	{
		$Template->TestName = $CurrentElement->FriendlyTestName;
		$Template->Message = $CurrentElement->Message;
	}
	
	public function TestClassesCallback($CurrentElement, $Template)
	{
		$TestClassName = $CurrentElement;
		
		// Show the name of the spec, without the trailing "spec"
		$Template->TestClassName = substr($CurrentElement,0,strlen($CurrentElement) - 4);
		
		$testClass = new $TestClassName();
		$testClass->Run();
		
		$Template->NumberPassing = count($testClass->PassedTests);
		$Template->NumberFailing = count($testClass->FailedTests);
		$Template->TotalSpecs = count($testClass->Tests);
		
		$Template->ElapsedTime = $testClass->TimeToRun . " seconds";
		
		$this->TestClasses->CurrentRepeaterItem->TestCases = new RepeaterControl();
		$this->TestClasses->CurrentRepeaterItem->TestCases->Data = $testClass->TestResults;
		$this->TestClasses->CurrentRepeaterItem->TestCases->SetCallback($this,"TestCasesCallback");

		$this->TestClasses->CurrentRepeaterItem->FailedTests = new RepeaterControl();
		$this->TestClasses->CurrentRepeaterItem->FailedTests->Data = $testClass->FailedTests;
		$this->TestClasses->CurrentRepeaterItem->FailedTests->SetCallback($this,"FailedTestsCallback");
	}
	
	protected function BuildControlArray($EventParameters)
	{
		$this->TestClasses = new RepeaterControl();
		$this->TestClasses->Data = $this->_specs;
		$this->TestClasses->SetCallback($this, "TestClassesCallback");

		parent::BuildControlArray($EventParameters);
	}
}

?>