<?php

class RoutingRule extends Module
{
	protected $_routingRule;
	
	protected $_routingURL;
	protected $_URLParameters;
	
	protected $_eventParameters = array();

	public function __construct($RoutingRule, $RoutingURL)
	{
		$this->setRoutingRule($RoutingRule);
		$this->setRoutingURL($RoutingURL);
	}
	
	public function getRoutingRule()
	{
		return $this->_routingRule;
	}

	public function setRoutingRule($Value)
	{
		$this->_routingRule = $this->ConvertRuleToRegEx($Value);
	}
	
	public function getRoutingURL()
	{
		return $this->_routingURL;
	}
	
	public function setRoutingURL($Value)
	{
		$this->_URLParameters = explode("/", $Value);
		$this->_routingURL = $Value;
	}
	
	public function getEventParameters()
	{
		return $this->_eventParameters;
	}
	
	public function AddEventParameter($Key, $Value)
	{
		$Key = strtolower($Key);
		$this->_eventParameters[$Key] = $Value;
	}
	
	public function AddDynamicEventParameter($Key, $ParameterLocation)
	{
		$Key = strtolower($Key);
		$this->_eventParameters[$Key] = $this->_URLParameters[$ParameterLocation];
	}
	
	public function CheckMatch($URL)
	{		
		$matchCheck = ereg($this->_routingRule, $URL);
		
		if ($matchCheck)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;				
	}
	
	protected function ConvertRuleToRegEx($rule)
	{
		$ruleParameters = explode("/", $rule);
		
		foreach ($ruleParameters as $key => $parameter) 
		{
			switch (strtolower($parameter)) 
			{
				case '[abc]':
					$ruleParameters[$key] = "[A-Za-z]+";
					break;
					
				case '[123]':
					$ruleParameters[$key] = "[0-9]+";
					break;
					
				case '[*]':
					$ruleParameters[$key] = "[A-Za-z0-9]+";
					break;
				
				default:
					$ruleParameters[$key] = "$parameter";
					break;
			}
		}
		
		$returnValue .= implode("\/", $ruleParameters);
				
		return $returnValue;
	}
}

?>