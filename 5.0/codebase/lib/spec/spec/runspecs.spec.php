<?php

class RunSpecsSpec extends DescribeBehavior
{
	public function ItShouldRunSpecifiedSpecClasses()
	{
		$runner = new RunSpecs();
		$runner->DescribeBehavior('FooTest');

		return $this->Expects($runner->SpecSuites[0])->ToBeInstanceOf('FooTest');
	}

	public function ItShouldLogPassingSpecs()
	{
		$runner = new RunSpecs();
		$runner->DescribeBehavior('FooTest');
		$runner->Run();

		return $this->Expects($runner->Passing[0])->ToExist();
	}
		
	public function ItShouldLogFailingSpecs()
	{
		$runner = new RunSpecs();
		$runner->DescribeBehavior('FooTest');
		$runner->Run();

		return $this->Expects($runner->Failing[0])->ToExist();
	}
		
	public function ItShouldLogPendingSpecs()
	{
		$runner = new RunSpecs();
		$runner->DescribeBehavior('FooTest');
		$runner->Run();

		return $this->Expects($runner->Pending[0])->ToExist();
	}

	public function ItShouldCreateAnEnglishSpecDescription()
	{
		$runner = new RunSpecs();
		$testResult = $runner->CreateEnglishSpecDescription("SandstoneSpec", "ItShouldBeTheBestFrameworkEver");

		return $this->Expects($testResult)->ToBeEqualTo("Sandstone should be the best framework ever");
	}
}

?>
