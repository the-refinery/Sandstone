<?php
/*
Tags Control Class File

@package Sandstone
@subpackage Application
*/

NameSpace::Using("Sandstone.Tag");

class TagsControl extends BaseControl
{

	protected $_entity;

	protected $_isReadOnly;
	protected $_isAdminUser;

	protected $_linkBase;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('tags_general');
		$this->_bodyStyle->AddClass('tags_body');

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;

		$this->_template->FileName = "tags";
	}

	/*
	Entity property

	@return EntityBase
	@param EntityBase $Value
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
		}
		else
		{
			$this->_entity = null;
		}

		$this->TagList->Data = $this->_entity->Tags;

	}

	/*
	IsReadOnly property

	@return boolean
	@param boolean $Value
	*/
	public function getIsReadOnly()
	{
		return $this->_isReadOnly;
	}

	public function setIsReadOnly($Value)
	{
		$this->_isReadOnly = $Value;
	}

	/*
	IsAdminUser property

	@return boolean
	@param boolean $Value
	*/
	public function getIsAdminUser()
	{
		return $this->_isAdminUser;
	}

	public function setIsAdminUser($Value)
	{
		$this->_isAdminUser = $Value;
	}

	/*
	LinkBase property

	@return string
	@param string $Value
	 */
	public function getLinkBase()
	{
		return $this->_linkBase;
	}

	public function setLinkBase($Value)
	{
		$this->_linkBase = $Value;

		if (strlen($this->_linkBase) > 0)
		{
			if (substr($this->_linkBase, -1, 1) != "/")
			{
				$this->_linkBase .= "/";
			}
		}
	}

    protected function SetupControls()
	{
		parent::SetupControls();

		$this->TagList = new RepeaterControl();
		$this->TagList->SetCallback($this, "TagListCallBack");
		$this->TagList->ItemIDsuffixFormat = "{TagID}";

		$this->NewTagText = new TitleTextBoxControl();
		$this->NewTagText->LabelText = "Tag";
        $this->NewTagText->AddValidator("GenericValidator","IsRequired");

		$this->NewTagSubmit = new JavascriptButtonControl();
		$this->NewTagSubmit->LabelText = "Add";

		$this->DeleteTagID = new HiddenControl();
	}

	public function TagListCallBack($CurrentElement, $Template)
	{

		//Determine which template we need to use
		if (strlen($this->_linkBase) > 0)
		{
			//Each tag is a link
            if ($this->_isAdminUser)
			{
				$Template->FileName = "taglist_taglink_admin";
			}
			else
			{
				$Template->FileName = "taglist_taglink";
			}

			$tagTextURL = urlencode($CurrentElement->Text);
			$Template->TagLink = $this->_linkBase . $tagTextURL;
		}
		else
		{
			//No link for each tag
			if ($this->_isAdminUser)
			{
				$Template->FileName = "taglist_tag_admin";
			}
			else
			{
				$Template->FileName = "taglist_tag";
			}

		}
	}

    public function AJAX_AddTag($Processor)
    {
        $Processor->Template->ControlName = $this->TagList->Name;
        $Processor->Template->ParentContainerName = $this->Name;

        $isValid = $this->NewTagText->Validate();

        if ($isValid)
        {
            $Processor->Template->FileName = "tags_addtag_success";

            //Get the correctly formatted text
			$targetTagText = Tag::FormatTextForTag($this->NewTagText->Value);

			//Add the tag
			$this->_entity->AddTag($targetTagText);

			//Now load a new copy of this tag so we have it's ID
			$tempTag = new Tag();
			$tempTag->Text = $targetTagText;

			$newTagItem = new Renderable();
			$newTagItem->Template->ControlName = "{$this->TagList->Name}_Item_{$tempTag->TagID}";
			$newTagItem->Template->RequestFileType = "htm";
			$newTagItem->Template->Element = $tempTag;

			$this->TagListCallBack($tempTag, $newTagItem->Template);

			$Processor->Template->NewTagItem = $newTagItem->Render();
			$Processor->Template->NewTagID = $tempTag->TagID;

        }
        else
        {
            $Processor->Template->FileName = "tags_addtag_failure";
            $Processor->Template->ValidationMessage = $this->NewTagText->ValidationMessage;
        }

    }

	public function AJAX_DeleteTag($Processor)
	{
        $Processor->Template->ControlName = $this->TagList->Name;
        $Processor->Template->ParentContainerName = $this->Name;

		if (is_set($Processor->EventParameters['targettagid']))
		{
			$targetTagID = $Processor->EventParameters['targettagid'];

			//Get the tag we need to remove
			$tempTag = new Tag();
			$tempTag->LoadByID($targetTagID);

			if ($tempTag->IsLoaded)
			{
				$this->_entity->RemoveTag($tempTag->Text);
			}

			if (count($this->_entity->Tags) > 0)
			{
				$Processor->Template->FileName = "tags_deletetag_success";
			}
			else
			{
				$Processor->Template->FileName = "tags_deletetag_success_lasttag";
			}

			$Processor->Template->TargetTagID = $targetTagID;
		}
	}

}

?>
