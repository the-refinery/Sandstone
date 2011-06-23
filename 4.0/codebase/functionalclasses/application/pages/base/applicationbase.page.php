<?php 

SandstoneNamespace::Using("Sandstone.Application");
SandstoneNamespace::Using("Sandstone.Lookup");
SandstoneNamespace::Using("Sandstone.Utilities.String");

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
		
	// Type can be anything you want, but generally "error", "notice" or "success"
	protected function SetNotificationMessage($Message, $Type = 'success')
	{
		Application::SetSessionVariable('notificationmessage', $Message);
		Application::SetSessionVariable('notificationmessagetype', $Type);
	}
    
} 

?>
