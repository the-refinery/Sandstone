<?php
/*
Search Control Class File

@package Sandstone
@subpackage Application
*/

Namespace::Using("Sandstone.Search");

class SearchControl extends BaseControl
{

	protected $_defaultValue;

	protected $_types;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_bodyStyle->AddClass('search_body');

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;

        //We don't use the wrapper and message stuff.
        $this->_template->IsMasterLayoutUsed = false;

        $this->_template->FileName = "search";

        $this->_types = Array();
    }

	/*
	Types property

	@return array
	 */
	public function getTypes()
	{
		return $this->_types;
	}

	public function AddType($NewType)
	{
		if (strlen($NewType) > 0)
		{
			$this->_types[strtolower($NewType)] = $NewType;
		}
	}

	public function RemoveType($OldType)
	{
		$targetKey = strtolower($OldType);

		if (array_key_exists($targetKey, $this->_searchTypes))
		{
			unset($this->_types[$targetKey]);
		}
	}

    protected function SetupControls()
	{

		parent::SetupControls();

   		$this->SearchText = new TitleTextBoxControl();
		$this->SearchText->ControlStyle->AddClass("search_searchtext");
		$this->SearchText->LabelText = "Search...";

		$this->Submit = new SubmitButtonControl();
		$this->Submit->LabelText = "Search";

		$this->SearchTypes = new HiddenControl();

	}

	public function Render()
	{

		$this->SearchTypes->DefaultValue = implode(",", $this->_types);
		$this->_template->ControlLabelText = $this->SearchText->LabelText;

		$returnValue = parent::Render();

		return $returnValue;
	}
}
?>
