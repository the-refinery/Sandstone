<?php

class RunSpecsSpec extends DescribesBehavior
{
	public function ItShouldRunSpecifiedSpecClasses()
	{
		$runner = new SpecRunner();
		$runner->DescribeBehavior('FooSpec');

		return $this->Expects($runner->SpecSuites[0] instanceof FooSpec)->ToBeTrue();
	}

	public function ItShouldLogPassingSpecs()
	{
		$runner = new SpecRunner();
		$runner->DescribeBehavior('FooSpec');
		$runner->Run();

		return $this->Expects($runner->Passing[0])->ToExist();
	}
		
	public function ItShouldLogFailingSpecs()
	{
		$runner = new SpecRunner();
		$runner->DescribeBehavior('FooSpec');
		$runner->Run();

		return $this->Expects($runner->Failing[0])->ToExist();
	}
		
	public function ItShouldLogPendingSpecs()
	{
		$runner = new SpecRunner();
		$runner->DescribeBehavior('FooSpec');
		$runner->Run();

		return $this->Expects($runner->Pending[0])->ToExist();
	}

	public function ItShouldCreateAnEnglishSpecDescription()
	{
		$runner = new SpecRunner();
		$testResult = $runner->CreateEnglishSpecDescription("SandstoneSpec", "ItShouldBeTheBestFrameworkEver");

		return $this->Expects($testResult)->ToBeEqualTo("Sandstone should be the best framework ever");
	}
}

?>
