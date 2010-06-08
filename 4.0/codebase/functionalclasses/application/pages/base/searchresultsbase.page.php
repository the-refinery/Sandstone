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

    $EventParameters['formname'] = 'searchform';

    $this->_searchTerm = $EventParameters[$valueName];
    $this->_types = explode(",", $EventParameters[$typesName]);

    unset($EventParameters[$valueName]);
    unset($EventParameters[$typesName]);

    parent::Generic_PreProcessor($EventParameters);
  }

  protected function HTM_Processor($EventParameters)
  {
    if ($this->_searchTerm <> $EventParameters['labeltext'])
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

  protected function SearchForm_Processor($EventParameters)
  {
    return false;
  }

  protected function BuildControlArray($EventParameters)
  {
    $this->SearchForm = new PageForm($EventParameters);

    $this->Results = new RepeaterControl();

    $this->Search = new SearchControl();
    $this->Search->SearchText->DefaultValue = $this->_searchTerm;

    foreach ($this->_types as $tempType)
    {
      $this->Search->AddType($tempType);
    }

    parent::BuildControlArray($EventParameters);
  }
}
