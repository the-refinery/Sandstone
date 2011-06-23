<?php
/*
Data Navigation Control Class File

@package Sandstone
@subpackage Application
*/

class DataNavigationControl extends BaseControl
{

	//This must be an odd number.
	const PAGE_WINDOW_SIZE = 11;

	protected $_routingRuleName;
	protected $_routingRuleParameters;
	protected $_recordCount;
	protected $_totalPages;
	protected $_currentPageNumber;

	protected $_data;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('datanavigation_general');
		$this->_bodyStyle->AddClass('datanavigation_body');

		$this->_template->IsMasterLayoutUsed = false;

	}

	/*
	RoutingRuleName property

	@return string
	@param string $Value
	 */
	public function getRoutingRuleName()
	{
		return $this->_routingRuleName;
	}

	public function setRoutingRuleName($Value)
	{
		$this->_routingRuleName = $Value;
	}

	/*
	RoutingRuleParameters property

	@return array
	@param array $Value
	 */
	public function getRoutingRuleParameters()
	{
		return $this->_routingRuleParameters;
	}

	public function setRoutingRuleParameters($Value)
	{
		$this->_routingRuleParameters = $Value;
	}

	/*
	RecordCount property

	@return int
	 */
	public function getRecordCount()
	{
		return $this->_recordCount;
	}

	/*
	TotalPages property

	@return int
	 */
	public function getTotalPages()
	{
		return $this->_totalPages;
	}

	public function Lookup($Class, $Method = "All", $Parameters = Array(), $PageSize, $PageNumber = 1)
	{

		if (is_set($PageNumber) == false)
		{
			$PageNumber = 1;
		}

		$this->_recordCount = LookupCount($Class, $Method, $Parameters);
		$this->_totalPages = ceil($this->_recordCount / $PageSize);
		$this->_currentPageNumber = $PageNumber;

		if ($this->_totalPages > self::PAGE_WINDOW_SIZE)
		{
			$this->SetupPageLinksMultipleWindows();
		}
		else
		{
			$this->SetupPageLinksSingleWindow();
		}
	}

	protected function SetupPageLinksSingleWindow()
	{
		//Build our data for our repeater control
		for ($i = 1; $i <= $this->_totalPages; $i++)
		{
			switch ($i)
			{
				case 1:
					$this->AddPreviousPageLink();
					$this->AddPageLinkData($i);
					break;

				case $this->_totalPages:
					$this->AddPageLinkData($i);
					$this->AddNextPageLink();
					break;

				default:
					$this->AddPageLinkData($i);
					break;
			}
		}
	}


	protected function SetupPageLinksMultipleWindows()
	{
		if ($this->_currentPageNumber <= ((self::PAGE_WINDOW_SIZE + 1) / 2))
		{
			$this->SetupPageLinksFirstWindow();
		}
		elseif ($this->_currentPageNumber >= $this->_totalPages - ((self::PAGE_WINDOW_SIZE - 1) / 2))
		{
			$this->SetupPageLinksLastWindow();
		}
		else
		{
			$this->SetupPageLinksMiddleWindow();
		}
	}

	protected function SetupPageLinksFirstWindow()
	{
		$this->AddPreviousPageLink();

		for ($i=1; $i <= self::PAGE_WINDOW_SIZE; $i++)
		{
			$this->AddPageLinkData($i);
		}

		$this->AddNextPageLink();
		$this->AddLastPageLink();
	}

	protected function SetupPageLinksMiddleWindow()
	{
		$this->AddFirstPageLink();
		$this->AddPreviousPageLink();

		$padding = (self::PAGE_WINDOW_SIZE - 1) / 2;
		$start = $this->_currentPageNumber - $padding;
		$end = $this->_currentPageNumber + $padding;

		for ($i = $start; $i <= $end; $i++)
		{
			$this->AddPageLinkData($i);
		}	

		$this->AddNextPageLink();
		$this->AddLastPageLink();
	}

	protected function SetupPageLinksLastWindow()
	{
		$this->AddFirstPageLink();
		$this->AddPreviousPageLink();

		$start = $this->_totalPages - (self::PAGE_WINDOW_SIZE - 1);

		for ($i = $start; $i <= $this->_totalPages; $i++)
		{
			$this->AddPageLinkData($i);
		}

		$this->AddNextPageLink();
	}
	
	protected function AddPreviousPageLink()
	{
		if ($this->_currentPageNumber <> 1)
		{
			$this->AddPageLinkData($this->_currentPageNumber - 1, "prev");
		}
	}

	protected function AddNextPageLink()
	{
		if ($this->_currentPageNumber <> $this->_totalPages)
		{
			$this->AddPageLinkData($this->_currentPageNumber + 1, "next");
		}
	}

	protected function AddFirstPageLink()
	{
		$this->AddPageLinkData(1, "first");
	}

	protected function AddLastPageLink()
	{
		$this->AddPageLinkData($this->_totalPages, "last");
	}

	protected function AddPageLinkData($TargetPageNumber, $Text = null)
	{

		$navItem['pagenumber'] = $TargetPageNumber;

		if (is_set($Text))
		{
			$navItem['text'] = $Text;
		}
		else
		{
			$navItem['text'] = $TargetPageNumber;
		}

		$this->_data[] = $navItem;

	}

	public function LinksCallback($CurrentElement, $Template)
	{

		//Which template do we use?
		if ($CurrentElement->PageNumber == $this->_currentPageNumber)
		{
			$Template->FileName = "datanavigation_links_currentpage_item";
		}
		else
		{
			$Template->FileName = "datanavigation_links_otherpage_item";

			if (is_array($this->_routingRuleParameters) == false)
			{
				$this->_routingRuleParameters = Array();
			}

			$this->_routingRuleParameters['pagenumber'] = $CurrentElement->PageNumber;

			$Template->TargetPageURL = Routing::BuildURLbyRule($this->_routingRuleName, $this->_routingRuleParameters);

		}

	}

	protected function SetupControls()
	{
		parent::SetupControls();

		$this->Links = new RepeaterControl();
		$this->Links->SetCallback($this, "LinksCallback");
		$this->Links->Template->FileName = "datanavigation_links";
		$this->Links->BodyStyle->AddClass("datanavigation_body");
	}

	public function Render()
	{

		if ($this->_totalPages > 1)
		{
			$this->Links->Data = $this->_data;
		}
		else
		{
			$this->Template->FileName = "datanavigation_singlepage";
		}

		$returnValue = parent::Render();

		return $returnValue;
	}

}
?>
