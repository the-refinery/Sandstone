<?php

class SpecRunnerSpec extends DescribesBehavior
{
	public function ItShouldRunSpecifiedSpecClasses()
	{
		$runner = new SpecRunner();
		$runner->AddSpecSuite('FooSpec');

		return $this->Expects($runner->SpecSuites[0] instanceof FooSpec)->ToBeEqualTo(true);
	}
}

?>
