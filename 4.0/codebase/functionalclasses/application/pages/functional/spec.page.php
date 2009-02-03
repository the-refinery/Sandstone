<?php

Namespace::Using("Sandstone.Spec");

class SpecPage extends BasePage
{
	protected $_specs;
	
	public function __construct()
	{
		parent::__construct();

		$this->_template->MasterLayoutFileName = "spec";
		$this->_template->FileName = "spec";
	}
	
	public function Generic_PreProcessor($EventParameters)
	{
		parent::Generic_PreProcessor($EventParameters);
		
		$isDevMode = Application::Registry()->RunSpecs;
		
		if ($isDevMode == false)
		{
			$this->SetResponseCode(404, $EventParameters);			
		}
		
		$this->_specs = SpecLoader::FetchTestFiles($EventParameters['specname']);
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
		$this->TestClasses->Data = $this->_specs;
		$this->TestClasses->SetCallback($this, "TestClassesCallback");

		parent::BuildControlArray($EventParameters);
	}
}

?>