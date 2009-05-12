<?php

include_once('dependencies.php');

class AlterClassSpec extends DescribesBehavior
{
	public function ItShouldMixClasses()
	{
		AlterClass::Mixin('TargetClass', 'SourceClass');

		$target = new TargetClass();

		return $this->Expects($target->String('foobar'))->ToBeEqualTo('foobar');
	}
}
