<?php

class SpecRunnerSpec extends DescribesBehavior
{
	public function ItShouldRunSpecifiedSpecClasses()
	{
		$runner = new SpecRunner();
		$runner->AddSpecSuite('FooSpec');

		return $this->Expects($runner->SpecSuites[0] instanceof FooSpec)->ToBeEqualTo(true);
	}

	public function ItShouldLogPassingSpecs()
	{
		$runner = new SpecRunner();
		$runner->AddSpecSuite('FooSpec');
		$runner->Run();

		return $this->Expects($runner->Passing[0])->ToExist();
	}
		
	public function ItShouldLogFailingSpecs()
	{
		$runner = new SpecRunner();
		$runner->AddSpecSuite('FooSpec');
		$runner->Run();

		return $this->Expects($runner->Failing[0])->ToExist();
	}
		
	public function ItShouldLogPendingSpecs()
	{
		$runner = new SpecRunner();
		$runner->AddSpecSuite('FooSpec');
		$runner->Run();

		return $this->Expects($runner->Pending[0])->ToExist();
	}
}

?>
