<?php
/**
 * ActionHistoryDetail Class File
 * @package Sandstone
 * @subpackage Action
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class ActionHistoryDetail extends EntityBase
{
	protected function SetupProperties()
	{
		$this->AddProperty("Action","Action",null,false,false,false,false,false,null);
		$this->AddProperty("Timestamp","date","Timestamp",true,false,false,false,false,null);
		$this->AddProperty("AssociatedEntityID","integer","AssociatedEntityID",true,false,false,false,false,null);
		$this->AddProperty("Details","string","Details",true,false,false,false,false,null);
		$this->AddProperty("User","User","UserID",true,false,false,false,false,null);
		$this->AddProperty("AssociatedPageName","string","AssociatedPageName",true,false,false,false,false,null);
		$this->AddProperty("AssociatedEntityType","string","AssociatedEntityType",true,false,false,false,false,null);

		parent::SetupProperties();
	}


	/**
	 * AssociatedPageName property
	 *
	 * @return string
	 */
	public function getAssociatedPageName()
	{
		if (is_set($this->_associatedPageName))
		{
			$returnValue = $this->_associatedPageName;
		}
		else
		{
			$returnValue = $this->_action->AssociatedPageName;
		}

		return $returnValue;
	}

	/**
	 * AssociatedEntityType property
	 *
	 * @return string
	 */
	public function getAssociatedEntityType()
	{

		if (is_set($this->_associatedEntityType))
		{
			$returnValue = $this->_associatedEntityType;
		}
		else
		{
			$returnValue = $this->_action->AssociatedEntityType;
		}

		return $returnValue;
	}

	public function Save()
	{
		//We don't allow any maintenance of these records from the applications
		return false;
	}

	/**
	 *
	 * Static Query Functions
	 *
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT		a.ActionID,
										a.Timestamp,
										a.AssociatedEntityID,
										a.Details,
										a.UserID,
										a.AssociatedPageName,
										a.AssociatedEntityType ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_ActionHistory a ";

		return $returnValue;
	}

}
?>