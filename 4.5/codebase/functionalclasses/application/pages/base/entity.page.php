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

	protected function SetupIndex($EventParameters)
	{
		$this->_lookupType = 'All';
		$this->_lookupParameters = array();
		$this->_resultsPerPage = 10;

		$this->LoadBlankEntity();
		$this->_template->Entity = $this->_loadedEntity;
	}

	/** For Overwriting in Specific Page **/

	protected function SetupShow($EventParameters) 
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

	protected function SetupEdit($EventParameters) 
	{
		$this->LoadEntity($EventParameters);
		$this->Throw404IfInvalidEntity($EventParameters);

		$this->_template->Entity = $this->_loadedEntity;
	}

	protected function BuildControlArray($EventParameters)
	{
		$restSetupMethod = 'Build' . $this->_action . 'Controls';
		$this->$restSetupMethod($EventParameters);

		parent::BuildControlArray($EventParameters);
	}

	protected function BuildIndexControls($EventParameters)
	{
    $pageNumber = $this->DeterminePageNumber($EventParameters['pagenumber']);
		$data = Lookup($this->_entityType, $this->_lookupType, $this->_lookupParameters, $this->_resultsPerPage, $pageNumber);

		$this->EntityList = new RepeaterControl();
		$this->EntityList->Data = $data;
		$this->EntityList->SetCallback($this, "EntityListCallback");

		$this->Paginate = new DataNavigationControl();
		$this->Paginate->RoutingRuleName = "{$this->_entityType}{$this->_action}";
		$this->Paginate->Lookup($this->_entityType, $this->_lookupType, $this->_lookupParameters, $this->_resultsPerPage, $pageNumber);
  }

  public function EntityListCallback($CurrentElement, $Template) {}

	protected function BuildShowControls($EventParameters) {}
	
	protected function BuildEditControls($EventParameters) 
	{
		$this->EditEntityForm = new PageForm($EventParameters);

		$this->EditEntityForm->EntityObject = $this->_loadedEntity;
		$this->EditEntityForm->EntitySaveSuccessNotification = "Saved Successfully";
		$this->EditEntityForm->EntitySaveFailureNotification = "Was NOT Saved Succesfully";
		$this->EditEntityForm->EntitySaveSuccessRoutingAction = 'show';

		$this->EditEntityForm->Submit = new SubmitButtonControl();
		$this->EditEntityForm->Submit->LabelText = "Save";
	}

	protected function BuildNewControls($EventParameters) 
	{
		$this->NewEntityForm = new PageForm($EventParameters);
		$this->NewEntityForm->EntityObject = $this->_loadedEntity;
		$this->NewEntityForm->EntitySaveSuccessNotification = "Saved Successfully";
		$this->NewEntityForm->EntitySaveFailureNotification = "Was NOT Saved Succesfully";
		$this->NewEntityForm->EntitySaveSuccessRoutingAction = 'show';

		$this->NewEntityForm->Submit = new SubmitButtonControl();
		$this->NewEntityForm->Submit->LabelText = "Save";
	}

	/** GENERIC - Not for overwriting **/

	protected function LoadEntity($EventParameters)
	{
		$className = $this->_entityType;
		$id = $EventParameters[$this->_primaryKeyField];

		$this->_loadedEntity = new $className($id);
	}

	protected function LoadBlankEntity()
	{
		$class = $this->_entityType;
		$this->_loadedEntity = new $class();
	}

	protected function Throw404IfInvalidEntity($EventParameters)
	{
		if ($this->_loadedEntity->IsLoaded == false)
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
