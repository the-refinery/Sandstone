<?
/*
Data Navigation Control Class File

@package Sandstone
@subpackage Application
*/

class DataNavigationControl extends BaseControl
{

	protected $_routingRuleName;
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

		//Build our data for our repeater control
		for ($i = 1; $i <= $this->_totalPages; $i++)
		{

			switch ($i)
			{
				case 1:
					//Page 1, if this isn't the current page, we add a previous
					if ($PageNumber <> 1)
					{
						$this->AddPageLinkData($PageNumber - 1, "prev");
					}

					$this->AddPageLinkData($i);

					break;

				case $this->_totalPages:
					//Last Page.  If this isn't the current page, add a next
					$this->AddPageLinkData($i);

					if ($PageNumber <> $this->_totalPages)
					{
						$this->AddPageLinkData($PageNumber + 1, "next");
					}

					break;

				default:
					$this->AddPageLinkData($i);
					break;
			}
		}

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

			$Template->TargetPageURL = Routing::BuildURLbyRule($this->_routingRuleName, Array("pagenumber"=>$CurrentElement->PageNumber));

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