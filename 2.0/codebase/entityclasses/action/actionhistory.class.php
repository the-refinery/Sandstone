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

class ActionHistory extends Module
{

	protected $_associatedPageName;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_limit;

	protected $_historyDetail;
	protected $_actions;

	public function __construct($AssociatedPageName = null, $AssociatedEntityType = null, $AssociatedEntityID = null, $Limit = null)
	{

		$this->_historyDetail = new DIarray();
		$this->_actions = new DIarray();

		$this->_associatedPageName = $AssociatedPageName;
		$this->_associatedEntityType = $AssociatedEntityType;
		$this->_associatedEntityID = $AssociatedEntityID;
		$this->_limit = $Limit;

		if (is_set($AssociatedPageName) || is_set($AssociatedEntityType) || is_set($Limit))
		{
			$this->Load();
		}

	}

	/**
	 * AssociatedPageName property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getAssociatedPageName()
	{
		return $this->_associatedPageName;
	}

	public function setAssociatedPageName($Value)
	{
		if ($this->_associatedPageName != $Value)
		{
			$this->_historyDetail = new DIarray();
		}

		$this->_associatedPageName = $Value;
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
		if ($this->_associatedEntityType != $Value)
		{
			$this->_historyDetail = new DIarray();
		}

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
		if ($this->_associatedEntityID != $Value)
		{
			$this->_historyDetail = new DIarray();
		}

		$this->_associatedEntityID = $Value;
	}

	/**
	 * Limit property
	 *
	 * @return integer
	 *
	 * @param integer $Value
	 */
	public function getLimit()
	{
		return $this->_limit;
	}

	public function setLimit($Value)
	{
		if ($this->_limit != $Value)
		{
			$this->_historyDetail = new DIarray();
		}

		$this->_limit = $Value;
	}

	/**
	 * HistoryDetail property
	 *
	 * @return DIarray
	 */
	public function getHistoryDetail()
	{
		if (count($this->_historyDetail) == 0)
		{
			$this->Load();
		}

		return $this->_historyDetail;
	}

	public function Load()
	{
		$this->_historyDetail = new DIarray();

		$conn = GetConnection();

		$selectClause = ActionHistoryDetail::GenerateBaseSelectClause();
		$fromClause = ActionHistoryDetail::GenerateBaseFromClause();
		$fromClause .= "INNER JOIN core_ActionMaster b on a.ActionID = b.ActionID ";

		if (is_set($this->_associatedPageName))
		{
			$searchText = strtolower($this->_associatedPageName);
			$whereClause = "WHERE LOWER(IF(a.AssociatedPageName IS NULL, b.AssociatedPageName, a.AssociatedPageName)) = '{$searchText}' ";
		}
		else if (is_set($this->_associatedEntityType))
		{
			$searchText = strtolower($this->_associatedEntityType);
			$whereClause = "WHERE LOWER(IF(a.AssociatedEntityType IS NULL, b.AssociatedEntityType, a.AssociatedEntityType)) = '{$searchText}' ";
		}

		if (is_set($this->_associatedEntityID) && strlen($whereClause) > 0)
		{
			$whereClause .= "AND a.AssociatedEntityID = {$this->_associatedEntityID} ";
		}

		$orderByClause = "ORDER BY a.Timestamp DESC ";

		if (is_set($this->_limit) && $this->_limit > 0)
		{
			$limitClause = "LIMIT {$this->_limit} ";
		}

		$query = $selectClause . $fromClause . $whereClause . $orderByClause . $limitClause;

		$ds = $conn->Execute($query);

		if ($ds)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempDetail = new ActionHistoryDetail($dr);

				if (array_key_exists($dr['ActionID'], $this->_actions) == false)
				{
					$tempAction = new Action($dr['ActionID']);
					$this->_actions[$tempAction->ActionID] = $tempAction;
				}

				$tempDetail->Action = $this->_actions[$dr['ActionID']];

				$this->_historyDetail[] = $tempDetail;
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

}
?>
