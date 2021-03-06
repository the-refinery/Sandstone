<?php
/**
 * ActionLogging Class File
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

class ActionLogging extends EntityBase
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

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_ActionLogging
					(
						ActionID,
						AssociatedEntityID
					)
					VALUES
					(
						{$this->_actionID},
						{$this->_associatedEntityID}
					)";

		$conn->Execute($query);

		return true;
	}

	protected function SaveUpdateRecord()
	{

		//Since we are always inserting on a save, just call the new
		$this->SaveNewRecord();

		return true;
	}

	/**
	 *
	 * Static Query Functions
	 *
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.ActionID,
										a.AssociatedEntityID ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_ActionLogging a ";

		return $returnValue;
	}

}
?>