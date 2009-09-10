<?php
/*
Sortable Entity Base Class File

@package Sandstone
@subpackage EntityBase
*/

class SortableEntityBase extends EntityBase
{

	protected $_sortOrderPropertyName;
	protected $_sortOrder;

	/*
	SortOrder property

	@return integer
	@param integer $Value
	 */
	public function getSortOrder()
	{
		if (is_set($this->_sortOrderPropertyName))
		{
            $propertyName = $this->_sortOrderPropertyName;
			$returnValue = $this->$propertyName;
		}
		else
		{
			$returnValue = $this->_sortOrder;
		}

		return $returnValue;
	}

	public function setSortOrder($Value)
	{

		if (is_set($this->_sortOrderPropertyName))
		{
			$propertyName = $this->_sortOrderPropertyName;
			$this->$propertyName = $Value;
		}
		else
		{
			$this->_sortOrder = $Value;
		}
	}
}
?>