<?php

class EntityPage extends ApplicationPage
{
	protected $_loadedEntity;

	protected $_entityType;
	protected $_EntityIDEventParameter;

  	protected function Generic_PreProcessor(&$EventParameters)
  	{
		$this->LoadEntity($EventParameters);

		// Invalid Entity - Throw 404
  		if ($this->_loadedEntity->IsLoaded == false)
  		{
			$this->_isOKtoLoadControls = false;
  			$this->SetResponseCode(404, $EventParameters);
  		}

		$this->_template->Entity = $this->_loadedEntity;

  		parent::Generic_PreProcessor($EventParameters);
  	}

	// Override in the page if the _EntityIDEventParameter is not the primary key
	// and thus cannot be loaded in the default manner.
	protected function LoadEntity($EventParameters)
	{
		$EntityType = $this->_entityType;
  		$this->_loadedEntity = new $EntityType($EventParameters[$this->_EntityIDEventParameter]);
	}

	protected function ValidateAJAXForm($Form)
	{

		//Do we have any controls to validate?
		if (count($Form->Controls) > 0)
		{

			//Begin with a true value, and set it to false if
			//any validation fails.
			$returnValue = true;

			//Validate each control
			foreach ($Form->Controls as $tempControl)
			{
				//Attemt the validation
				$success = $tempControl->Validate();

				//Were we successful?
				if ($success == false)
				{
					$returnValue = false;
				}
			}
		}
		else
		{
			//There are no controls, so default to success
			$returnValue = true;
		}

		return $returnValue;
	}
}

?>