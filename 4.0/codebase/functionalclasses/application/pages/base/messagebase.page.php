<?php

NameSpace::Using("Sandstone.Application");
NameSpace::Using("Sandstone.Message");

class MessageBasePage extends BasePage
{

    protected $_message;

	protected function Generic_PreProcessor(&$EventParameters)
	{

		if (is_set($EventParameters['messageid']))
		{
			$this->_message = new Message($EventParameters['messageid']);
			
            if ($this->_message->IsLoaded)
            {
                $this->_template->Message = $this->_message;
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

	public function AddCommentForm_Processor($EventParameters)
	{

		$this->_message->AddComment(Application::CurrentUser(), $this->AddCommentForm->Content->Value);
        
        $this->AddCommentForm->RedirectTarget = Routing::BuildURLbyEntity($this->_message, "view");
        
		return true;
	}
        
	protected function BuildControlArray($EventParameters)
	{
        
		$this->Comments = new RepeaterControl();
		$this->Comments->ItemIDsuffixFormat = "{CommentID}";

        $this->AddCommentForm = new PageForm($EventParameters);
        
        $this->AddCommentForm->Content = new TextAreaControl();
        $this->AddCommentForm->Content->LabelText = "Add Comment";
        $this->AddCommentForm->Content->AddValidator("GenericValidator", "IsRequired");
        
        $this->AddCommentForm->Submit = new SubmitButtonControl();
		
        parent::BuildControlArray($EventParameters);
    }
    
    
	protected function LoadControlData($EventParameters)
	{    
        $this->Comments->Data = $this->_message->Comments;
    }
    
}