<?php

class SmartyActionBase extends Module
{
	protected $_actionName;
	protected $_className;
	
	public function LinkTo($Action, $Object)
	{
		$this->_actionName = ucfirst(strtolower($Action)) . "Action";
		$this->_className = strtolower(get_class($Object));
		
		if (method_exists($this, $this->_actionName))
		{
			$methodName = $this->_actionName;
			$this->$methodName($Object);
		}
		else
		{
			throw new InvalidActionException();
		}
	}
	
}

?>