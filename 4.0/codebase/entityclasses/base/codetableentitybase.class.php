<?php
/*
CodeTables Entity Base Class File

@package Sandstone
@subpackage EntityBase
*/

SandstoneNamespace::Using("Sandstone.Tag");
SandstoneNamespace::Using("Sandstone.Message");

class CodeTableEntityBase extends EntityBase
{

	public function __construct($ID = null)
	{
    	$this->_isTagsDisabled = true;
		$this->_isMessagesDisabled = true;

		parent::__construct($ID);

	}

    public function LoadByID($ID)
    {

        //Build the Select and from clause
        //We have to do it this way so we call the correct static functions
		$currentClassName = get_class($this);

		$cmd = "	\$selectClause = {$currentClassName}::GenerateBaseSelectClause();
					\$fromClause = {$currentClassName}::GenerateBaseFromClause();
					\$whereClause = {$currentClassName}::GenerateBaseWhereClause();";

		eval($cmd);

		if (strlen($whereClause) == 0)
		{
			$whereClause = "WHERE ";
		}
		else
		{
			$whereClause .= "AND ";
		}

		if ($this->_primaryIDproperty->DataType == "integer" || $this->_primaryIDproperty->DataType == "int")
		{
			$whereClause .= "a.{$this->_primaryIDproperty->DBfieldName} = {$ID} ";
		}
		else
		{
			$whereClause .= "a.{$this->_primaryIDproperty->DBfieldName} = '{$ID}' ";
		}

		$query = $this->SetupQuery();

		$query->SQL = $selectClause . $fromClause . $whereClause;

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

        return $returnValue;

    }

    public function SetupQuery()
    {
       	$returnValue = new Query("LocationDB");

        return $returnValue;
    }


    public function Save()
    {
    	//We don't save to the location db from the classes
        return false;
    }

}
?>