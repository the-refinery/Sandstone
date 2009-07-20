<?php

class AlterClass extends BaseService
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
