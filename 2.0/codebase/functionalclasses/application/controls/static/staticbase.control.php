<?php
/**
 * Static Base Control Class File
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

class StaticBaseControl extends BaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_isRawValuePosted = false;
	}

	public function __toString()
	{
		if ($this->_isRendered)
		{
			$returnValue = $this->RenderControlBody();
			$returnValue .= $this->RenderBufferDIV();
		}

		return $returnValue;
	}

    /**
	 * MasterControlDOMid property
	 *
	 * @return string
	 */
	public function getMasterControlDOMid()
	{
		return $this->Name;
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
			//Static controls themselves have no values
			$returnValue = null;
		}

		return $returnValue;
	}


	protected function SetupControls()
	{

	}

}
?>
