<?php

class TemplateEngine	
{
	public function Render($Template)
	{
		return $Template->Content();
	}
}
