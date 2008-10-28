<?php
/**
 * Message Comment Class
 *
 * @package Sandstone
 * @subpackage Message
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

NameSpace::Using("Sandstone.ADOdb");

class MessageComment extends Module
{

	protected $_commentID;
	protected $_messageID;
	protected $_user;
	protected $_timestamp;
	protected $_content;

    public function __construct($ID = null, $Message = null)
    {
        if (is_set($ID))
        {
            if (is_array($ID))
            {
                $this->Load($ID);
            }
            else
            {
                $this->LoadByID($ID);
            }
        }
        else
        {
            //This is a new Comment
            if ($Message instanceof Message && $Message->IsLoaded)
            {
            	$this->_messageID = $Message->MessageID;
            }

        }

    }

	/**
	 * CommentID property
	 *
	 * @return int
	 */
	public function getCommentID()
	{
		return $this->_commentID;
	}

	/**
	 * MessageID property
	 *
	 * @return int
	 */
	public function getMessageID()
	{
		return $this->_messageID;
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
		if ($Value instanceof User && $Value->IsLoaded)
		{
			$this->_user = $Value;
		}
		else
		{
			$this->_user = null;
		}

	}

	/**
	 * Timestamp property
	 *
	 * @return Date
	 */
	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	/**
	 * Content property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getContent()
	{
		return $this->_content;
	}

	public function setContent($Value)
	{
		$this->_content = $Value;
	}

    public function Load($dr)
    {

    	$this->_commentID = $dr['CommentID'];
		$this->_messageID = $dr['MessageID'];

		$this->_user = new User($dr['UserID']);
		$this->_timestamp = new Date($dr['Timestamp']);
		$this->_content = $dr['Content'];

        $this->_isLoaded = true;

        return true;
    }

    public function LoadByID($ID)
    {
        $conn = GetConnection();

        $selectClause = self::GenerateBaseSelectClause();
        $fromClause = self::GenerateBaseFromClause();
        $whereClause = "WHERE     CommentID = {$ID} ";

        $query = $selectClause . $fromClause . $whereClause;

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

	public function Save()
	{

		$isOkToSave = $this->ValidateRequiredProperties();

		if ($isOkToSave == true)
		{

			if (is_set($this->_commentID) OR $this->_commentID > 0)
			{
				$returnValue = $this->SaveUpdateRecord();
			}
			else
			{
				$returnValue = $this->SaveNewRecord();
			}
		}
		else
		{
			$returnValue = false;
		}

		$this->_isLoaded = $returnValue;

		return $returnValue;

	}

	protected function SaveNewRecord()
	{

		$conn = GetConnection();

		$query = "	INSERT INTO core_MessageCommentMaster
					(
						MessageID,
						UserID,
						Timestamp,
						Content
					)
					VALUES
					(
						{$this->_messageID},
						{$this->_user->UserID},
						NOW(),
						{$conn->SetTextField($this->_content)}
					)";

		$conn->Execute($query);


		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_commentID = $dr['newID'];

		$returnValue = $this->RefreshTimestamp();

		return $returnValue;

	}

	protected function SaveUpdateRecord()
	{

		$conn = GetConnection();

		$query = "	UPDATE core_MessageCommentMaster SET
						UserID = {$this->_user->UserID},
						Content = {$conn->SetTextField($this->_content)}
					WHERE CommentID = {$this->_commentID}";

		$conn->Execute($query);

		return true;
	}

	protected function ValidateRequiredProperties()
	{

		//Start as true, and set it to false if something is missing
		$returnValue = true;

		if (is_set($this->_messageID) == false)
		{
			$returnValue = false;
		}

		if (is_set($this->_user) == false)
		{
			$returnValue = false;
		}

		if (strlen($this->_content) == 0)
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function RefreshTimestamp()
	{
		$conn = GetConnection();

		$query = "	SELECT	Timestamp
					FROM    core_MessageCommentMaster
					WHERE	CommentID = {$this->_commentID} ";

		$ds = $conn->Execute($query);

        if ($ds && $ds->RecordCount() > 0)
        {
            $dr = $ds->FetchRow();
            $this->_timestamp = new Date($dr['Timestamp']);
            $returnValue = true;
        }
        else
        {
            $returnValue = false;
        }

        return $returnValue;

	}

	public function Delete()
	{
		$conn = GetConnection();

		$query = "	DELETE
				    FROM    core_MessageCommentMaster
				    WHERE CommentID = {$this->_commentID} ";

		$conn->Execute($query);

		$this->_commentID = null;

		return true;
	}

    /**
     *
     * Static Query Functions
     *
     */
    static public function GenerateBaseSelectClause()
    {
        $returnValue = "    SELECT  a.CommentID,
                                    a.MessageID,
                                    a.UserID,
                                    a.Timestamp,
                                    a.Content ";

        return $returnValue;

    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    core_MessageCommentMaster a ";

        return $returnValue;
    }

}
?>
