<?php
/**
 * Tags Control Class File
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

NameSpace::Using("Sandstone.Tag");

class TagsControl extends BaseControl
{

	protected $_entity;

	protected $_isReadOnly;
	protected $_isAdminUser;

	protected $_deleteImage;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('tags_general');
		$this->_bodyStyle->AddClass('tags_body');

		$this->Message->BodyStyle->AddClass('tags_message');
		$this->Label->BodyStyle->AddClass('tags_label');

		//Set this up once, so we don't have to keep rebuilding it.
		$this->_deleteImage = new ImageControl();
		$this->_deleteImage->URL = "images/sandstone/trash.gif";

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;
	}

	/**
	 * Entity property
	 *
	 * @return EntityBase
	 *
	 * @param EntityBase $Value
	 */
	public function getEntity()
	{
		return $this->_entity;
	}

	public function setEntity($Value)
	{
		if ($Value instanceof EntityBase && $Value->IsLoaded)
		{
			$this->_entity = $Value;

			$this->LoadTagsList();
		}
		else
		{
			$this->_entity = null;

			$this->ClearTagsList();
		}
	}

	/**
	 * IsReadOnly property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
	 */
	public function getIsReadOnly()
	{
		return $this->_isReadOnly;
	}

	public function setIsReadOnly($Value)
	{
		$this->_isReadOnly = $Value;

		$this->SetReadOnlyStatus();
	}

	/**
	 * IsAdminUser property
	 *
	 * @return boolean
	 *
	 * @param boolean $Value
	 */
	public function getIsAdminUser()
	{
		return $this->_isAdminUser;
	}

	public function setIsAdminUser($Value)
	{
		$this->_isAdminUser = $Value;

		$this->SetAdminUserStatus();
	}

    protected function SetupControls()
	{
		parent::SetupControls();

		$this->Label->Text = "Tags";

		$this->Tags = new ULcontrol();
		$this->Tags->Effects->Scope= $this->_effects->Scope;

		$this->AddLink = new JavascriptLinkControl();
		$this->AddLink->AnchorText = "Add";
		$this->AddLink->Effects->Scope= $this->_effects->Scope;

		$this->SetupAddDIV();

		$this->SelectedTagID = new HiddenControl();

	}

	protected function SetupControlJavascript()
    {
		//Show Add Form
		$this->_JS->ShowAddForm->Add("\$('{$this->AddTag->NewTagText->Name}').value = \"\";");
		$this->_JS->ShowAddForm->Add($this->AddTag->Effects->BlindDown);

		//Add Link
		$this->AddLink->JS->OnClick->AddFunctionCall($this->_JS->ShowAddForm);

        //Save Button
        $this->AddTag->Save->JS->OnClick->AddControlEvent("TagSave", false, $this);

		//Cancel Link
		$this->AddTag->Cancel->JS->OnClick->Add($this->AddTag->Effects->BlindUp);

		//Delete Tag
        $this->_JS->DeleteTag->Add("\$('{$this->SelectedTagID->Name}').value = TagID;");
		$this->_JS->DeleteTag->AddControlEvent("TagDelete", false, $this);
		$this->_JS->DeleteTag->AddParameter("TagID");

	}

	protected function SetupAddDIV()
	{
		$this->AddTag = new DIVcontrol();
		$this->AddTag->BodyStyle->AddStyle("display:none");

		$this->AddTag->NewTagText = new TextBoxControl();
		$this->AddTag->NewTagText->Label->Text = "Add Tag";
		$this->AddTag->NewTagText->AddValidator("GenericValidator","IsRequired");
		$this->AddTag->NewTagText->Effects->Scope= $this->_effects->Scope;

		$this->AddTag->Save = new JavascriptButtonControl();
		$this->AddTag->Save->Label->Text = "Save";
		$this->AddTag->Save->Effects->Scope= $this->_effects->Scope;

		$this->AddTag->Cancel = new JavascriptLinkControl();
		$this->AddTag->Cancel->AnchorText = "Cancel";
		$this->AddTag->Cancel->Effects->Scope= $this->_effects->Scope;

	}

	protected function LoadTagsList()
	{

		if (is_set($this->_entity))
		{

			$this->Tags->ClearItems();

			foreach ($this->_entity->Tags->Tags as $tempTag)
			{
				$tagLIid = $tempTag->TagID;

				$tagTextURL = urlencode($tempTag->Text);
				$template = "<a href=\"tags/{$tagTextURL}/\">{$tempTag->Text}</a>";

				$this->Tags->AddItem($tagLIid, "");

				if ($this->_isAdminUser)
				{
					$this->Tags->$tagLIid->DeleteLink = new JavascriptLinkControl();
					$this->Tags->$tagLIid->DeleteLink->AnchorText = "Delete";
					$this->Tags->$tagLIid->DeleteLink->LinkImage = $this->_deleteImage;
					$this->Tags->$tagLIid->DeleteLink->JS->OnClick->AddFunctionCall($this->_JS->DeleteTag, Array($tempTag->TagID));

					$template .= " {DeleteLink}";

				}

				$this->Tags->$tagLIid->Template = $template;
				$this->Tags->$tagLIid->Effects->Scope= $this->_effects->Scope;
			}
		}
	}

	protected function ClearTagsList()
	{
		$this->Tags->ClearItems();
	}

    protected function SetReadOnlyStatus()
    {
		if ($this->_isReadOnly)
		{
			//Make sure we aren't in Admin Mode
			$this->_isAdminUser = false;

			$this->AddLink->IsRendered = false;
			$this->AddTag->IsRendered = false;
		}
		else
		{
			$this->AddLink->IsRendered = true;
			$this->AddTag->IsRendered = true;
		}

		$this->LoadTagsList();

    }

    protected function SetAdminUserStatus()
    {
		//Admin User = true is not compatible with ReadOnly = true
		if ($this->_isAdminUser)
		{
			//Make sure we aren't in ReadOnly mode
			$this->_isReadOnly = false;
		}

		$this->LoadTagsList();
    }

    protected function TagSave_Handler($EventParameters)
    {
        $returnValue = new EventResults();

		//Make sure the control validates
		$isValid = $this->AddTag->NewTagText->Validate();

		if ($isValid)
		{
			//Save the tag
			$actualTagText = $this->_entity->AddTag($this->AddTag->NewTagText->Value);
			$returnValue->Value = $this->_entity->Save();

			//Refresh the list
			$this->LoadTagsList();

			$newTagLIname = $this->_entity->Tags->Tags[$actualTagText]->TagID;
			$this->Tags->$newTagLIname->BodyStyle->AddStyle("display:none;");

			echo $this->Tags->Effects->InnerHTMLblock;

            //Clear any Validation Message
			echo $this->AddTag->NewTagText->ValidationJavascript;

            //Hide the Add Message DIV and display the list div
            echo $this->AddTag->Effects->BlindUpBlock;

            //Show the new Tag
			echo $this->Tags->$newTagLIname->Effects->BlindDownBlock;
			echo $this->Tags->$newTagLIname->Effects->HighlightBlock;

		}
        else
        {
            //Failed Validation

            //Return Validation Message
            echo $this->AddTag->NewTagText->ValidationJavascript;

            $returnValue->Value = false;
        }

        $returnValue->Complete();

        return $returnValue;
    }

    protected function TagDelete_Handler($EventParameters)
    {
		$returnValue = new EventResults();

		//Get the tag we need to remove
		$tempTag = new Tag();
		$tempTag->LoadByID($this->SelectedTagID->Value);

		if ($tempTag->IsLoaded)
		{
			$oldTagLIname = $this->_entity->Tags->Tags[$tempTag->Text]->TagID;

			$this->_entity->RemoveTag($tempTag->Text);
			$this->_entity->Save();

            //Hide the old Tag
			echo $this->Tags->$oldTagLIname->Effects->BlindUpBlock;

			$returnValue->Value = true;
		}
		else
		{
			$returnValue->Value = false;
		}

        $returnValue->Complete();

        return $returnValue;

    }

}

?>
