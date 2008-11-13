<?php
/*
SearchProperty Class File
@package Sandstone
@subpackage Search
*/

class SearchProperty extends EntityBase
{
	public function __construct($ID = null)
	{
		$this->_isTagsDisabled = true;
		$this->_isMessagesDisabled = true;

		parent::__construct($ID);
	}

	protected function SetupProperties()
	{
		$this->AddProperty("TopLevelNamespace","string","TopLevelNamespace",true,false,false,false,false,null);
		$this->AddProperty("ClassName","string","ClassName",true,false,false,false,false,null);
		$this->AddProperty("PropertyName","string","PropertyName",true,false,false,false,false,null);
		$this->AddProperty("MatchWeight","integer","MatchWeight",true,false,false,false,false,null);
		$this->AddProperty("WildcardWeight","integer","WildcardWeight",true,false,false,false,false,null);
		$this->AddProperty("IsUsedInCombinedSearch","boolean","IsUsedInCombinedSearch",true,false,false,false,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		return false;
	}

	protected function SaveUpdateRecord()
	{
		return false;
	}

	/*
	Static Query Functions
	*/
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.TopLevelNamespace,
										a.ClassName,
										a.PropertyName,
										a.MatchWeight,
										a.WildcardWeight,
										a.IsUsedInCombinedSearch ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_SearchEntityProperty a ";

		return $returnValue;
	}

}
?>