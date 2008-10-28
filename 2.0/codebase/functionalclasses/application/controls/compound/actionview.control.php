<?php
/**
 * Action View Control Class File
 * @package Sandstone
 * @subpackage Application
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

class ActionViewControl extends StaticBaseControl
{

	protected $_actions;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_daysOfHistory;

    public function __construct()
	{
		parent::__construct();

		//Show the last week as a default
		$this->_daysOfHistory = 7;

		$this->_actions = Array();

		$this->_isRawValuePosted = false;
	}

	/**
	 * AssociatedEntityType property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}

	public function setAssociatedEntityType($Value)
	{
		$this->_associatedEntityType = $Value;
	}

	/**
	 * AssociatedEntityID property
	 *
	 * @return integer
	 *
	 * @param integer $Value
	 */
	public function getAssociatedEntityID()
	{
		return $this->_associatedEntityID;
	}

	public function setAssociatedEntityID($Value)
	{
		if (is_numeric($Value) && $Value > 0)
		{
			$this->_associatedEntityID = $Value;
		}
		else
		{
			$this->_associatedEntityID = null;
		}

	}

	/**
	 * DaysOfHistory property
	 *
	 * @return integer
	 *
	 * @param integer $Value
	 */
	public function getDaysOfHistory()
	{
		return $this->_daysOfHistory;
	}

	public function setDaysOfHistory($Value)
	{
		if (is_numeric($Value) && $Value > 0)
		{
			$this->_daysOfHistory = $Value;
		}
		else
		{
			$this->_daysOfHistory = 7;
		}

	}

	public function AddAction($ActionName)
	{
		$targetAction = Action::LookupActionByName($ActionName);

		if (is_set($targetAction))
		{
			$this->_actions[$targetAction->ActionID] = $targetAction;
		}
	}

	public function RemoveAction($ActionName)
	{
		$targetAction = Action::LookupActionByName($ActionName);

		if (is_set($targetAction))
		{
			unset($this->_actions[$targetAction->ActionID]);
		}
	}

	protected function RenderControlBody()
	{

		$this->LoadHistory();

		$returnValue = $this->List->__toString();

		return $returnValue;
	}

	protected function SetupControls()
	{
		parent::SetupControls();

		$this->List = new ULcontrol();
		$this->List->BodyStyle->AddClass("actionview_body");
	}

	protected function LoadHistory()
	{
		$conn = GetConnection();

		$query = $this->BuildHistorySQL($conn);

		$ds = $conn->Execute($query);

        if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$i++;
				$itemID = "History{$i}";
				
				$innerHTML = $this->BuildItemInnerHTML($dr);
				
				$this->List->AddItem($itemID, $innerHTML);
			}
		}
		else
		{
			//No Records Found
            $this->List->NoneFound = new ListItemControl();
			$this->List->NoneFound->InnerHTML = "No Recent Actions";
		}

		return $returnValue;
	}

	protected function BuildItemInnerHTML($dr)
	{
		$timestamp = new date($dr['Timestamp']);
		$returnValue = "<a href='{$dr['AssociatedPageName']}/{$dr['AssociatedEntityID']}'>{$timestamp->FriendlyDate}, {$dr['Details']}</a>";
		
		return $returnValue;
	}
	
	protected function BuildHistorySQL($conn)
	{

		$now = new Date();

		$startDate = $now->SubtractDays($this->_daysOfHistory);

		$returnValue = "  SELECT	a.Timestamp,
							a.AssociatedEntityID,
							a.Details,
							a.UserID,
							IF(a.AssociatedPageName IS NULL, b.AssociatedPageName, a.AssociatedPageName) AssociatedPageName
					FROM	core_ActionHistory a
							INNER JOIN core_ActionMaster b ON b.ActionID = a.ActionID
					WHERE	a.Timestamp >= {$conn->SetDateField($startDate)} ";

		if (is_set($this->_associatedEntityType))
		{
			$entityType = strtolower($this->_associatedEntityType);
			$returnValue .= "AND	IF(a.AssociatedEntityType IS NULL, LOWER(b.AssociatedEntityType), LOWER(a.AssociatedEntityType)) = {$conn->SetTextField($entityType)} ";
		}

		if (count($this->_actions) > 0)
		{
			$actionIDs = implode(",", array_keys($this->_actions));

			$returnValue .= "AND a.ActionID IN ({$actionIDs}) ";
		}

		if (is_set($this->_associatedEntityType) || count($this->_actions) > 0)
		{
			if (is_set($this->_associatedEntityID))
			{
				$returnValue .= "AND	a.AssociatedEntityID = {$this->_associatedEntityID} ";
			}
		}

		$returnValue .= "ORDER BY a.Timestamp DESC ";

		return $returnValue;
	}

}
?>