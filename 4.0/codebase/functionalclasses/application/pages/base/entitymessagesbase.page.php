<?php

NameSpace::Using("Sandstone.Application");
NameSpace::Using("Sandstone.Message");

class EntityMessagesBasePage extends BasePage
{

    protected $_entity;
    
	protected function BuildControlArray($EventParameters)
	{
        
		$this->MessageList = new RepeaterControl();
		$this->MessageList->ItemIDsuffixFormat = "{MessageID}";

        $this->AddMessageForm = new PageForm($EventParameters);

        $this->AddMessageForm->Title = new TextBoxControl();
        $this->AddMessageForm->Title->LabelText = "Title";
        $this->AddMessageForm->Title->AddValidator("GenericValidator", "IsRequired");
        
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