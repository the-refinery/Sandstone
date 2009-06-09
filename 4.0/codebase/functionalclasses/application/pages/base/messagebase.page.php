<?php

NameSpace::Using("Sandstone.Message");

class MessageBasePage extends ApplicationPage
{

    protected $_message;
    protected $_entity;

    public function getIsModerator()
    {
        $returnValue = false;
        
        if (Application::CurrentUser()->IsInRole(new Role(2)))
        {
            $returnValue = true;
        }
        
        return $returnValue;
    }

	protected function Generic_PreProcessor(&$EventParameters)
	{

		parent::Generic_PreProcessor($EventParameters);

		if (is_set($EventParameters['messageid']))
		{
			$this->_message = new Message($EventParameters['messageid']);
			
            if ($this->_message->IsLoaded)
            {
                $this->_entity = new $this->_message->AssociatedEntityType ($this->_message->AssociatedEntityID);
                
                $this->_template->Message = $this->_message;
								$this->_template->MessageContent = nl2br($this->_message->Content);
                $this->_template->Entity = $this->_entity;
                $this->_template->IsModerator = $this->IsModerator;
            }
            else
            {
                $this->_message = null;                
            }
		}
		
        if (is_set($this->_message) == false)
		{
            $this->_isOKtoLoadControls = false;
			$this->SetResponseCode(404, $EventParameters);
		}

	}

	protected function HTM_Processor($EventParameters)
	{
        parent::HTM_Processor($EventParameters);	

        $this->_message->MarkRead(Application::CurrentUser());

	}
        
	public function AJAX_DeleteComment($Processor)
	{
		$this->_message->Comments[$Processor->EventParameters['commentid']]->Delete();
	}
    
	public function AddCommentForm_Processor($EventParameters)
	{

		$this->_message->AddComment(Application::CurrentUser(), $this->AddCommentForm->Content->Value);
        
        $this->AddCommentForm->RedirectTarget = Routing::BuildURLbyEntity($this->_message, "view");
        
		return true;
	}

	public function DeleteMessageForm_Processor($EventParameters)
	{
        
        $this->_message->Delete();
        
        $this->DeleteMessageForm->RedirectTarget = Routing::BuildURLbyEntity($this->_entity, "viewmessages");
        
        $this->SetNotificationMessage("Message Deleted Successfully");
        
		return true;
	}


	public function CommentsCallback($CurrentElement, $Template)
	{
		$Template->IsModerator = $this->IsModerator;
		$Template->MessageContent = nl2br($CurrentElement->Content);
	}
        
	protected function BuildControlArray($EventParameters)
	{
        
		$this->Comments = new RepeaterControl();
		$this->Comments->ItemIDsuffixFormat = "{CommentID}";
        $this->Comments->SetCallback($this, "CommentsCallback");        

        $this->AddCommentForm = new PageForm($EventParameters);
        
        $this->AddCommentForm->Content = new TextAreaControl();
        $this->AddCommentForm->Content->LabelText = "Add Comment";
        $this->AddCommentForm->Content->AddValidator("GenericValidator", "IsRequired");
        
        $this->AddCommentForm->Submit = new SubmitButtonControl();
		
        $this->DeleteMessageForm = new PageForm($EventParameters);
        
        
        parent::BuildControlArray($EventParameters);
    }
    
    
	protected function LoadControlData($EventParameters)
	{    
        $this->Comments->Data = $this->_message->Comments;
    }
    
}
