<?php

NameSpace::Using("Sandstone.Application");

class SystemPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
		
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		phpinfo();
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}

}

?>