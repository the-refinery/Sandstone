<?php

class WidgetsPage extends ApplicationPage
{

	protected $_widget;

	protected function Generic_PreProcessor(&$EventParameters)
	{
		if ($EventParameters['filetype'] != "png")
		{
			$this->SetResponseCode(404, $EventParameters);
		}

		$widgetClassName = strtolower($EventParameters['widgetname']) . "widget";

		if (array_search($widgetClassName, Namespace::ClassNames()) === false)
		{
			$this->SetResponseCode(404, $EventParameters);
		}
		else
		{
			$this->_widget = new $widgetClassName ();

			if (($this->_widget instanceof GraphicWidget) == false)
			{
				$this->SetResponseCode(404, $EventParameters);
			}
		}

	}

	protected function PNG_Processor($EventParameters)
	{
		$this->_widget->OutputPNG($EventParameters);
	}

}
?>
