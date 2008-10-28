<?php

class FlashMessageControl extends DIVControl
{
	protected $_mode;
	
	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('flashmessage_body');
		
		// Div is defaulted to invisible.
		$this->_bodyStyle->AddStyle('display:none;');

		// Set the default mode to 'good'
		$this->_mode = 1;
		$this->ChangeMode();
	}
	
	public function setInnerHTML($Value)
	{
		if (strlen($Value) > 0)
		{
			$this->_innerHTML = $Value;

			// Show the div (remove display:none from constructor)
			$this->_bodyStyle->ClearStyle();
		}
	}
	
	public function getMode()
	{
		return $this->_mode;
	}

	public function setMode($Value)
	{
		if (strlen($Value) > 0)
		{
			$this->_mode = $Value;

			$this->ChangeMode();
		}
	}

	protected function ChangeMode()
	{
		// Clear Classes
		$this->_bodyStyle->RemoveClass('badalert');
		$this->_bodyStyle->RemoveClass('infoalert');
		$this->_bodyStyle->RemoveClass('goodalert');
		
		// Set the class depending on the mode
		switch ($this->_mode)
		{
			case -1:				
				$this->_bodyStyle->AddClass('badalert');
				break;
				
			case 0:
				$this->_bodyStyle->AddClass('infoalert');
				break;
				
			case 1:
				$this->_bodyStyle->AddClass('goodalert');
				break;
		}
	}
}

?>