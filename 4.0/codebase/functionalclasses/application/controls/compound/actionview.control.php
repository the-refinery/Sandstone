<?php
/*
Action View Control Class File

@package Sandstone
@subpackage Application
*/

Namespace::Using("Sandstone.Database");

class ActionViewControl extends BaseControl
{

	protected $_actions;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_daysOfHistory;

    public function __construct()
	{

		parent::__construct();

		$this->_controlStyle->AddClass('actionview_general');
		$this->_bodyStyle->AddClass('actionview_body');

		//Show the last week as a default
		$this->_daysOfHistory = 7;
		$this->ActionList->Template->NoActionsText = "No Recent Activity";

		$this->_actions = Array();

		$this->_isRawValuePosted = false;
	}

	/*
	AssociatedEntityType property

	@return string
	@param string $Value
	*/
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}

	public function setAssociatedEntityType($Value)
	{
		$this->_associatedEntityType = $Value;
	}

	/*
	AssociatedEntityID property

	@return integer
	@param integer $Value
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

	/*
	DaysOfHistory property

	@return integer
	@param integer $Value
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

	/*
	NoActionText property

	@return string
	@param string $Value
	 */
	public function getNoActionText()
	{
		return $this->ActionList->Template->NoActionsText;
	}

	public function setNoActionText($Value)
	{
		$this->ActionList->Template->NoActionsText = $Value;
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

	protected function SetupControls()
	{
		parent::SetupControls();

		$this->ActionList = new RepeaterControl();
		$this->ActionList->SetCallback($this, "ActionCallBack");

	}

	protected function LoadHistory()
	{
		$query = new Query();

		$query->SQL = $this->BuildHistorySQL($query);

		$query->Execute();

		return $query->Results;

	}

	protected function BuildHistorySQL($Query)
	{

		$now = new Date();

		$startDate = $now->SubtractDays($this->_daysOfHistory);

		$returnValue = "  SELECT	a.Timestamp,
							a.AssociatedEntityID,
							a.Details,
							a.UserID,
							IF(a.AssociatedEntityType IS NULL, b.AssociatedEntityType, a.AssociatedEntityType) AssociatedEntityType,
							IF(a.RoutingAction IS NULL, b.RoutingAction, a.RoutingAction) RoutingAction
					FROM	core_ActionHistory a
							INNER JOIN core_ActionMaster b ON b.ActionID = a.ActionID
					WHERE	a.Timestamp >= {$Query->SetDateField($startDate)} ";

		if (is_set($this->_associatedEntityType))
		{
			$entityType = strtolower($this->_associatedEntityType);
			$returnValue .= "AND	IF(a.AssociatedEntityType IS NULL, LOWER(b.AssociatedEntityType), LOWER(a.AssociatedEntityType)) = {$Query->SetTextField($entityType)} ";

			if (is_set($this->_associatedEntityID))
			{
				$returnValue .= "AND	a.AssociatedEntityID = {$this->_associatedEntityID} ";
			}

		}

		if (count($this->_actions) > 0)
		{
			$actionIDs = implode(",", array_keys($this->_actions));

			$returnValue .= "AND a.ActionID IN ({$actionIDs}) ";
		}

		$returnValue .= "ORDER BY a.Timestamp DESC ";

		return $returnValue;
	}

	public function ActionCallBack($CurrentElement, $Template)
	{
		$Template->FileName = "actionlist_item";

		$timestamp = new date($CurrentElement->Timestamp);
		$Template->ActionDate =  $timestamp->FriendlyDate;

		$entityType = $CurrentElement->AssociatedEntityType;

		$entity = new $entityType ($CurrentElement->AssociatedEntityID);

		$Template->ActionURL = Routing::BuildURLbyEntity($entity, $CurrentElement->RoutingAction);
	}

	public function Render()
	{

		$this->ActionList->Data = $this->LoadHistory();

		$returnValue = parent::Render();

		return $returnValue;
	}

}
?>