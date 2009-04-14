<?php

class Factory extends Module
{
	static public function Create($ClassName)
	{
		return new $ClassName(func_get_args());
	}	
}

?>
