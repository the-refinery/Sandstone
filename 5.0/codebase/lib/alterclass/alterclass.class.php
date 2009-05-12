<?php

class AlterClass
{
	static public function Mixin($TargetClass, $SourceClass)
	{
		$methods = get_class_methods($SourceClass);

    foreach ($methods as $method) {
			eval("$TargetClass::\$_mixins['$method'] = array('$SourceClass','$method');");
    }
	}
}
