<?php
/**
 * Entity Relationship Control Class File
 * @package Sandstone
 * @subpackage Application
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

class EntityRelationshipControl extends BaseControl
{

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('entityrelationship_general');
		$this->_bodyStyle->AddClass('entityrelationship_body');

		$this->Message->BodyStyle->AddClass('entityrelationship_message');
		$this->Label->BodyStyle->AddClass('entityrelationship_label');

		$this->_isRawValuePosted = false;
	}


	protected function RenderControlBody()
	{
		$returnValue = $this->RenderLabel();
		$returnValue .= $this->Display->__toString();
		$returnValue .= $this->Select->__toString();
		$returnValue .= $this->Add->__toString();

		return $returnValue;
	}

	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->Display->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;
	}


	protected function SetupControls()
	{
		parent::SetupControls();


		$this->Display = new DIVcontrol();
		$this->Display->BodyStyle->AddClass("entityrelationship_displaydiv");
		$this->SetupDisplayDIVcontrols();

		$this->Select = new DIVcontrol();
		$this->Select->BodyStyle->AddClass("entityrelationship_selectdiv");
		$this->Select->BodyStyle->AddStyle("display:none;");
		$this->SetupSelectDIVcontrols();

		$this->Add = new DIVcontrol();
		$this->Add->BodyStyle->AddClass("entityrelationship_adddiv");
		$this->Add->BodyStyle->AddStyle("display:none;");
		$this->SetupAddDIVcontrols();

	}

	protected function SetupDisplayDIVcontrols()
	{

        $this->Display->InnerHTML = "<h2>Display</h2>";

		$this->Display->List = new ULcontrol();
		$this->Display->List->BodyStyle->AddClass("entityrelationship_displaylist");
		$this->Display->List->AddItem(1, "Item 1");
		$this->Display->List->AddItem(2, "Item 2");
		$this->Display->List->AddItem(3, "Item 3");

        $this->Display->SelectLink = new JavascriptLinkControl();
        $this->Display->SelectLink->AnchorText = "Select";
	}

	protected function SetupSelectDIVcontrols()
	{

        $this->Select->InnerHTML = "<h2>Select</h2>";

        $this->Select->AvailableDropdown = new DropDownControl();
        $this->Select->AvailableDropdown->ControlStyle->AddStyle("display:inline;");

        $this->Select->AvailableDropdown->AddElementGroup("Goerlich");
        $this->Select->AvailableDropdown->AddElement(1, "Dave");
        $this->Select->AvailableDropdown->AddElement(2, "Lynne");
        $this->Select->AvailableDropdown->AddElement(3, "Samantha");
        $this->Select->AvailableDropdown->AddElementGroup("Walsh");
        $this->Select->AvailableDropdown->AddElement(4, "Josh");
        $this->Select->AvailableDropdown->AddElement(5, "Nat");


        $this->Select->CancelLink = new JavascriptLinkControl();
        $this->Select->CancelLink->AnchorText = "Cancel";

        $this->Select->AddLink = new JavascriptLinkControl();
        $this->Select->AddLink->AnchorText = "Create New";

        $this->Select->Template = "{AvailableDropdown} {CancelLink}<br /><br />{AddLink}";

	}

	protected function SetupAddDIVcontrols()
	{

        $this->Add->InnerHTML = "<h2>Add New</h2>";

        $this->Add->CancelLink = new JavascriptLinkControl();
        $this->Add->CancelLink->AnchorText = "Cancel";
	}

    protected function SetupControlJavascript()
    {

        $this->_JS->ShowDisplay->Add($this->Display->Effects->Show);
        $this->_JS->ShowDisplay->Add($this->Select->Effects->Hide);
        $this->_JS->ShowDisplay->Add($this->Add->Effects->Hide);

        $this->_JS->ShowSelect->Add($this->Select->Effects->Show);
        $this->_JS->ShowSelect->Add($this->Display->Effects->Hide);
        $this->_JS->ShowSelect->Add($this->Add->Effects->Hide);

        $this->_JS->ShowAdd->Add($this->Add->Effects->Show);
        $this->_JS->ShowAdd->Add($this->Display->Effects->Hide);
        $this->_JS->ShowAdd->Add($this->Select->Effects->Hide);

        $this->Display->SelectLink->JS->OnClick->AddFunctionCall($this->_JS->ShowSelect);

        $this->Select->AddLink->JS->OnClick->AddFunctionCall($this->_JS->ShowAdd);
        $this->Select->CancelLink->JS->OnClick->AddFunctionCall($this->_JS->ShowDisplay);

        $this->Add->CancelLink->JS->OnClick->AddFunctionCall($this->_JS->ShowSelect);

        parent::SetupControlJavascript();

    }

}
?>
