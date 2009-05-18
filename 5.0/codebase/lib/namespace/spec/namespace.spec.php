<?php

class NamespaceSpec extends DescribeBehavior
{
	public function ItShouldIncludeClasses()
	{
		Namespace::Using("lib/namespace/spec/utility/");

		$condition = class_exists("NamespaceTest") && class_exists("NamespaceTest2");

		return $this->Expects($condition)->ToBeEqualTo(true);
	}
}
