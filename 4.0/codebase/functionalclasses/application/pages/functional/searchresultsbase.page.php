<?php

NameSpace::Using("Sandstone.Search");

class SearchResultsBasePage extends ApplicationPage
{
	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected $_search;

	protected $_searchTerm;
	protected $_types;

    protected function Generic_PreProcessor(&$EventParameters)
	{
		$valueName = strtolower($EventParameters['controlname'] . "_searchtext");
		$typesName = strtolower($EventParameters['controlname'] . "_searchtypes");

		$this->_searchTerm = $EventParameters[$valueName];
		$this->_types = explode(",", $EventParameters[$typesName]);

		unset($EventParameters[$valueName]);
		unset($EventParameters[$typesName]);
	}


	protected function HTM_Processor($EventParameters)
	{

		//Derive the search term

		if ($EventParameters[$valueName] <> $EventParameters['labeltext'])
		{
			$this->_template->SearchTerm = $this->_searchTerm;

			$this->_search = new Search();

			foreach ($this->_types as $tempType)
			{
				$this->_search->AddType($tempType);
			}

			$this->_search->Search($this->_searchTerm);

			$this->Results->Data = $this->_search->Results;
		}
	}

	protected function BuildControlArray($EventParameters)
	{

		$this->Results = new RepeaterControl();

		$this->Search = new SearchControl();
		foreach ($this->_types as $tempType)
		{
			$this->Search->AddType($tempType);
		}

   		parent::BuildControlArray($EventParameters);
	}

}
?>
