<?php

NameSpace::Using("Sandstone.Message");

class EntityMessagesBasePage extends ApplicationPage
{

    protected $_entity;
    
	public function AddMessageForm_Processor($EventParameters)
	{

		$this->_entity->Messages->AddMessage(Application::CurrentUser(), $this->AddMessageForm->Subject->Value, $this->AddMessageForm->Content->Value);
        
        $this->AddMessageForm->RedirectTarget = Routing::BuildURLbyEntity($this->_entity->Messages->LatestMessage, "view");
        
		return true;
	}
    
	public function MessageListCallback($CurrentElement, $Template)
	{

		$Template->MessageURL = Routing::BuildURLbyEntity($CurrentElement, "view");
        
        if ($CurrentElement->CheckReadStatus(Application::CurrentUser()) == true)
        {
            $Template->MessageStatusClass = "message_read";
        }
        else
        {
            $Template->MessageStatusClass = "message_unread";
        }

	}
        
	protected function BuildControlArray($EventParameters)
	{
        
		$this->MessageList = new RepeaterControl();
		$this->MessageList->ItemIDsuffixFormat = "{MessageID}";
		$this->MessageList->SetCallback($this, "MessageListCallback");

        $this->AddMessageForm = new PageForm($EventParameters);

        $this->AddMessageForm->Subject = new TextBoxControl();
        $this->AddMessageForm->Subject->LabelText = "Subject";
        $this->AddMessageForm->Subject->AddValidator("GenericValidator", "IsRequired");
        
        $this->AddMessageForm->Content = new TextAreaControl();
        $this->AddMessageForm->Content->LabelText = "Message";
        $this->AddMessageForm->Content->AddValidator("GenericValidator", "IsRequired");
        
        $this->AddMessageForm->Submit = new SubmitButtonControl();
		
        parent::BuildControlArray($EventParameters);
    }
    
    
	protected function LoadControlData($EventParameters)
	{
        $this->MessageList->Data = $this->_entity->Messages->Messages;
    }
    
    
}
?>