<?php

class RoutingBase extends Module
{	
	protected $_routingURL;
	protected $_routingRules = array();
	
	protected $_fileExtension;
	protected $_documentTypes = array();
	
	public function __construct($RoutingURL)
	{
		$this->setRoutingURL($RoutingURL);	
	}
	
	public function getRoutingURL()
	{
		return $this->_routingURL;
	}
	
	public function setRoutingURL($Value)
	{
		$this->_routingURL = $this->FormatURL($Value);
	}
	
	public function RouteURL()
	{
		$this->SetupRoutingRules();
		$this->SetupDefaultRoutingRules();
		
		foreach ($this->_routingRules as $RoutingRule)
		{
			if ($RoutingRule->CheckMatch($this->_routingURL))
			{
				$eventParameters = $RoutingRule->EventParameters;
				break;
			}
		}
		
		// Run a custom method based on the current environment
		// Which is determined by file extension
		$methodName = $this->_fileExtension . "_Environment";
		
		if (method_exists($this, $methodName))
		{
			$this->$methodName(&$eventParameters);
		}
		else
		{
			throw new UnknownFileEnvironmentException("Unknown File Environment: " . $this->_fileExtension);
		}
		
		return $eventParameters;
	}
	
	public function getDefaultPage()
	{
		return "Home";
	}

	protected function AddRoutingRule($RoutingRule)
	{
		if ($RoutingRule instanceof RoutingRule)
		{
			$this->_routingRules[] = $RoutingRule;
		}
	}
	
	// Overload in child class
	public function SetupRoutingRules()
	{
		
	}
	
	public function SetupDefaultRoutingRules()
	{		
		// Javascript
		$javascriptRoutingRule = new RoutingRule('javascript/[*]', $this->_routingURL);
		$javascriptRoutingRule->AddEventParameter('page', 'javascript');
		$javascriptRoutingRule->AddDynamicEventParameter('library', 1);
		$this->AddRoutingRule($javascriptRoutingRule);
		
		// CSS
		$cssRoutingRule = new RoutingRule('css/[*]', $this->_routingURL);
		$cssRoutingRule->AddEventParameter('page', 'css');
		$cssRoutingRule->AddDynamicEventParameter('library', 1);
		$this->AddRoutingRule($cssRoutingRule);
		
		// Image Display
		$imageRoutingRule = new RoutingRule('image/[123]', $this->_routingURL);
		$imageRoutingRule->AddEventParameter('page', 'image');
		$imageRoutingRule->AddDynamicEventParameter('imageid', 1);
		$this->AddRoutingRule($imageRoutingRule);
		
		// Max Size Thumbnail Display
		$maxSizeThumbnailRoutingRule = new RoutingRule('image/max/[123]/[123]', $this->_routingURL);
		$maxSizeThumbnailRoutingRule->AddEventParameter('page', 'image');
		$maxSizeThumbnailRoutingRule->AddEventParameter('event', 'thumbnailbymax');
		$maxSizeThumbnailRoutingRule->AddDynamicEventParameter('imageid', 2);
		$maxSizeThumbnailRoutingRule->AddDynamicEventParameter('max', 3);
		$this->AddRoutingRule($maxSizeThumbnailRoutingRule);
		
		// Manual Size Thumbnail Display
		$manualSizeThumbnailRoutingRule = new RoutingRule('thumbnail/[123]/[123]/[123]', $this->_routingURL);
		$manualSizeThumbnailRoutingRule->AddEventParameter('page', 'image');
		$manualSizeThumbnailRoutingRule->AddEventParameter('event', 'thumbnail');
		$manualSizeThumbnailRoutingRule->AddDynamicEventParameter('imageid', 1);
		$manualSizeThumbnailRoutingRule->AddDynamicEventParameter('width', 2);
		$manualSizeThumbnailRoutingRule->AddDynamicEventParameter('height', 3);
		$this->AddRoutingRule($manualSizeThumbnailRoutingRule);
		
		// Page Event Display
		$pageEventRoutingRule = new RoutingRule('[*]/[*]', $this->_routingURL);
		$pageEventRoutingRule->AddDynamicEventParameter('page', 0);
		$pageEventRoutingRule->AddDynamicEventParameter('event', 1);
		$this->AddRoutingRule($pageEventRoutingRule);
		
		// Page Load Display
		$pageEventRoutingRule = new RoutingRule('[*]', $this->_routingURL);
		$pageEventRoutingRule->AddDynamicEventParameter('page', 0);
		$this->AddRoutingRule($pageEventRoutingRule);
	}
	
	public function FormatURL($URL)
	{		
		// Extract file extension from URL, set as htm if not set		
		if (stripos($URL, "."))
		{
			$this->_fileExtension = ereg_replace("^.+\\.([^.]+)$", "\\1", basename($URL));
		}
		else
		{
			$this->_fileExtension = "htm";
		}
		
		// Remove extension from URL
		$URL = str_replace("." . $this->_fileExtension, "", $URL);
						
		// Remove trailing slash from $url
		if (substr($URL, -1, 1) == '/')
		{
			$URL = substr($URL,0, strlen($URL) - 1);
		}
		
		return $URL;
	}

	public function htm_Environment(&$EventParameters)
	{
		
	}
	
	public function html_Environment(&$EventParameters)
	{
		$this->htm_Environment();
	}
	
	public function js_Environment(&$EventParameters)
	{
		if ($EventParameters['page'] != 'javascript')
		{
			$EventParameters['event'] = 'javascript';			
		}
	}
	
	public function css_Environment(&$EventParameters)
	{
		if ($EventParameters['page'] != 'css')
		{
			$EventParameters['event'] = 'css';			
		}
	}
}
 
?>