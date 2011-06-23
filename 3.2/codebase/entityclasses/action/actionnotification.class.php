<?php
/*
ActionEmailNotification Class File

@package Sandstone
@subpackage Action
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

class ActionNotification extends EntityBase
{

	public function __construct($ID = null)
	{
		//We only want to call the parent if we are loading from a DataRow
		if (is_array($ID))
		{
			parent::__construct($ID);
		}
		else
		{
			parent::__construct();

			if (is_set($ID))
			{
				$this->_actionID = $ID;
				$this->_associatedEntityID = 0;
			}
		}

	}

	protected function SetupProperties()
	{
		$this->AddProperty("ActionID","integer","ActionID",true,false,false,false,false,null);
		$this->AddProperty("AssociatedEntityID","integer","AssociatedEntityID",false,true,true,false,false,null);
		$this->AddProperty("Emails","array",null,true,false,false,false,true,"LoadEmails");

		parent::SetupProperties();
	}

	public function Load($dr)
	{

		$returnValue = parent::Load($dr);

		if ($returnValue == true)
		{
			$returnValue = $this->AddEmail($dr['EmailID']);
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		foreach ($this->_emails as $tempEmail)
		{
			$query = "	INSERT INTO core_ActionNotification
								(
									AccountID,
									ActionID,
									EmailID,
									AssociatedEntityID
								)
								VALUES
								(
									{$this->AccountID},
									{$this->_actionID},
									{$tempEmail->EmailID},
									{$this->_associatedEntityID}
								)";

			$conn->Execute($query);
		}

		return true;
	}

	protected function SaveUpdateRecord()
	{

		//Since we are always inserting on a save, just call the new
		$this->SaveNewRecord();

		return true;
	}

	public function AddEmail($EmailID)
	{

		if ($EmailID > 0)
		{
			$tempEmail = new Email($EmailID);

			if ($tempEmail->IsLoaded)
			{
				$this->_emails[$EmailID] = $tempEmail;

				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function RemoveEmail($EmailID)
	{
		if ($EmailID > 0)
		{
			unset($this->_emails[$EmailID]);

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	/*
	Static Query Functions
	*/
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.ActionID,
										a.EmailID,
										a.AssociatedEntityID ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_ActionNotification a ";

		return $returnValue;
	}

}
?>