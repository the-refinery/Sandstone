<?php

class DescribeBehaviorSpec extends DescribeBehavior
{
	protected $_spec;
	protected $_fooSpec;

	public function BeforeEach()
	{
		$this->_spec = new DescribeBehavior();
		$this->_fooSpec = new FooSpec();
	}

	public function ItShouldSetTheExpectedValue()
	{
		$condition = $this->_spec->Expects('expectedValue');

		return $this->Expects($condition->ExpectedValue)->ToBeEqualTo('expectedValue');
	}

	public function ItShouldSetTheActualValue()
	{
		$condition = $this->_spec->Expects('expectedValue')->ToBeEqualTo('actualValue');

		return $this->Expects($condition->ActualValue)->ToBeEqualTo('actualValue');
	}

	public function ItShouldKnowTheNameOfTheFile()
	{
		$condition = $this->_spec->Expects('expectedValue')->ToBeEqualTo('actualValue');

		return $this->Expects($condition->Filename)->ToExist();
	}

	public function ItShouldKnowTheLineNumber()
	{
		$condition = $this->_spec->Expects('expectedValue')->ToBeEqualTo('actualValue');

		return $this->Expects($condition->LineNumber)->ToExist();
	}

	public function ItShouldSetTheNameOfTheSpec()
	{
		$condition = $this->_spec->Expects('expectedValue');

		return $this->Expects($condition->Name)->ToBeEqualTo('ItShouldSetTheNameOfTheSpec');
	}

	public function ItShouldSetTheNameOfTheDescribe()
	{
		$condition = $this->_fooSpec->Expects('expectedValue');

		return $this->Expects($condition->Spec->Name)->ToBeEqualTo('FooSpec');
	}
			
	public function ItShouldReportASpecAsPassing()
	{
		return $this->Expects($this->_fooSpec->ItIsAPassingSpec()->TestResult)->ToBeEqualTo(true);
	}

	public function ItShouldReportASpecAsFailing()
	{
		return $this->Expects($this->_fooSpec->ItIsAFailingSpec()->TestResult)->ToBeEqualTo(false);
	}

	public function ItShouldReportASpecAsPending()
	{
		return $this->Expects($this->_fooSpec->ItIsAPendingSpec()->TestResult)->ToBeEqualTo(RunSpecs::PENDING);
	}

	public function ItShouldFindSpecsToRun()
	{
		return $this->Expects($this->_fooSpec->FindSpecs())->ToContain('ItIsAPendingSpec');
	}

}
