<?php

class NamespaceSpec extends DescribeBehavior
{
	public function ItShouldIncludeClass()
	{
		Namespace::Using("lib/namespace/spec/utility/namespacetest.class.php");

		return $this->Expects(class_exists("NamespaceTest"))->ToBeEqualTo(true);
	}

}
