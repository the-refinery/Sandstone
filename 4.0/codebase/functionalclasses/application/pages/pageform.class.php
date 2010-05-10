<?php
/*
Page Form Class File

@package Sandstone
@subpackage Application
 */

class PageForm extends ControlContainer
{
	protected $_target;

	protected $_encType;

	protected $_redirectTarget;

	protected $_entityObject;
	protected $_isEntitySaveDisabled;
	protected $_entitySaveSuccessNotification;
	protected $_entitySaveFailureNotification;
	protected $_entitySaveSuccessRoutingAction;

	protected $_isValidated;
	protected $_isValidationPassed; 

	public function __construct($EventParameters)
	{
		parent::__construct();

		$this->_eventParameters = $EventParameters;

		// Set some default values
		$this->_encType = "multipart/form-data";

		$this->_isRawValuePosted = false;

		//Prep our template to use the form layout
		$this->_template->IsMasterLayoutUsed = true;
		$this->_template->MasterLayoutFileName = "form";

		$this->_isEntitySaveDisabled = false;

		$this->_isValidated = false;
		$this->_isValidationPassed = false;
	}

	/*
	Target property

	@return string
	@param string $Value
	 */
	public function getTarget()
	{
		return $this->_target;
	}

	public function setTarget($Value)
	{
		$this->_target = $Value;
	}

	/*
	RedirectTarget property

	@return string
	@param string $Value
	 */
	public function getRedirectTarget()
	{
		return $this->_redirectTarget;
	}

	public function setRedirectTarget($Value)
	{
		$this->_redirectTarget = $Value;
	}

	/*
	EntityObject property

	@return Entity
	@param Entity $Value
	 */
	public function getEntityObject()
	{
		return $this->_entityObject;
	}

	public function setEntityObject($Value)
	{
		$this->_entityObject = $Value;
	}

	/*
	IsEntitySaveDisabled property

	@return boolean
	@param boolean $Value
	 */
	public function getIsEntitySaveDisabled()
	{
		return $this->_isEntitySaveDisabled;
	}

	public function setIsEntitySaveDisabled($Value)
	{
		$this->_isEntitySaveDisabled = $Value;
	}

	/*
	EntitySaveSuccessNotification property

	@return string
	@param string $Value
	 */
	public function getEntitySaveSuccessNotification()
	{
		return $this->_entitySaveSuccessNotification;
	}

	public function setEntitySaveSuccessNotification($Value)
	{
		$this->_entitySaveSuccessNotification = $Value;
	}

	/*
	EntitySaveFailureNotification property

	@return string
	@param string $Value
	 */
	public function getEntitySaveFailureNotification()
	{
		return $this->_entitySaveFailureNotification;
	}

	public function setEntitySaveFailureNotification($Value)
	{
		$this->_entitySaveFailureNotification = $Value;
	}

	/*
	EntitySaveSuccessRoutingAction property

	@return string
	@param string $Value
	 */
	public function getEntitySaveSuccessRoutingAction()
	{
		return $this->_entitySaveSuccessRoutingAction;
	}

	public function setEntitySaveSuccessRoutingAction($Value)
	{
		$this->_entitySaveSuccessRoutingAction = $Value;
	}

	public function getIsValidated()
	{
		return $this->_isValidated;
	}

	public function setIsValidated($Value)
	{
		$this->_isValidated = $Value;
	}

	public function getIsValidationPassed()
	{
		return $this->_isValidationPassed;
	}

	public function setIsValidationPassed($Value)
	{
		$this->_isValidationPassed = $Value;
	}

	public function Render()
	{

		$this->_template->FormName = $this->_name;
		$this->_template->RequestedURL = Routing::GetRequestedURL();

		if (is_set($this->_target))
		{
			$this->_template->Target = "target=\"{$this->_target}\"";
		}

		//Loop through the controls and see if we have a file control
		foreach ($this->AllActiveControls as $tempControl)
		{
			if ($tempControl instanceof FileControl)
			{
				//We have a file control, add the ENC type
				$this->_template->EncType = "enctype=\"multipart/form-data\"";
			}
		}

		//Now call our parent's render method to generate the actual output.
		$returnValue =  parent::Render();

		return $returnValue;

	}

}
?>
