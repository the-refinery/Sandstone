<?php

class EntityPage extends ApplicationPage
{
	protected $_loadedEntity;
	protected $_entityType;
	protected $_primaryKeyField;
	protected $_action;

	// Index
	protected $_lookupType;
	protected $_lookupParameters;
	protected $_resultsPerPage;

	protected function Generic_PreProcessor(&$EventParameters)
	{
		$this->_entityType = $EventParameters['class'];
		$this->_primaryKeyField = $EventParameters['primarykey'];
		$this->_action = $EventParameters['restaction'];

		$this->_template->Filename = $this->LocalName . $this->_action;

		$setupMethod = 'setup' . $this->_action;
		$this->$setupMethod($EventParameters);

		parent::Generic_PreProcessor($EventParameters);
	}

	public function JSON_Processor($EventParameters)
	{
		switch($this->_action)
		{
			case 'show':
				$json = $this->API->PrimitiveToJSON($this->_loadedEntity);

				$this->_template->filename = 'entityshow';
				$this->_template->JSON = $json;
				break;

			case 'index':
				$this->_template->filename = 'entityindex';
				break;
		}
	}

	protected function SetupIndex($EventParameters)
	{
		$this->_lookupType = 'All';
		$this->_lookupParameters = array();
		$this->_resultsPerPage = 10;

		$this->LoadBlankEntity();
		$this->_template->Entity = $this->_loadedEntity;
	}

	/** For Overwriting in Specific Page **/

	protected function SetupShow(&$EventParameters) 
	{
		$this->LoadEntity($EventParameters);
		$this->Throw404IfInvalidEntity($EventParameters);

		$this->_template->Entity = $this->_loadedEntity;
	}

	protected function SetupNew($EventParameters) 
	{
		$this->LoadBlankEntity();

		$this->_template->Entity = $this->_loadedEntity;
	}

	protected function SetupEdit(&$EventParameters) 
	{
		$this->LoadEntity($EventParameters);
		$this->Throw404IfInvalidEntity($EventParameters);

		$this->_template->Entity = $this->_loadedEntity;
	}

	protected function BuildControlArray($EventParameters)
	{
		$restSetupMethod = 'Build' . $this->_action . 'Controls';
		$preSetupMethod = $restSetupMethod . "_Pre";
		$additionalSetupMethod = 'BuildAdditional' . $this->_action . 'Controls';

		$form = $this->$preSetupMethod($EventParameters);

		if (method_exists($this, $restSetupMethod))
		{
			$this->$restSetupMethod($form);
		}

		if (method_exists($this, $additionalSetupMethod))
		{
			$this->$additionalSetupMethod($EventParameters);
		}

		parent::BuildControlArray($EventParameters);
	}

	protected function BuildIndexControls_Pre($EventParameters)
	{
		$data = $this->LookupIndexData($EventParameters);

		$this->EntityList = new RepeaterControl();
		$this->EntityList->Data = $data;
		$this->EntityList->SetCallback($this, "EntityListCallback");

		return $this->EntityList;
  }

  protected function LookupIndexData($EventParameters)
  {
    return array();
  }

  public function EntityListCallback($CurrentElement, $Template) {}

	protected function BuildShowControls_Pre($EventParameters) 
	{
		$this->ShowEntityForm = new PageForm($EventParameters);

		$this->ShowEntityForm->EntityObject = $this->_loadedEntity;
		$this->ShowEntityForm->EntitySaveSuccessNotification = "Saved Successfully";
		$this->ShowEntityForm->EntitySaveFailureNotification = "Was NOT Saved Succesfully";
		$this->ShowEntityForm->EntitySaveSuccessRoutingAction = 'show';

		$this->ShowEntityForm->Submit = new SubmitButtonControl();
		$this->ShowEntityForm->Submit->LabelText = "Save";

		return $this->ShowEntityForm;
	}
	
	protected function BuildEditControls_Pre($EventParameters) 
	{
		$this->EditEntityForm = new PageForm($EventParameters);

		$this->EditEntityForm->EntityObject = $this->_loadedEntity;
		$this->EditEntityForm->EntitySaveSuccessNotification = "Saved Successfully";
		$this->EditEntityForm->EntitySaveFailureNotification = "Was NOT Saved Succesfully";
		$this->EditEntityForm->EntitySaveSuccessRoutingAction = 'show';

		$this->EditEntityForm->Submit = new SubmitButtonControl();
		$this->EditEntityForm->Submit->LabelText = "Save";

		return $this->EditEntityForm;
	}

	protected function BuildNewControls_Pre($EventParameters) 
	{
		$this->NewEntityForm = new PageForm($EventParameters);
		$this->NewEntityForm->EntityObject = $this->_loadedEntity;
		$this->NewEntityForm->EntitySaveSuccessNotification = "Saved Successfully";
		$this->NewEntityForm->EntitySaveFailureNotification = "Was NOT Saved Succesfully";
		$this->NewEntityForm->EntitySaveSuccessRoutingAction = 'show';

		$this->NewEntityForm->Submit = new SubmitButtonControl();
		$this->NewEntityForm->Submit->LabelText = "Create";

		return $this->NewEntityForm;
	}

	protected function LoadBlankEntity()
	{
		$class = $this->_entityType;
		$this->_loadedEntity = new $class();
	}

	/** GENERIC - Not for overwriting **/

	protected function LoadEntity($EventParameters)
	{
		$class = $this->_entityType;
		$primaryKey = $EventParameters[$this->_primaryKeyField];
		
		$loadIt = "\$this->_loadedEntity = \$this->API->Load{$class}({$primaryKey});";
		eval($loadIt);
	}

	protected function Throw404IfInvalidEntity(&$EventParameters)
	{
		if (is_null($this->_loadedEntity))
		{
			$this->_isOKtoLoadControls = false;
			$this->SetResponseCode(404, $EventParameters);
		}
	}

	public function DetermineEntityTemplateFilename()
	{
		$returnValue = 'entity' . $this->_action;

		return $returnValue;
	}

  protected function SetupNotificationMessage($IsSuccessful)
  {
    if ($IsSuccessful)
    {
      $this->SetNotificationMessage("Save was successful", "success");
    }
    else
    {
      $this->SetNotificationMessage("Save was NOT successful", "error");
    }
  }

  protected function DeterminePageNumber($PageNumber = null)
  {
    if ($PageNumber)
    {
      $returnValue = $PageNumber;
    }
    else
    {
      $returnValue = 1;
    }

    return $returnValue;
  }
}

?>
