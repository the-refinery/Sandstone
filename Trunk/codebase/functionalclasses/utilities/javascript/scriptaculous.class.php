<?php

class Scriptaculous extends Prototype
{
	protected $_scope;

	public function __construct()
	{
		$this->_scope = "PageScope";
	}

	public function getScope()
	{
		return $this->_scope;
	}

	public function setScope($Value)
	{
		$this->_scope = $Value;
	}

	protected function ParseParameters($Parameters)
	{
		$Parameters = $this->SetupDefaultParameters($Parameters);
				
		return DIarray::ImplodeAssoc(":", ",", $Parameters, true);
	}
	
	protected function SetupDefaultParameters($Parameters)
	{
		if (is_null($Parameters['queue']))
		{
			$Parameters['queue'] = 'end';			
		}
		
		if (is_null($Parameters['scope']))
		{
			$Parameters['scope'] = $this->_scope;			
		}
		
		return $Parameters;
	}
	
	/*
	Flip on/off without effect
	*/
	
	public function Show($ElementID, $Parameters = null)
	{
		$paramList = $this->ParseParameters($Parameters);
		
		$returnValue = "
				new Element.show('{$ElementID}',
					{
						{$paramList}
					});
		";
		
		return $returnValue;
	}
	
	public function Hide($ElementID, $Parameters = null)
	{
		$paramList = $this->ParseParameters($Parameters);
		
		$returnValue = "
				new Element.hide('{$ElementID}',
					{
						{$paramList}
					});
		";
		
		return $returnValue;
	}
	
	/*
	Display Effects
	*/
	
	protected function ShowEffect($ElementID, $Effect, $Parameters = null)
	{
		$paramList = $this->ParseParameters($Parameters);
		
		$returnValue = "
				new Effect.{$Effect}('{$ElementID}',
					{
						{$paramList}
					});
		";
		
		return $returnValue;
	}
	
	protected function HideEffect($ElementID, $Effect, $Parameters = null)
	{
		$paramList = $this->ParseParameters($Parameters);
		
		$returnValue = "
				new Effect.{$Effect}('{$ElementID}',
					{
						{$paramList}
					});
			";
		
		return $returnValue;
	}
	
	protected function AnimationEffect($ElementID, $Effect, $Parameters = null)
	{
		$paramList = $this->ParseParameters($Parameters);
		
		$returnValue = "
			new Effect.{$Effect}('{$ElementID}',
				{
					{$paramList}
				});
		";
		
		return $returnValue;
	}
	
	/*
	Toggle Show or Hide
	*/
	
	public function Toggle($ElementID, $Effect = "blind", $Parameters = null) // Possible Effects are 'appear', 'slide', and 'blind'
	{
		$paramList = $this->ParseParameters($Parameters);
		$Effect = strtolower($Effect);
		
		$returnValue = "new Effect.toggle('{$ElementID}', '{$Effect}',
							{
								{$paramList}
							});";
		
		return $returnValue;
	}
	
	
	/*
	Core Effects
	*/
	
	public function Opacity($ElementID, $Start = "1", $End = "0.5")
	{
		return $this->AnimationEffect($ElementID, 'Highlight', 
				array(
						'from' => $Start, 
						'to' => $End)
				);
	}
	
	public function Scale($ElementID, $Percent, $X = 'true', $Y = 'true', $Content = 'true', $FromCenter = 'false')
	{
	    $parameters = array(
                'scaleX' => $X,
                'scaleY' => $Y,
                'scaleContent' => $Content,
                'scaleFromCenter' => $FromCenter
            );
            
	    $paramList = $this->ParseParameters($parameters);
		
		$returnValue = "
			new Effect.Scale('{$ElementID}',{$Percent},
				{
					{$paramList}
				});
		";
		
		return $returnValue;
	}
	
	public function Move($ElementID, $X, $Y, $Mode = 'relative')
	{
	    $Mode = strtolower($Mode);
	    
	    return $this->AnimationEffect($ElementID, 'Move', 
	            array(
	                    'x' => $X,
	                    'y' => $Y,
	                    'mode' => $Mode)
	            );
	}
	
	/*
	Animation Effects
	*/
	
	public function Highlight($ElementID)
	{
		return $this->AnimationEffect($ElementID, 'Highlight');
	}
	
	public function Pulsate($ElementID)
	{
		return $this->AnimationEffect($ElementID, 'Pulsate');
	}
	
	public function Shake($ElementID)
	{
		return $this->AnimationEffect($ElementID, 'Shake');
	}
	
	/*
	Specific Appear Effects
	*/
	
	public function Appear($ElementID)
	{
		return $this->ShowEffect($ElementID, 'Appear');
	}
	
	public function BlindDown($ElementID)
	{
		return $this->ShowEffect($ElementID, 'BlindDown');
	}
	
	public function Grow($ElementID)
	{
		return $this->ShowEffect($ElementID, 'Grow');
	}
	
	public function SlideDown($ElementID)
	{
		return $this->ShowEffect($ElementID, 'SlideDown');
	}
	
	
	/*
	Specific Disappear Effects
	*/
	
	public function Fade($ElementID)
	{
		return $this->HideEffect($ElementID, 'Fade');
	}
	
	public function BlindUp($ElementID)
	{
		return $this->HideEffect($ElementID, 'BlindUp');
	}
	
	public function DropOut($ElementID)
	{
		return $this->HideEffect($ElementID, 'DropOut');
	}
	
	public function Fold($ElementID)
	{
		return $this->HideEffect($ElementID, 'Fold');
	}

	public function Puff($ElementID)
	{
		return $this->HideEffect($ElementID, 'Puff');
	}
	
	public function Shrink($ElementID)
	{
		return $this->HideEffect($ElementID, 'Shrink');
	}
	
	public function SlideUp($ElementID)
	{
		return $this->HideEffect($ElementID, 'SlideUp');
	}
	
	public function Squish($ElementID)
	{
		return $this->HideEffect($ElementID, 'Squish');
	}
	
	public function SwitchOff($ElementID)
	{
		return $this->HideEffect($ElementID, 'SwitchOff');
	}
		
	
    /*
	Navigation Effects
	*/
	
	public function ScrollTo($ElementID, $Parameters = null)
	{
		$paramList = $this->ParseParameters($Parameters);
		
		return "new Effect.ScrollTo('". $ElementID . "',
			{
				{$paramList}
			});";
	}

    /*
	CSS Manipulation
    */

    public function AddClassName($ElementID, $ClassName)
    {
        return "\$('{$ElementID}').addClassName('{$ClassName}');";
    }
    
    public function RemoveClassName($ElementID, $ClassName)
    {
        return "\$('{$ElementID}').removeClassName('{$ClassName}');";
    }
    
    /*
	DOM Manipulation
    */

    public function CleanWhitespace($ElementID)
    {
        return "Element.cleanWhitespace('{$ElementID}');";
    }
    
    public function Remove($ElementID)
    {
        return "Element.remove('{$ElementID}');";
    }
    
    public function Update($ElementID, $HTML)
    {
        return "Element.update('{$ElementID}','{$HTML}');";
    }
    
    public function Insert($ElementID, $Content, $Location = 'Bottom')
    {
        // $Location can be 'before', 'after', 'top', or 'bottom'
        
        $Location = ucfirst(strtolower($Location));
        
        return "Insertion.{$Location}('{$ElementID}','{$Content}');";
    }
}

?>