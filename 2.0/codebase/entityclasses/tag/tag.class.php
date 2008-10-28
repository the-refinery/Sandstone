<?php
/**
 * Tag Class File
 * @package Sandstone
 * @subpackage Tag
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */
 
class Tag extends Module
{

	protected $_tagID;
	protected $_text;
	protected $_user;
	protected $_addTimestamp;

    public function __construct($dr = null)
    {
        if (is_set($dr))
        {
            if (is_array($dr))
            {
                $this->Load($dr);
            }
        }
    }

	/**
	 * TagID property
	 *
	 * @return int
	 */
	public function getTagID()
	{
		return $this->_tagID;
	}

	/**
	 * Text property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getText()
	{
		return $this->_text;
	}

	public function setText($Value)
	{
		$this->_text = $Value;

		$this->LookupIDfromText();
	}

	/**
	 * User property
	 *
	 * @return User
	 *
	 * @param User $Value
	 */
	public function getUser()
	{
		return $this->_user;
	}

	public function setUser($Value)
	{
		$this->_user = $Value;
	}

	/**
	 * Addtimestamp property
	 *
	 * @return date
	 */
	public function getAddTimestamp()
	{
		return $this->_addTimestamp;
	}

	public function setAddTimestamp($Value)
	{
		if ($Value instanceof Date)
		{
			$this->_addTimestamp = $Value;
		}
		else
		{
			$this->_addTimestamp = null;
		}
	}

	public function Load($dr)
	{
		$this->_tagID = $dr['TagID'];
		$this->_text = $dr['TagText'];
		$this->_user = new User($dr['UserID']);
		$this->_addTimestamp = new Date($dr['AddTimestamp']);

		$this->_isLoaded = true;

		return true;
	}

	public function LoadByID($ID)
	{
		$conn = GetConnection();

		$query = "	SELECT	TagID,
							TagText
					FROM 	core_TagMaster
					WHERE	TagID = {$ID}";

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();

			$returnValue = $this->Load($dr);
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function LookupIDfromText()
	{

		if (strlen($this->_text) > 0)
		{
			$conn = GetConnection();

			$query = "	SELECT	TagID
						FROM 	core_TagMaster
						WHERE	TagText = {$conn->SetTextField($this->_text)}";

			$ds = $conn->Execute($query);

			if ($ds && $ds->RecordCount() > 0)
			{
				$dr = $ds->FetchRow();

				$this->_tagID = $dr['TagID'];
				$this->_isLoaded = true;
			}
			else
			{
            	$this->_isLoaded = false;
				$this->_tagID = null;
			}
		}
		else
		{
			$this->_isLoaded = false;
			$this->_tagID = null;
		}

	}

	public function Save()
	{
		//We only are saving the TagMaster Record here, so we only
		//need to deal with new records.
		if ($this->_isLoaded == false)
		{
			$conn = GetConnection();

			$query = "	INSERT INTO core_TagMaster
						(
							 TagText
						)
						VALUES
						(
							{$conn->SetTextField($this->_text)}
						)";

			$conn->Execute($query);

			//Get the new ID
			$query = "SELECT LAST_INSERT_ID() newID ";

			$dr = $conn->GetRow($query);

			$this->_tagID = $dr['newID'];

			$this->_isLoaded = true;

			$returnValue = true;
		}
		else
		{
			$returnValue = true;
		}

		return $returnValue;
	}

}
?>
