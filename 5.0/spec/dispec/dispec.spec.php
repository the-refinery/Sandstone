<?php

include_once('dependencies.php');

class DISpecSuiteSpec extends DISpecSuite
{
	public function ItShouldSetTheExpectedValue()
	{
		$spec = new DISpecSuite();
		$condition = $spec->Expects('expectedValue');

		return $this->Expects($condition->ExpectedValue)->ToBeEqualTo('expectedValue');
	}

	public function ItShouldReportASpecAsPassing()
	{
		$spec = new FooSpec();

		return $this->Expects($spec->ItIsAPassingSpec())->ToBeEqualTo(true);
	}

	public function ItShouldReportASpecAsFailing()
	{
		$spec = new FooSpec();

		return $this->Expects($spec->ItIsAFailingSpec())->ToBeEqualTo(false);
	}

	public function ItShouldReportASpecAsPending()
	{
		$spec = new FooSpec();

		return $this->Expects($spec->ItIsAPendingSpec())->ToBeEqualTo(null);
	}

	public function ItShouldFindSpecsToRun()
	{
		$spec = new FooSpec();

		return $this->Expects($spec->FindSpecs())->ToBeEqualTo(array('ItIsAPendingSpec','ItIsAPassingSpec','ItIsAFailingSpec'));
	}

	public function ItShouldRunSpecifiedSpecClasses()
	{
		$runner = new SpecRunner();
		$runner->AddSpecSuite('FooSpec');

		return $this->Expects($runner->SpecSuites[0] instanceof FooSpec)->ToBeEqualTo(true);
	}
}


