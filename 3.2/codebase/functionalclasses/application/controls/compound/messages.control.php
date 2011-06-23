<?php
/*
Messages Control Class File

@package Sandstone
@subpackage Application
*/

SandstoneNamespace::Using("Sandstone.Action");
SandstoneNamespace::Using("Sandstone.Message");
SandstoneNamespace::Using("Sandstone.Markdown");

class MessagesControl extends BaseControl
{

	protected $_messages;
	protected $_isReadOnly;
	protected $_isAdminUser;

	protected $_actionLogAssociatedEntityType;
	protected $_actionLogAssociatedEntityID;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('messages_general');
		$this->_bodyStyle->AddClass('messages_body');

        $this->_isTopLevelControl = true;
        $this->_isRawValuePosted = false;

        $this->_template->FileName = "messages";
	}

	/*
	Messages property

	@return Messages
	@param Messages $Value
	*/
	public function getMessages()
	{
		return $this->_messages;
	}

	public function setMessages($Value)
	{
		if ($Value instanceof Messages)
		{
			$this->_messages = $Value;
			$this->MessagesSummary->Data = $Value->Messages;
            $this->_actionLogAssociatedEntityType= $this->_messages->AssociatedEntityType;
            $this->_actionLogAssociatedEntityID = $this->_messages->AssociatedEntityID;
        }
		else
		{
			$this->_messages = null;
			$this->MessagesSummary->Data = null;
            $this->_actionLogAssociatedEntityType = null;
            $this->_actionLogAssociatedEntityID = null;
        }
	}

	/*
	IsReadOnly property

	@return boolean
	@param boolean $Value
	*/
	public function getIsReadOnly()
	{
		return $this->_isReadOnly;
	}

	public function setIsReadOnly($Value)
	{
		$this->_isReadOnly = $Value;

		$this->SetReadOnlyStatus();
	}

	/*
	IsAdminUser property

	@return boolean
	@param boolean $Value
	*/
	public function getIsAdminUser()
	{
		return $this->_isAdminUser;
	}

	public function setIsAdminUser($Value)
	{
		$this->_isAdminUser = $Value;

		$this->SetAdminUserStatus();
	}

	protected function SetReadOnlyStatus()
    {
		if ($this->_isReadOnly)
		{
			//Make sure we aren't in Admin Mode
			$this->_isAdminUser = false;
			$this->SetAdminUserStatus();
		}
    }

    protected function SetAdminUserStatus()
    {
		//Admin User = true is not compatible with ReadOnly = true
		if ($this->_isAdminUser)
		{
			//Make sure we aren't in ReadOnly mode
			$this->_isReadOnly = false;
			$this->SetReadOnlyStatus();
		}
    }

	/*
	ActionLogAssociatedEntity property

	@return string
	@param string $Value
	 */
	public function getActionLogAssociatedEntityType()
	{
		return $this->_actionLogAssociatedEntityType;
	}

	public function setActionLogAssociatedEntityType($Value)
	{
		$this->_actionLogAssociatedEntityType = $Value;
	}

	/*
	ActionLogAssociatedEntityID property

	@return int
	@param int $Value
	 */
	public function getActionLogAssociatedEntityID()
	{
		return $this->_actionLogAssociatedEntityID;
	}

	public function setActionLogAssociatedEntityID($Value)
	{
		$this->_actionLogAssociatedEntityID = $Value;
	}

 	protected function SetupControls()
	{
		parent::SetupControls();

		$this->MessagesSummary = new RepeaterControl();
		$this->MessagesSummary->SetCallback($this, "MessageSummaryCallBack");
		$this->MessagesSummary->ItemIDsuffixFormat = "{MessageID}";

		//----------Message Detail DIV----------------
		//Content Text Area
		$this->NewCommentContent = new TextAreaControl();
        $this->NewCommentContent->LabelText = "Add Comment";
        $this->NewCommentContent->AddValidator("GenericValidator","IsRequired");

        //Submit Button
        $this->NewCommentSubmit = new JavascriptButtonControl();
        $this->NewCommentSubmit->LabelText = "Submit";
        $this->NewCommentSubmit->ControlStyle->AddStyle("display: inline;");

		//Comments Repeater
		$this->Comments = new RepeaterControl();
		$this->Comments->SetCallback($this, "CommentsCallBack");
		$this->Comments->ItemIDsuffixFormat = "{CommentID}";

		//-------------New Message DIV-----------------
        //Subject Title Text Box
        $this->NewMessageSubject = new TitleTextBoxControl();
        $this->NewMessageSubject->LabelText = "Subject";
        $this->NewMessageSubject->AddValidator("GenericValidator","IsRequired");

        //Content Text Area
        $this->NewMessageContent = new TextAreaControl();
        $this->NewMessageContent->LabelText = "Content";
        $this->NewMessageContent->AddValidator("GenericValidator","IsRequired");

        //Submit Button
        $this->NewMessageSubmit = new JavascriptButtonControl();
        $this->NewMessageSubmit->LabelText = "Submit";
        $this->NewMessageSubmit->ControlStyle->AddStyle("display: inline;");

        //-----------------Hidden Fields--------------------
        $this->ActiveMessageID = new HiddenControl();
        $this->ActiveCommentID = new HiddenControl();

	}

	public function AJAX_DisplayMessage($Processor)
	{
		$Processor->Template->FileName = "messages_messagedisplay";
		$Processor->Template->ControlName = $this->Name;

		$selectedMessage = $this->_messages->Messages[$Processor->EventParameters['messageid']];
		$this->ActiveMessageID->DefaultValue = $Processor->EventParameters['messageid'];

		//Determine which template we should use
		if ($this->_isAdminUser)
		{
			//Admin Mode
			$this->_template->FileName = "messagedetail_admin";
		}
		else if ($this->_isReadOnly)
		{
			//Read Only Mode
			$this->_template->FileName = "messagedetail_readonly";
		}
		else
		{
			//Normal Mode
			$this->_template->FileName = "messagedetail";
		}
		$this->_template->IsMasterLayoutUsed = false;
		$this->_template->RequestFileType = "htm";


		$this->_template->MessageID = $selectedMessage->MessageID;
		$this->_template->MessageSubject = $selectedMessage->Subject;
		$this->_template->Author = DIescape("{$selectedMessage->User->FirstName} {$selectedMessage->User->LastName}");
		$this->_template->MessageDate = $selectedMessage->Timestamp->FriendlyDate;
		$this->_template->MessageContent = DImarkdown($selectedMessage->Content);

		//Set the Comments repeater's data
		$this->Comments->Data = $selectedMessage->Comments;

		if (count($selectedMessage->Comments) == 0)
		{
			$this->Comments->Template->FileName = "messagedetail_comments_nocomments";
		}

		//Make sure we don't have any new lines in the template
		$output = $this->Render();
		$output = str_replace("\n", " ", $output);

		$Processor->Template->MessageDetail = $output;

		//Now figure out what comment delete observsers we need to register
		if ($this->_isAdminUser)
		{
			$javascriptFunctions = "function {$this->Name}_Comments_Item_DeleteComment_OnClick(e)";

			$observers = $this->Comments->RenderObservers($javascriptFunctions);

			$Processor->Template->CommentDeleteObservers = $observers;
		}

	}

	public function CommentsCallBack($CurrentElement, $Template)
	{

		//Set its template
		if ($this->_isAdminUser)
		{
			$Template->FileName = "messagedetail_comment_admin";
		}
		else
		{
			$Template->FileName = "messagedetail_comment";
		}

		$Template->CommentID = $CurrentElement->CommentID;
		$Template->Author = DIescape("{$CurrentElement->User->FirstName} {$CurrentElement->User->LastName}");
		$Template->CommentDate = $CurrentElement->Timestamp->FriendlyDate;
		$Template->CommentContent = DImarkdown($CurrentElement->Content);
	}

	public function AJAX_AddComment($Processor)
	{

		$Processor->Template->ControlName = $this->Name;

		$isValid = $this->NewCommentContent->Validate();

		$Processor->Template->ValidationMessage = $this->NewCommentContent->ValidationMessage;

		if ($isValid)
		{
			$Processor->Template->FileName = "messages_addcomment_success";

			$tempMessage = $this->_messages->Messages[$this->ActiveMessageID->Value];

			if ($tempMessage->IsLoaded)
			{

				//Save the comment
				$tempMessage->AddComment(Application::CurrentUser(), $this->NewCommentContent->Value);

				if (is_set($this->_actionLogAssociatedEntityType))
				{
					$logEntityType = $this->_actionLogAssociatedEntityType;
					$logEntityID = $this->_actionLogAssociatedEntityID;

                    Action::Log("CommentCreated", "A new comment was added.", $logEntityID, "view", $logEntityType);
				}

				//Build the new comment DIV
				$newComment = new Renderable();
				$newComment->Template->RequestFileType = "htm";
				$newComment->Template->ControlName = "{$this->Name}_Comments_Item_{$tempMessage->LatestComment->CommentID}";

				$this->CommentsCallBack($tempMessage->LatestComment, $newComment->Template);
				$newComment->Template->DIVstyle = "display:none;";

				$newCommentDIV = $newComment->Render();
				$newCommentDIV = str_replace("\n", " ", $newCommentDIV);

				$Processor->Template->NewCommentDIV = $newCommentDIV;
				$Processor->Template->NewCommentID = $tempMessage->LatestComment->CommentID;

				//IF we are in admin mode, add the observer for the delete image
				if ($this->_isAdminUser)
				{
					$Processor->Template->CommentDeleteObservers = "Event.observe('Messages_Comments_Item_{$tempMessage->LatestComment->CommentID}_DeleteComment', 'click', Messages_Comments_Item_DeleteComment_OnClick);";
				}
			}
		}
		else
		{
			$Processor->Template->FileName = "messages_addcomment_failure";
		}

	}

	public function AJAX_DeleteComment($Processor)
	{
		$Processor->Template->ControlName = $this->Name;

        if ($this->ActiveMessageID->Value > 0 && $this->ActiveCommentID->Value > 0)
        {

       		$targetMessage = $this->_messages->Messages[$this->ActiveMessageID->Value];

			if ($targetMessage->IsLoaded && array_key_exists($this->ActiveCommentID->Value, $targetMessage->Comments))
			{
				//Get a reference to the target comment
				$targetComment = $targetMessage->Comments[$this->ActiveCommentID->Value];

				//Delete the comment
				$targetMessage->RemoveComment($targetComment);

				if (is_set($this->_actionLogAssociatedEntityType))
				{
					$logEntityType = $this->_actionLogAssociatedEntityType;
					$logEntityID = $this->_actionLogAssociatedEntityID;
				}
				else
				{
					$logEntityType = $this->AssociatedEntityType->Value;
					$logEntityID = $this->AssociatedEntityID->Value;
				}

				Action::Log("CommentDeleted", "A comment was deleted.", $logEntityID, "view", $logEntityType);


				$Processor->Template->FileName = "messages_deletecomment_success";
				$Processor->Template->OldCommentID = $this->ActiveCommentID->Value;
			}
        }
	}

	public function AJAX_AddMessage($Processor)
	{
		$Processor->Template->ControlName = $this->Name;

		//Validate our controls
		$isSubjectValid = $this->NewMessageSubject->Validate();
		$isContentValid = $this->NewMessageContent->Validate();

		$Processor->Template->SubjectValidationMessage = $this->NewMessageSubject->ValidationMessage;
		$Processor->Template->ContentValidationMessage = $this->NewMessageContent->ValidationMessage;

		if ($isSubjectValid && $isContentValid)
		{

			//Add the message
            $targetMessage = new Message(null, $this->_messages->AssociatedEntityType, $this->_messages->AssociatedEntityID);

            $targetMessage->User =  Application::CurrentUser();
            $targetMessage->Subject = $this->NewMessageSubject->Value;
            $targetMessage->Content = $this->NewMessageContent->Value;

            $targetMessage->Save();

			if (is_set($this->_actionLogAssociatedEntityType))
			{
				$logEntityType = $this->_actionLogAssociatedEntityType;
				$logEntityID = $this->_actionLogAssociatedEntityID;

                Action::Log("MessageCreated", "A new message was added.", $logEntityID, "view", $logEntityType);
			}

			if (count($this->_messages->Messages) == 0)
			{
				//Just added the first message
				$Processor->Template->FileName = "messages_addmessage_success_firstmessage";

				//Reload our messages & rebind the repeater control
				$this->_messages->Load();
				$this->MessagesSummary->Data = $this->_messages->Messages;

				//Build a renderable object to build a new message list
				$messageList = new Renderable();
				$messageList->Template->RequestFileType = "htm";
				$messageList->Template->ControlName = $this->Name;

				$this->MessagesSummary->Template->RequestFileType = "htm";

				$messageListDIV = $this->RenderMessageList($messageList);
				$messageListDIV = str_replace("\n", " ", $messageListDIV);

				$Processor->Template->MessageListDIV = $messageListDIV;
			}
			else
			{
				//We have existing messages
                $Processor->Template->FileName = "messages_addmessage_success_additionalmessage";

				//Build a renderable object for the message summary
				$newMessageSummary = new Renderable();
				$newMessageSummary->Template->RequestFileType = "htm";
				$newMessageSummary->Template->ControlName = "{$this->Name}_MessagesSummary_Item_{$targetMessage->MessageID}";
				$newMessageSummary->Template->Element = $targetMessage;

				$this->MessageSummaryCallBack($targetMessage, $newMessageSummary->Template);

				$newSummaryDIV = $newMessageSummary->Render();
				$newSummaryDIV = str_replace("\n", " ", $newSummaryDIV);

				$Processor->Template->NewSummaryDIV = $newSummaryDIV;

			}

			$Processor->Template->NewMessageID = $targetMessage->MessageID;
		}
		else
		{
			$Processor->Template->FileName = "messages_addmessage_failure";
		}

	}

	public function AJAX_DeleteMessage($Processor)
	{
		$Processor->Template->ControlName = $this->Name;

        if ($this->ActiveMessageID->Value > 0)
        {
			$targetMessage = $this->_messages->Messages[$this->ActiveMessageID->Value];

			if ($targetMessage->IsLoaded)
			{
				//Delete the message
				$targetMessage->Delete();

				if (is_set($this->_actionLogAssociatedEntityType))
				{
					$logEntityType = $this->_actionLogAssociatedEntityType;
					$logEntityID = $this->_actionLogAssociatedEntityID;
				}
				else
				{
					$logEntityType = $this->AssociatedEntityType->Value;
					$logEntityID = $this->AssociatedEntityID->Value;
				}

				Action::Log("MessageDeleted", "A message was deleted.", $logEntityID, "view", $logEntityType);


				$Processor->Template->FileName = "messages_deletemessage_success";
				$Processor->Template->OldMessageID = $this->ActiveMessageID->Value;
			}
		}
	}

	public function Render()
	{
		//Only do this if we need it.
		if ($this->_template->FileName == "messages")
		{
			//Setup a renderable object for the message list
            $messageList = new Renderable();
			$messageList->Template->RequestFileType = $this->_template->RequestFileType;
			$messageList->Template->ControlName = $this->Name;

			//Build the message list content
			$this->_template->MessageList = $this->RenderMessageList($messageList);
		}

		$returnValue = parent::Render();

		return $returnValue;
	}

	protected function RenderMessageList($MessageList)
	{

        //Are there any messages to render?
		if (is_set($this->_messages) == false || count($this->_messages->Messages) == 0)
		{
			//No messages, so use a no-messages template
			if ($this->_isReadOnly)
			{
				$MessageList->Template->FileName = "messagelist_nomessages_readonly";
			}
			else
			{
				$MessageList->Template->FileName = "messagelist_nomessages";
			}
		}
		else
		{
			//There are messages
			if ($this->_isReadOnly)
			{
				$MessageList->Template->FileName = "messagelist_readonly";
			}
			else
			{
				$MessageList->Template->FileName = "messagelist";
			}

			//Render a repeater control to generate the summary divs for each
			//message

			$MessageList->Template->MessageListSummary = $this->MessagesSummary->Render();

		}

		//Render it into a template variable on our main template
		$returnValue = $MessageList->Render();

		return $returnValue;

	}

	public function MessageSummaryCallBack($CurrentElement, $Template)
	{

		//Set the template FileName
		$Template->FileName = "messagesummary";

		//Build the Author and Message Date
		$Template->Author = DIescape("{$CurrentElement->User->FirstName} {$CurrentElement->User->LastName}");
		$Template->MessageDate = $CurrentElement->Timestamp->FriendlyDate;

		//Do we have comments?
        if (count($CurrentElement->Comments) > 0)
        {
            $latestComment = new Renderable();
            $latestComment->Template->RequestFileType = $this->_template->RequestFileType;
            $latestComment->Template->FileName = "messagesummary_latestcomment";

            $latestComment->Template->Author = DIescape("{$CurrentElement->LatestComment->User->FirstName} {$CurrentElement->LatestComment->User->LastName}");
			$latestComment->Template->CommentDate = $CurrentElement->LatestComment->Timestamp->FriendlyDate;

			$Template->LatestComment = $latestComment->Render();
        }

	}

}
?>