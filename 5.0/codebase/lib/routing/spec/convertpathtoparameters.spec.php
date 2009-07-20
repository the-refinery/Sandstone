<?php

class ConvertPathToParametersSpec extends DescribeBehavior
{
	public function ItShouldExplodeAPathBySlashes()
	{
		$parameters = ConvertPathToParameters::Convert('foo/bar');

		return $this->Expects($parameters)->ToContain('foo');
	}

	public function ItShouldAlwaysReturnLowerCaseParameters()
	{
		$parameters = ConvertPathToParameters::Convert("Foo/Bar");

		return $this->Expects($parameters)->ToContain('foo');
	}
}
