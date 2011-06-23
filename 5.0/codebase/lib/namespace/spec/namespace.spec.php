<?php

class NamespaceSpec extends DescribeBehavior
{
	public function ItShouldIncludeClasses()
	{
		SandstoneNamespace::Using("lib.namespace.spec.utility");

		$condition = class_exists("NamespaceTest") && class_exists("NamespaceTest2");

		return $this->Expects($condition)->ToBeEqualTo(true);
	}

	public function ItShouldLazyLoadClass()
	{
		SandstoneNamespace::Using("lib.namespace.spec.utility");

		$condition = false;
		$before = class_exists("NamespaceLoadTest", false);
		$after = class_exists("NamespaceLoadTest");
	
		if ($before == false && $after == true)
		{
			$condition = true;
		}
	
		return $this->Expects($condition)->ToBeEqualTo(true);
	}

	public function ItShouldAllowNamespacesToBeUsedMultipleTimes()
	{
		SandstoneNamespace::Using("lib.namespace.spec.utility");
		SandstoneNamespace::Using("lib.namespace.spec.utility");

		$condition = class_exists("NamespaceTest");

		return $this->Expects($condition)->ToBeEqualTo(true);
	}

	public function ItShouldIncludeWildcardNamespaces()
	{
		SandstoneNamespace::Using("lib.namespace.spec.utility.wildcard.*");

		return $this->Expects(class_exists("WildcardTest"))->ToBeEqualTo(true);
	}

	public function ItShouldOverwriteClassDefinitions()
	{
		SandstoneNamespace::Using("lib.namespace.spec.utility.override");

		$test = new OverrideTest();

		return $this->Expects($test->IsNewClass)->ToBeEqualTo(true);
	}

	public function ItShouldGiveAListOfIncludedClasses()
	{
		SandstoneNamespace::Using("lib.namespace.spec.utility");

		return $this->Expects(SandstoneNamespace::Classes())->ToContain("wildcardtest");
	}
}

