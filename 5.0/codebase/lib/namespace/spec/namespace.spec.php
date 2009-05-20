<?php

class NamespaceSpec extends DescribeBehavior
{
	public function ItShouldIncludeClasses()
	{
		Namespace::Using("lib.namespace.spec.utility");

		$condition = class_exists("NamespaceTest") && class_exists("NamespaceTest2");

		return $this->Expects($condition)->ToBeEqualTo(true);
	}

	public function ItShouldLazyLoadClass()
	{
		Namespace::Using("lib.namespace.spec.utility");

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
		Namespace::Using("lib.namespace.spec.utility");
		Namespace::Using("lib.namespace.spec.utility");

		$condition = class_exists("NamespaceTest");

		return $this->Expects($condition)->ToBeEqualTo(true);
	}

	public function ItShouldIncludeWildcardNamespaces()
	{
		Namespace::Using("lib.namespace.spec.utility.*");

		return $this->Expects(class_exists("WildcardTest"))->ToBeEqualTo(true);
	}

}

