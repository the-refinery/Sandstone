<?php
/**
 * Messages Control Class File
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

SandstoneNamespace::Using("Sandstone.Message");
SandstoneNamespace::Using("Sandstone.Markdown");

class MessagesControl extends BaseControl
{

	protected $_messages;
	protected $_isReadOnly;
	protected $_isAdminUser;

	protected $_deleteImage;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('messages_general');
		$this->_bodyStyle->AddClass('messages_body');

		$this->Message->BodyStyle->AddClass('messages_message');
		$this->Label->BodyStyle->AddClass('messages_label');

		//Set this up once, so we don't have to keep rebuilding it.
		$this->_deleteImage = new ImageControl();
		$this->_deleteImage->URL = "images/sandstone/trash.gif";

        $this->_isTopLevelControl = true;
        $this->_isRawValuePosted = false;
	}

	/**
	 * Messages property
	 *
	 * @return Messages
	 *
	 * @param Messages $Value
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

			$this->LoadMessagesList();
            $this->AssociatedEntityType->DefaultValue = $this->_messages->AssociatedEntityType;
            $this->AssociatedEntityID->DefaultValue = $this->_messages->AssociatedEntityID;
		}
		else
		{
			$this->_messages = null;

			$this->ClearMessagesList();
            $this->AssociatedEntityType->DefaultValue = null;
            $this->AssociatedEntityID->DefaultValue = null;
		}
	}

	/**
	 * IsReadOnly property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
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

	/**
	 * IsAdminUser property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
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

    protected function SetupControls()
	{
		parent::SetupControls();

		$this->Label->Text = "Messages";

        $this->BuildMessagesList();
        $this->BuildMessageDetail();
        $this->BuildAddMessage();

        $this->AssociatedEntityType = new HiddenControl();
        $this->AssociatedEntityID = new HiddenControl();
        $this->ActiveMessageID = new HiddenControl();
        $this->ActiveCommentID = new HiddenControl();

	}

	protected function SetupControlJavascript()
    {

    	//View Message
        $this->_JS->ViewMessage->Add("\$('{$this->ActiveMessageID->Name}').value = MessageID;");
		$this->_JS->ViewMessage->Add($this->MessageDetail->Effects->InnerHTML);
        $this->_JS->ViewMessage->AddControlEvent("ViewMessageDetail", false, $this);
        $this->_JS->ViewMessage->Add($this->MessagesList->Effects->BlindUp);
        $this->_JS->ViewMessage->Add($this->MessageDetail->Effects->BlindDown);
        $this->_JS->ViewMessage->AddParameter("MessageID");

        //View List
        $this->_JS->ViewList->Add("\$('{$this->ActiveMessageID->Name}').value = \"\";");
        $this->_JS->ViewList->Add($this->MessageDetail->Effects->BlindUp);
        $this->_JS->ViewList->Add($this->MessagesList->Effects->BlindDown);

        //Add Message
        $this->_JS->AddMessage->Add("\$('{$this->ActiveMessageID->Name}').value = \"\";");
        $this->_JS->AddMessage->Add("\$('{$this->AddMessage->Subject->Name}').value = \"{$this->AddMessage->Subject->Label->Text}\";");
        $this->_JS->AddMessage->Add("\$('{$this->AddMessage->Content->Name}').value = \"\";");
        $this->_JS->AddMessage->Add($this->AddMessage->Subject->Message->Effects->Hide);
        $this->_JS->AddMessage->Add($this->AddMessage->Content->Message->Effects->Hide);
        $this->_JS->AddMessage->Add($this->MessagesList->Effects->BlindUp);
        $this->_JS->AddMessage->Add($this->AddMessage->Effects->BlindDown);

        //Cancel Message
        $this->_JS->CancelMessage->Add("\$('{$this->ActiveMessageID->Name}').value = \"\";");
        $this->_JS->CancelMessage->Add($this->AddMessage->Effects->BlindUp);
        $this->_JS->CancelMessage->Add($this->MessagesList->Effects->BlindDown);

        // Save Message
        $this->_JS->SaveMessage->AddControlEvent("MessageSave", false, $this);

        // Delete Message
		$this->_JS->DeleteMessage->Add("\$('{$this->ActiveMessageID->Name}').value = MessageID;");
        $this->_JS->DeleteMessage->Add($this->MessageDetail->Effects->BlindUp);
        $this->_JS->DeleteMessage->Add($this->MessagesList->Effects->BlindDown);
		$this->_JS->DeleteMessage->AddControlEvent("MessageDelete", false, $this);
		$this->_JS->DeleteMessage->AddParameter("MessageID");

		// Save Comment
        $this->_JS->SaveComment->AddControlEvent("CommentSave", false, $this);

        // Delete Comment
        $this->_JS->DeleteComment->Add("\$('{$this->ActiveCommentID->Name}').value = CommentID;");
		$this->_JS->DeleteComment->AddControlEvent("CommentDelete", false, $this);
		$this->_JS->DeleteComment->AddParameter("CommentID");

        parent::SetupControlJavascript();

    }

    protected function BuildMessagesList()
    {
        $this->MessagesList = new DIVcontrol();
        $this->MessagesList->BodyStyle->AddClass("messages_list");
        $this->MessagesList->Effects->Scope= $this->_effects->Scope;

        $this->MessagesList->TopAddMessage = $this->BuildAddMessageLink();
        $this->MessagesList->BottomAddMessage = $this->BuildAddMessageLink();
    }

	protected function BuildAddMessageLink()
	{

    	$returnValue = new JavascriptLinkControl();
		$returnValue->BodyStyle->AddClass("messages_utilitylink");
		$returnValue->AnchorText = "Add Message";
		$returnValue->JS->OnClick->AddFunctionCall($this->_JS->AddMessage);
		$returnValue->Effects->Scope= $this->_effects->Scope;

		return $returnValue;
	}

    protected function BuildMessageDetail()
    {
        $this->MessageDetail = new DIVcontrol();
        $this->MessageDetail->BodyStyle->AddClass("messages_detail");
        $this->MessageDetail->BodyStyle->AddStyle("display: none;");
        $this->MessageDetail->InnerHTML = "<h2>Loading...</h2>";
        $this->MessageDetail->Effects->Scope= $this->_effects->Scope;

        //This is here so we can process returned results, a different label text is rendered
        //this is just set here for validation.  To change the rendered one, it's in LoadMessageDetail();
		$this->MessageDetail->NewComment = new TextAreaControl();
		$this->MessageDetail->NewComment->Label->Text = "Comment Content";
		$this->MessageDetail->NewComment->ControlStyle->AddStyle("display: none;");
		$this->MessageDetail->NewComment->AddValidator("GenericValidator","IsRequired");
		$this->MessageDetail->NewComment->Effects->Scope= $this->_effects->Scope;

	}

    protected function BuildAddMessage()
    {
        //The Main DIV
        $this->AddMessage = new DIVcontrol();
        $this->AddMessage->BodyStyle->AddClass("messages_addmessage");
        $this->AddMessage->BodyStyle->AddStyle("display: none;");
        $this->AddMessage->Effects->Scope= $this->_effects->Scope;

        //Our Title
        $this->AddMessage->InnerHTML = "<h2>New Message</h2>";

        //Subject Title Text Box
        $this->AddMessage->Subject = new TitleTextBoxControl();
        $this->AddMessage->Subject->Label->Text = "Subject";
        $this->AddMessage->Subject->AddValidator("GenericValidator","IsRequired");
        $this->AddMessage->Subject->Effects->Scope= $this->_effects->Scope;

        //Content Text Area
        $this->AddMessage->Content = new TextAreaControl();
        $this->AddMessage->Content->Label->Text = "Message Content";
		$this->AddMessage->Content->Label->IsRendered = false;
        $this->AddMessage->Content->AddValidator("GenericValidator","IsRequired");
        $this->AddMessage->Content->Effects->Scope= $this->_effects->Scope;

        //Submit Button
        $this->AddMessage->Submit = new JavascriptButtonControl();
        $this->AddMessage->Submit->Label->Text = "Submit";
        $this->AddMessage->Submit->ControlStyle->AddStyle("display: inline;");
        $this->AddMessage->Submit->JS->OnClick->AddFunctionCall($this->_JS->SaveMessage);
        $this->AddMessage->Submit->Effects->Scope= $this->_effects->Scope;

        //Cancel Link
        $this->AddMessage->Cancel = new JavascriptLinkControl();
        $this->AddMessage->Cancel->BodyStyle->AddClass("messages_utilitylink");
        $this->AddMessage->Cancel->AnchorText = "Cancel";
        $this->AddMessage->Cancel->JS->OnClick->AddFunctionCall($this->_JS->CancelMessage);
        $this->AddMessage->Cancel->Effects->Scope= $this->_effects->Scope;
    }

    protected function LoadMessagesList()
    {

    	//Start building the template
    	$template = "{TopAddMessage}";

    	if (count($this->_messages->Messages) > 0)
    	{
	        foreach($this->_messages->Messages as $tempMessage)
	        {
        		//Build a summary DIV for this message
        		$summaryDIV = new DIVcontrol();
        		$summaryDIV->BodyStyle->AddClass("messages_summary");
        		$summaryDIV->Effects->Scope= $this->_effects->Scope;

        		//Build the Subject DIV
				$summaryDIV->Subject = new DIVcontrol();
				$summaryDIV->Subject->BodyStyle->AddClass("messages_summarysubject");
				$summaryDIV->Subject->Effects->Scope= $this->_effects->Scope;

	            $summaryDIV->Subject->ViewLink = new JavascriptLinkControl();
	            $summaryDIV->Subject->ViewLink->AnchorText = DIescape($tempMessage->Subject);
	            $summaryDIV->Subject->ViewLink->JS->OnClick->AddFunctionCall($this->_JS->ViewMessage, Array($tempMessage->MessageID));
	            $summaryDIV->Subject->ViewLink->Effects->Scope= $this->_effects->Scope;

				//Build the Author DIV
				$summaryDIV->Author = new DIVcontrol();
				$summaryDIV->Author->BodyStyle->AddClass("messages_summaryauthor");
				$summaryDIV->Author->Effects->Scope= $this->_effects->Scope;

				$userName = DIescape("{$tempMessage->User->FirstName} {$tempMessage->User->LastName}");
				$summaryDIV->Author->InnerHTML = "by {$userName} <em>{$tempMessage->Timestamp->FriendlyDate}</em>";

                if (count($tempMessage->Comments) > 0)
                {
                    //Build the latest comment div
                    $summaryDIV->LatestComment = new DIVcontrol();
                    $summaryDIV->LatestComment->BodyStyle->AddClass("messages_summarylatestcomment");
                    $summaryDIV->LatestComment->Effects->Scope= $this->_effects->Scope;

                    $userName = DIescape("{$tempMessage->LatestComment->User->FirstName} {$tempMessage->LatestComment->User->LastName}");
                    $summaryDIV->LatestComment->InnerHTML = "Latest comment by {$userName} <em>{$tempMessage->LatestComment->Timestamp->FriendlyDate}</em>";
                }

	            //Add the summary div to the list div
	            $divName = $this->GenerateMessageSummaryDIVname($tempMessage);
	            $this->MessagesList->$divName = $summaryDIV;

	            //Add this new name to the template
	            $template .= "{{$divName}}";
	        }

	        $template .= "{BottomAddMessage}";
		}

        $this->MessagesList->Template = $template;

    }

	protected function GenerateMessageSummaryDIVname($Message)
	{
		$returnValue = "Message_{$Message->MessageID}";

		return $returnValue;
	}

    protected function ClearMessagesList()
    {
        $this->MessagesList = new DIVcontrol();
    }

    protected function SetReadOnlyStatus()
    {
		if ($this->_isReadOnly)
		{
			//Make sure we aren't in Admin Mode
			$this->_isAdminUser = false;
			$this->SetAdminUserStatus();

    		//Message List Add Links
			$this->MessagesList->TopAddMessage->IsRendered = false;
    		$this->MessagesList->BottomAddMessage->IsRendered = false;

            //Add Message DIV
			$this->AddMessage->Subject->ControlStyle->AddStyle("display: none;");
			$this->AddMessage->Content->ControlStyle->AddStyle("display: none;");
			$this->AddMessage->Submit->IsRendered = false;
		}
		else
		{
    		//Message List Add Links
			$this->MessagesList->TopAddMessage->IsRendered = true;
    		$this->MessagesList->BottomAddMessage->IsRendered = true;

            //Add Message DIV
			$this->AddMessage->Subject->ControlStyle->RemoveStyle("display: none;");
			$this->AddMessage->Content->ControlStyle->RemoveStyle("display: none;");
			$this->AddMessage->Submit->IsRendered = true;
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

    protected function ViewMessageDetail_Handler($EventParameters)
    {
        $returnValue = new EventResults();

        if ($this->ActiveMessageID->Value > 0)
        {

            //Setup the DIV
            $this->LoadMessageDetail($this->ActiveMessageID->Value);

            //Send the new DIV content
            echo $this->MessageDetail->Effects->InnerHTMLblock;

            $returnValue->Value = true;
        }
        else
        {
            //No Message ID Passed
            $returnValue->Value = false;
        }

        $returnValue->Complete();

        return $returnValue;
    }

    protected function LoadMessageDetail($MessageID)
    {
        $tempMessage = new Message($MessageID);

        $this->MessageDetail = new DIVcontrol();
        $this->MessageDetail->Effects->Scope= $this->_effects->Scope;

        if ($tempMessage->IsLoaded)
        {

        	//Build the Subject DIV
			$this->MessageDetail->Subject = new DIVcontrol();
			$this->MessageDetail->Subject->BodyStyle->AddClass("messages_detailsubject");
			$this->MessageDetail->Subject->Effects->Scope= $this->_effects->Scope;

            $this->MessageDetail->Subject->SubjectLink = new JavascriptLinkControl();
            $this->MessageDetail->Subject->SubjectLink->AnchorText = DIescape($tempMessage->Subject);
            $this->MessageDetail->Subject->SubjectLink->JS->OnClick->AddFunctionCall($this->_JS->ViewList);
            $this->MessageDetail->Subject->SubjectLink->Effects->Scope= $this->_effects->Scope;

			//If we are in Admin Mode, add a delete link
			if ($this->_isAdminUser)
			{
				//Force this inline with the Subject
				$this->MessageDetail->Subject->BodyStyle->AddStyle("display: inline;");

				//Now add the link
				$this->MessageDetail->Delete = new JavascriptLinkControl();
				$this->MessageDetail->Delete->AnchorText = "Delete";
				$this->MessageDetail->Delete->LinkImage = $this->_deleteImage;
				$this->MessageDetail->Delete->JS->OnClick->AddFunctionCall($this->_JS->DeleteMessage, Array($tempMessage->MessageID));
				$this->MessageDetail->Delete->Effects->Scope= $this->_effects->Scope;
			}

			//Build the Author DIV
			$this->MessageDetail->Author = new DIVcontrol();
			$this->MessageDetail->Author->BodyStyle->AddClass("messages_detailauthor");
			$this->MessageDetail->Author->Effects->Scope= $this->_effects->Scope;

			$userName = DIescape("{$tempMessage->User->FirstName} {$tempMessage->User->LastName}");
			$this->MessageDetail->Author->InnerHTML = "by {$userName} <em>{$tempMessage->Timestamp->FriendlyDate}</em>";

			//Build the Content DIV
			$this->MessageDetail->Content= new DIVcontrol();
			$this->MessageDetail->Content->BodyStyle->AddClass("messages_detailcontent");
			$this->MessageDetail->Content->Effects->Scope= $this->_effects->Scope;

			$this->MessageDetail->Content->InnerHTML = DImarkdown($tempMessage->Content);

            //Loop through any comments
            if (count($tempMessage->Comments) > 0)
            {
                foreach($tempMessage->Comments as $tempComment)
                {
					//Build a comment DIV for this comment
					$commentDIV = new DIVcontrol();
					$commentDIV->BodyStyle->AddClass("messages_detailcomment");
					$commentDIV->Effects->Scope= $this->_effects->Scope;

					//Build the comment author DIV
					$commentDIV->Author = new DIVcontrol();
					$commentDIV->Author->BodyStyle->AddClass("messages_detailcommentauthor");
					$commentDIV->Author->Effects->Scope= $this->_effects->Scope;

					$userName = DIescape("{$tempComment->User->FirstName} {$tempComment->User->LastName}");
					$commentDIV->Author->InnerHTML = "{$userName} <em>{$tempComment->Timestamp->FriendlyDate}</em>";

					//If we are in Admin Mode, add a delete link
					if ($this->_isAdminUser)
					{
						//Force this inline with the Author
						$commentDIV->Author->BodyStyle->AddStyle("display: inline;");

						//Now add the link
						$commentDIV->Delete = new JavascriptLinkControl();
						$commentDIV->Delete->AnchorText = "Delete";
						$commentDIV->Delete->LinkImage = $this->_deleteImage;
						$commentDIV->Delete->JS->OnClick->AddFunctionCall($this->_JS->DeleteComment, Array($tempComment->CommentID));
						$commentDIV->Delete->Effects->Scope= $this->_effects->Scope;
					}

                    //Build the comment content DIV
                    $commentDIV->Content = new DIVcontrol();
                    $commentDIV->Content->BodyStyle->AddClass("messages_detailcommentcontent");
                    $commentDIV->Content->Effects->Scope= $this->_effects->Scope;

					$commentDIV->Content->InnerHTML = DImarkdown($tempComment->Content);

                    //Add the comment DIV to the display div
					$divName = $this->GenerateCommentDIVname($tempComment);
                    $this->MessageDetail->$divName = $commentDIV;
                }
            }


            //If we aren't in read only mode, show the controls to allow for an added comment.
            if ($this->_isReadOnly == false)
            {
				$this->MessageDetail->NewComment = new TextAreaControl();
				$this->MessageDetail->NewComment->Label->Text = "Add Comment";
				$this->MessageDetail->NewComment->Label->BodyStyle->AddStyle("display: block;");
				$this->MessageDetail->NewComment->AddValidator("GenericValidator","IsRequired");
				$this->MessageDetail->NewComment->Rows = 10;
				$this->MessageDetail->NewComment->Columns = 40;
				$this->MessageDetail->NewComment->Effects->Scope= $this->_effects->Scope;

				$this->MessageDetail->Submit = new JavascriptButtonControl();
				$this->MessageDetail->Submit->Label->Text = "Submit";
				$this->MessageDetail->Submit->JS->OnClick->AddFunctionCall($this->_JS->SaveComment);
				$this->MessageDetail->Submit->Effects->Scope= $this->_effects->Scope;
            }
        }
        else
        {
            //Unknown Message ID
            $this->MessageDetail->InnerHTML = "<h2>Unable to Load Message</h2>";
        }

        $this->MessageDetail->CloseLink = new JavascriptLinkControl();
        $this->MessageDetail->CloseLink->BodyStyle->AddClass("messages_utilitylink");
        $this->MessageDetail->CloseLink->AnchorText = "Close";
        $this->MessageDetail->CloseLink->JS->OnClick->AddFunctionCall($this->_JS->ViewList);
        $this->MessageDetail->CloseLink->Effects->Scope= $this->_effects->Scope;

    }

	protected function GenerateCommentDIVname($Comment)
	{
		$returnValue = "Comment_{$Comment->CommentID}";

		return $returnValue;
	}

    protected function MessageSave_Handler($EventParameters)
    {
        $returnValue = new EventResults();

        //Make sure all controls validate
        $isSubjectValid = $this->AddMessage->Subject->Validate();
        $isContentValid = $this->AddMessage->Content->Validate();
				
        if ($isSubjectValid && $isContentValid)
        {
            if ($this->ActiveMessageID->Value > 0)
            {
                //Update
                $targetMessage = new Message($this->ActiveMessageID->Value);

                if ($targetMessage->IsLoaded)
                {
                    $targetMessage->Subject = $this->AddMessage->Subject->Value;
                    $targetMessage->Content = $this->AddMessage->Content->Value;

                    $returnValue->Value = $targetMessage->Save();
                }
            }
            else
            {

                //Insert
                $targetMessage = new Message(null, $this->AssociatedEntityType->Value, $this->AssociatedEntityID->Value);

                $targetMessage->User =  Application::CurrentUser();
                $targetMessage->Subject = $this->AddMessage->Subject->Value;
                $targetMessage->Content = $this->AddMessage->Content->Value;

                $returnValue->Value = $targetMessage->Save();

				Action::Log("MessageCreated", "A new message was added.", $this->AssociatedEntityID->Value, $this->Page->ActivePageName, $this->AssociatedEntityType->Value);
            }

            //Reload our Messages and the message list DIV
            $this->Messages = new Messages($this->AssociatedEntityType->Value, $this->AssociatedEntityID->Value);

            //Now update the message list DIV
            echo $this->MessagesList->Effects->InnerHTMLblock;

            //Clear any Validation Messages
            echo $this->AddMessage->Subject->ValidationJavascript;
            echo $this->AddMessage->Content->ValidationJavascript;

            //Hide the Add Message DIV and display the list div
            echo $this->AddMessage->Effects->BlindUpBlock;
            echo $this->MessagesList->Effects->BlindDownBlock;

            //Finally highlight our new message
            $highlightDIVname = $this->GenerateMessageSummaryDIVname($targetMessage);
			echo $this->MessagesList->$highlightDIVname->Effects->HighlightBlock;

        }
        else
        {
            //Failed Validation

            //Return Validation Messages
            echo $this->AddMessage->Subject->ValidationJavascript;
            echo $this->AddMessage->Content->ValidationJavascript;

            $returnValue->Value = false;
        }


        $returnValue->Complete();

        return $returnValue;
    }

    protected function MessageDelete_Handler($EventParameters)
    {
        $returnValue = new EventResults();

        if ($this->ActiveMessageID->Value > 0)
        {
			$targetMessage = new Message($this->ActiveMessageID->Value);

			if ($targetMessage->IsLoaded)
			{
				//Capture the name before we delete it
				$divName = $this->GenerateMessageSummaryDIVname($targetMessage);

				//Delete the message
				$targetMessage->Delete();

				//Get rid of it's DIV
				echo $this->MessagesList->$divName->Effects->HighlightBlock;
				echo $this->MessagesList->$divName->Effects->SwitchOffBlock;

				//Clear the ActiveCommentID
				echo JavascriptFunctions::FormatJavascriptBlock("\$('{$this->ActiveMessageID->Name}').value = \"\";");

				Action::Log("MessageDeleted", "A message was deleted.", $this->AssociatedEntityID->Value, $this->Page->ActivePageName, $this->AssociatedEntityType->Value);

				$returnValue->Value = true;
			}
			else
			{
				$returnValue->Value = false;
			}
        }
        else
        {
            //No Message ID Passed
            $returnValue->Value = false;
        }

        $returnValue->Complete();

        return $returnValue;
    }

    protected function CommentSave_Handler($EventParameters)
    {
		$returnValue = new EventResults();

		//Make sure the control validates
		$isValid = $this->MessageDetail->NewComment->Validate();

		if ($isValid)
		{

			if ($this->ActiveCommentID->Value > 0)
			{
				//Update
				$targetComment = new Comment($this->ActiveCommentID->Value);

				if ($targetComment->IsLoaded)
				{
					$targetComment->Content = $this->MessageDetail->NewComment->Value;

					$returnValue->Value = $targetComment->Save();

					$highlightComment = $targetComment;
				}
				else
				{
					$returnValue->Value = false;
				}

			}
			else
			{
				//Insert
		        if ($this->ActiveMessageID->Value > 0)
		        {
					$tempMessage = new Message($this->ActiveMessageID->Value);

					if ($tempMessage->IsLoaded)
					{
						$returnValue->Value = $tempMessage->AddComment(Application::CurrentUser(), $this->MessageDetail->NewComment->Value);

						$highlightComment = $tempMessage->LatestComment;

						Action::Log("CommentCreated", "A new comment was added.", $this->AssociatedEntityID->Value, $this->Page->ActivePageName, $this->AssociatedEntityType->Value);
					}
					else
					{
						$returnValue->Value = false;
					}
		        }
		        else
		        {
		            //No Message ID Passed
		            $returnValue->Value = false;
		        }
			}

			//Reload the Message Detail DIV
			$this->LoadMessageDetail($this->ActiveMessageID->Value);

            //Now update the Message Detail DIV
            echo $this->MessageDetail->Effects->InnerHTMLblock;

			//Show a highlight on the specific comment
			$highlightDIVname = $this->GenerateCommentDIVname($highlightComment);
			echo $this->MessageDetail->$highlightDIVname->Effects->HighlightBlock;

            //Finally, clear the Comment Entry box
            echo JavascriptFunctions::FormatJavascriptBlock("\$('{$this->MessageDetail->NewComment->Name}').value = \"\";");

		}
        else
        {
            //Failed Validation

            //Return Validation Messages
            echo $this->MessageDetail->NewComment->ValidationJavascript;

            $returnValue->Value = false;
        }

        $returnValue->Complete();

        return $returnValue;
    }

    protected function CommentDelete_Handler($EventParameters)
    {
        $returnValue = new EventResults();

        if ($this->ActiveMessageID->Value > 0 && $this->ActiveCommentID->Value > 0)
        {

        	$targetMessage = new Message($this->ActiveMessageID->Value);

			if ($targetMessage->IsLoaded && array_key_exists($this->ActiveCommentID->Value, $targetMessage->Comments))
			{

				//Get a reference to the target comment
				$targetComment = $targetMessage->Comments[$this->ActiveCommentID->Value];

				//Make sure we have a locally loaded MessageDetail DIV
				$this->LoadMessageDetail($targetMessage->MessageID);

				//Capture the name before we delete it
				$divName = $this->GenerateCommentDIVname($targetComment);

				//Delete the comment
				$targetMessage->RemoveComment($targetComment);

				//Get rid of it's DIV
				echo $this->MessageDetail->$divName->Effects->HighlightBlock;
				echo $this->MessageDetail->$divName->Effects->SwitchOffBlock;

				//Clear the ActiveCommentID
				echo JavascriptFunctions::FormatJavascriptBlock("\$('{$this->ActiveCommentID->Name}').value = \"\";");

				Action::Log("CommentDeleted", "A comment was deleted.", $this->AssociatedEntityID->Value, $this->Page->ActivePageName, $this->AssociatedEntityType->Value);

				$returnValue->Value = true;
			}
			else
			{
				$returnValue->Value = false;
			}
        }
        else
        {
            //No Message ID Passed
            $returnValue->Value = false;
        }

        $returnValue->Complete();

        return $returnValue;
    }

}
?>