<?php
/**
 * User Referral Class
 * 
 * @package AddOns
 * @subpackage User
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * @version 1.0
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class UserReferral
{
	private $_parentUser;
	private $_childUsers = array();
	
	public function __construct($id = null)
	{
		if (is_set($id))
		{
			if (is_array($id))
				$this->load($id);
			else
				$this->loadByID($id);
		}
	}
	
	/**
	 * ParentUser property
	 * 
	 * @return 
	 */
	public function getParentUser()
	{
		return $this->_parentUser;
	}
	
	/**
	 * ChildUsers property
	 * 
	 * @return array
	 */
	public function getChildUsers()
	{
		return $this->_childUsers;
	}
	
	public function load($data)
	{
		$this->_parentUser = new User($data['ParentUserID']);
	}
	
	public function loadByID($id)
	{
		$this->load($id);
	}
	
	public function loadChildren()
	{
		$conn = GetConnection();
		$query = "SELECT a.ChildUserID,
						b.UserID,
						b.FirstName,
						b.LastName,
						b.Gender,
						b.Username,
						b.Password,
						b.JoinDate  
			FROM addon_UserReferral a 
			INNER JOIN core_UserMaster b ON a.ChildUserID = b.UserID 
			WHERE a.ParentUserID = {$this->_parentUser->UserID}";
		$rs = $conn->Execute($query);
		while ($array = $rs->FetchRow()) {
		    $this->_childUsers[] = new User($array);
		}
	}
}

?>