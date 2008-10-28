<?php
/**
 * OL Control Class File
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

class OLcontrol extends ListBaseControl
{

   	public function __construct()
	{

		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('ol_body');

		$this->_listType = "ol";

	}
	
}

?>
