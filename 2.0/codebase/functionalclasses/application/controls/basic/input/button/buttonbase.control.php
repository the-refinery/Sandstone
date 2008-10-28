<?php
/**
 * Base Button Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */

class ButtonBaseControl extends InputBaseControl
{

	public function __construct()
	{
		parent::__construct();
		
		//Setup the default style classes
		$this->_controlStyle->AddClass('button_general');
		$this->_bodyStyle->AddClass('button_body');

		$this->Message->BodyStyle->AddClass('button_message');
		$this->Label->BodyStyle->AddClass('button_label');

		//Set this default style to always display inline
		$this->_bodyStyle->AddStyle("display:inline;");
	}
		
	/**
	 * PostControlValue property
	 *
	 * @return string
	 */
	public function getPostControlValue()
	{

		if ($this->IsCompoundControl)
		{
			//Return the PostControlValue for the child controls
			$returnValue = $this->BuildChildPostControlValues();
		}
		else
		{
			//Button controls themselves don't have a useful value to return
			$returnValue = null;
		}

		return $returnValue;
	}

	public function RenderInput()
	{

		//Since we don't do a label tag, the label will go to the
		//button caption, as set by the "value" property
		if (is_set($this->Label->Text))
		{
			$this->_defaultValue = $this->Label->Text;
		}

		$returnValue = parent::RenderInput();

		return $returnValue;
	}

}
?>