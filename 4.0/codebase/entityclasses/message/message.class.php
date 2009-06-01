<?php
/*
Message Class File

@package Sandstone
@subpackage Message
*/

NameSpace::Using("Sandstone.User");

class Message extends EntityBase
{
	public function __construct($ID = null)
	{

		$this->_isTagsDisabled = true;
		$this->_isMessagesDisabled = true;

		parent::__construct($ID);

	}

	protected function SetupProperties()
	{
		$this->AddProperty("MessageID","integer","MessageID",PROPERTY_PRIMARY_ID);
		$this->AddProperty("AssociatedEntityType","string","AssociatedEntityType",PROPERTY_REQUIRED);
		$this->AddProperty("AssociatedEntityID","integer","AssociatedEntityID",PROPERTY_REQUIRED);
		$this->AddProperty("User","User","UserID",PROPERTY_REQUIRED + PROPERTY_LOADED_REQUIRED);
		$this->AddProperty("Timestamp","date","Timestamp",PROPERTY_READ_ONLY);
		$this->AddProperty("Subject","string","Subject",PROPERTY_REQUIRED);
		$this->AddProperty("Content","string","Content",PROPERTY_REQUIRED);
		$this->AddProperty("IsEmailOnComment","boolean","IsEmailOnComment",PROPERTY_READ_WRITE);
		$this->AddProperty("Comments","array",null,PROPERTY_READ_ONLY,"LoadComments");
		$this->AddProperty("LatestComment","MessageComment",null,PROPERTY_READ_ONLY,"LoadComments");

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$this->_timestamp = new Date();

		$query->SQL = "	INSERT INTO core_MessageMaster
							(
								AccountID,
								AssociatedEntityType,
								AssociatedEntityID,
								UserID,
								Timestamp,
								Subject,
								Content,
								IsEmailOnComment
							)
							VALUES
							(
								{$this->AccountID},
								{$query->SetTextField($this->_associatedEntityType)},
								{$this->_associatedEntityID},
								{$this->_user->UserID},
								{$query->SetNullDateField($this->_timestamp)},
								{$query->SetTextField($this->_subject)},
								{$query->SetTextField($this->_content)},
								{$query->SetBooleanField($this->_isEmailOnComment)}
							)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_MessageMaster SET
								UserID = {$this->_user},
								Subject = {$query->SetTextField($this->_subject)},
								Content = {$query->SetTextField($this->_content)},
								IsEmailOnComment = {$query->SetBooleanField($this->_isEmailOnComment)}
							WHERE MessageID = {$this->_messageID}";

		$query->Execute();

		return true;
	}

	public function LoadComments()
	{

		$this->_Comments->Clear();

		$query = new Query();

		$selectClause = MessageComment::GenerateBaseSelectClause();
		$fromClause = MessageComment::GenerateBaseFromClause();
		$whereClause = "WHERE a.MessageID = {$this->_MessageID} ";
		$orderByClause = "ORDER BY a.Timestamp ";

		$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause;

		$query->Execute();

		$query->LoadEntityArray($this->_Comments, "MessageComment", "CommentID", $this, "LoadCommentsCallback");

		return true;

	}

	public function LoadCommentsCallback($MessageComment)
	{

		$MessageComment->Message = $this;

		$this->_latestComment = $MessageComment;

		return $MessageComment;

	}

    public function AddComment($User, $Content)
    {
		$returnValue = false;

        if ($User instanceof User && $User->IsLoaded && strlen($Content) > 0)
        {
            $newComment = new MessageComment();
            $newComment->Message = $this;
            $newComment->User = $User;
            $newComment->Content = $Content;

            $success = $newComment->Save();

            if ($success == true)
            {
				$returnValue = $this->LoadComments();

				//A new comment flags as unread for everybody.
				$this->MarkUnread();
				
				//Do we need to send a notification?
				if ($this->_isEmailOnComment)
				{
					$this->SendNotificationEmail();
				}
			}
        }

        return $returnValue;
    }

	protected function SendNotificationEmail()
	{
		

		
	}

	public function RemoveComment($Comment)
	{

		$returnValue = false;

		if ($Comment instanceof MessageComment && $Comment->IsLoaded)
		{
			if (array_key_exists($Comment->CommentID, $this->Comments))
			{
				$commentID = $Comment->CommentID;

				$this->_comments[$commentID]->Delete();

				$returnValue = $this->LoadComments();
			}
		}

		return $returnValue;
	}

	public function Delete()
	{

		//First delete any comments
		$query = new Query();
		
		$query->SQL = "	DELETE
						FROM	core_MessageCommentMaster
						WHERE MessageID = {$this->_messageID} ";
						
		$query->Execute();						

		//Remove any read flags
		$this->MarkUnread();

		//Now delete this record.
		$query->SQL = "	DELETE
					    FROM    core_MessageMaster
					    WHERE MessageID = {$this->_messageID} ";

		$query->Execute();

		return true;
	}

	public function MarkRead($User)
	{

		$returnValue = false;

		if ($User instanceof User && $User->IsLoaded)
		{

			//Make sure it's not currently marked as read
			$this->MarkUnread($User);

			$query = new Query();

			$query->SQL = "	INSERT INTO core_UserReadMessages
							(
								UserID,
								MessageID
							)
							VALUES
							(
								{$User->UserID},
								{$this->_messageID}
							)";

			$query->Execute();

			$returnValue = true;
		}

		return $returnValue;
	}

	public function MarkUnread($User=null)
	{
		$query = new Query();

		$query->SQL = "	DELETE
						FROM	core_UserReadMessages
						WHERE	MessageID = {$this->_messageID} ";

		if ($User instanceof User && $User->IsLoaded)
		{
			$query->SQL .= " AND UserID = {$User->UserID} ";
		}


		$query->Execute();

		return true;
	}

	public function CheckReadStatus($User = null)
	{
		$returnValue = false;

		if (is_set($User) == false)
		{
			$User = Application::CurrentUser();
		}

		if ($User instanceof User && $User->IsLoaded)
		{
			$query = new Query();

			$query->SQL = "	SELECT	MessageID
                            FROM	core_UserReadMessages
							WHERE	MessageID = {$this->_messageID}
							AND		UserID = {$User->UserID} ";

			$query->Execute();

			if ($query->SelectedRows > 0)
			{
				$returnValue = true;
			}
		}

		return $returnValue;
	}

	public function CountSearchTermOccurrances($SearchTerm)
	{

		//First check my Subject
		$returnValue = substr_count($this->_subject, $SearchTerm);

		//Next my content
		$returnValue += substr_count($this->_content, $SearchTerm);

		//Now the content of any comments
		if (count($this->Comments) > 0)
		{
			foreach ($this->_comments as $tempComment)
			{
				$returnValue += substr_count($tempComment->Content, $SearchTerm);
			}
		}

		return $returnValue;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.MessageID,
										a.AssociatedEntityType,
										a.AssociatedEntityID,
										a.UserID,
										a.Timestamp,
										a.Subject,
										a.Content,
										a.IsEmailOnComment ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_MessageMaster a ";

		return $returnValue;
	}

}
?>
