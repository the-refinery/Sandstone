<?php

class DevPage extends BasePage
{
	protected function HTM_Processor(&$EventParameters)
	{
		parent::HTM_Processor($EventParameters);

		if (Application::Registry()->DevMode == false)
		{
			$this->SetResponseCode(404, $EventParameters);
		}
	}
	
}

?>