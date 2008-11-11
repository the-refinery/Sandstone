<?php
/*
Message Comment Class

@package Sandstone
@subpackage Message
*/

NameSpace::Using("Sandstone.Database");

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

	/*
	CommentID property

	@return int
	*/
	public function getCommentID()
	{
		return $this->_commentID;
	}

	/*
	MessageID property

	@return int
	*/
	public function getMessageID()
	{
		return $this->_messageID;
	}

	/*
	User property

	@return User
	@param User $Value
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

	/*
	Timestamp property

	@return Date
	*/
	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	/*
	Content property

	@return string
	@param string $Value
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
        $query = new Query();

        $selectClause = self::GenerateBaseSelectClause();
        $fromClause = self::GenerateBaseFromClause();
        $whereClause = "WHERE     CommentID = {$ID} ";

        $query->SQL = $selectClause . $fromClause . $whereClause;

        $query->Execute();

        $returnValue = $query->LoadEntity($this);

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

		$query = new Query();

		$accountID = Application::License()->AccountID;

		$query->SQL = "	INSERT INTO core_MessageCommentMaster
						(
							AccountID,
							MessageID,
							UserID,
							Timestamp,
							Content
						)
						VALUES
						(
							{$accountID},
							{$this->_messageID},
							{$this->_user->UserID},
							NOW(),
							{$query->SetTextField($this->_content)}
						)";

		$query->Execute();


		//Get the new ID
		$query->SQL = "SELECT LAST_INSERT_ID() newID ";

		$query->Execute();

		$this->_commentID = $query->SingleRowResult['newID'];

		$returnValue = $this->RefreshTimestamp();

		return $returnValue;

	}

	protected function SaveUpdateRecord()
	{

		$query = new Query();

		$query->SQL = "	UPDATE core_MessageCommentMaster SET
							UserID = {$this->_user->UserID},
							Content = {$query->SetTextField($this->_content)}
						WHERE CommentID = {$this->_commentID}";

		$query->Execute();

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
		$query = new Query();

		$query->SQL = "	SELECT	Timestamp
						FROM    core_MessageCommentMaster
						WHERE	CommentID = {$this->_commentID} ";

		$query->Execute();

        if ($query->SelectedRows > 0)
        {
            $this->_timestamp = new Date($query->SingleRowResult['Timestamp']);
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
		$query = new Query();

		$query->SQL = "	DELETE
					    FROM    core_MessageCommentMaster
					    WHERE CommentID = {$this->_commentID} ";

		$query->Execute();

		$this->_commentID = null;

		return true;
	}

    /*
    Static Query Functions
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
