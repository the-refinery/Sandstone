<?php

class AlterClass
{
	static function Mixin($Target, $Mixin) 
	{
		$methods = get_class_methods($Mixin);
		
		foreach ($methods as $method) 
		{
			eval("$Target::\$_mixins['$method'] = array('$Mixin','$method');");
		}
	}
}
