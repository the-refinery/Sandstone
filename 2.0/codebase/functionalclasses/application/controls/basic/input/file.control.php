<?php
/**
 * File Control Class File
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

class FileControl extends InputBaseControl
{	
	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "file";
		$this->_isValueReturned = false;

		//Setup the default style classes
		$this->_controlStyle->AddClass('file_general');
		$this->_bodyStyle->AddClass('file_body');

		$this->Message->BodyStyle->AddClass('file_message');
		$this->Label->BodyStyle->AddClass('file_label');

	}
	
	protected function RenderControlBody()
	{	
		//Define the label
		$returnValue = $this->RenderLabel();
		$returnValue .= $this->RenderInput();

		return $returnValue;
	}	
	
	/**
	 * FileName property
	 * 
	 * @return string
	 */
	public function getFileName()
	{
		return $this->_value['name'];
	}
	
	/**
	 * Type property
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->_value['type'];
	}
	
	/**
	 * TemporaryFileSpec property
	 * 
	 * @return string
	 */
	public function getTemporaryFileSpec()
	{
		return $this->_value['tmp_name'];
	}
	
	/**
	 * Size property
	 * 
	 * @return int
	 */
	public function getSize()
	{
		return $this->_value['size'];
	}
}
?>