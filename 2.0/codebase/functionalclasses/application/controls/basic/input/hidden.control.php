<?php
/**
 * Hidden Control Class File
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

class HiddenControl extends InputBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "hidden";
		$this->_isValueReturned = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('hidden_general');
		$this->_bodyStyle->AddClass('hidden_body');

		$this->Message->BodyStyle->AddClass('hidden_message');
		$this->Label->BodyStyle->AddClass('hidden_label');
	}

    public function __toString()
    {
        //We override this because there is no need for an outer wrapper, message or buffer
        //on a hidden control
        if ($this->_isRendered)
        {
            $returnValue = $this->RenderControlBody();
        }

        return $returnValue;
    }


	protected function RenderControlBody()
	{
		$returnValue = $this->RenderInput();

		return $returnValue;
	}
	
}
?>