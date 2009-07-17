<?php 

NameSpace::Using("Sandstone.Application");
Namespace::Using("Sandstone.Lookup");
Namespace::Using("Sandstone.Utilities.String");

class ApplicationBasePage extends BasePage
{
	protected function Generic_PreProcessor(&$EventParameters)
	{
		$this->SetupDefaultTemplateVariables();
	}
	
	public function SetupDefaultTemplateVariables()
	{
		$license =  Application::License();
		
		$this->_template->License = $license;
	}
		
	protected function SetNotificationMessage($Message)
	{
		Application::SetSessionVariable('notificationmessage', $Message);
	}
    
} 

?>
