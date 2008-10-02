<?php
/*
Image Control Class File

@package Sandstone
@subpackage Application
*/

class ImageControl extends BaseControl
{

	protected $_image;
	protected $_thumbnailWidth;
	protected $_thumbnailHeight;
	protected $_thumbnailMaxSize;
	protected $_relContent;
	protected $_alternateText;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('image_general');
		$this->_bodyStyle->AddClass('image_body');

        //We don't use the wrapper and message stuff.
        $this->_template->IsMasterLayoutUsed = false;
	}

	/*
	Image property

	@return Image
	@param Image $Value
	 */
	public function getImage()
	{
		return $this->_image;
	}

	public function setImage($Value)
	{
		if ($Value instanceof Image && $Value->IsLoaded)
		{
			$this->_image = $Value;
		}
		else
		{
			$this->_image = null;
		}
	}

	/*
	ThumbnailWidth property

	@return integer
	@param integer $Value
	 */
	public function getThumbnailWidth()
	{
		return $this->_thumbnailWidth;
	}

	public function setThumbnailWidth($Value)
	{
		$this->_thumbnailWidth = $Value;
	}

	/*
	ThumbnailHeight property

	@return integer
	@param integer $Value
	 */
	public function getThumbnailHeight()
	{
		return $this->_thumbnailHeight;
	}

	public function setThumbnailHeight($Value)
	{
		$this->_thumbnailHeight = $Value;
	}

	/*
	ThumbnailMaxSize property

	@return integer
	@param integer $Value
	 */
	public function getThumbnailMaxSize()
	{
		return $this->_thumbnailMaxSize;
	}

	public function setThumbnailMaxSize($Value)
	{
		$this->_thumbnailMaxSize = $Value;
	}

	/*
	RelContent property

	@return string
	@param string $Value
	 */
	public function getRelContent()
	{
		return $this->_relContent;
	}

	public function setRelContent($Value)
	{
		$this->_relContent = $Value;
	}

	/*
	AltContent property

	@return string
	@param string $Value
	 */
	public function getAlternateText()
	{
		return $this->_alternateText;
	}

	public function setAlternateText($Value)
	{
		$this->_alternateText = $Value;
	}

	public function getIsThumbnailMode()
	{

		if (is_set($this->_thumbnailHeight) || is_set($this->_thumbnailWidth) || is_set($this->_thumbnailMaxSize))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function Render()
	{

		//Only render if we have an image
		if (is_set($this->_image))
		{

			//Determine what file version we need
			$targetFileVersion = $this->CalculateTargetFileVersionAndSetHeightWidth();

			$this->_template->ImageURL = $this->GenerateImageURL($targetFileVersion);

			if (is_set($this->_alternateText))
			{
				$this->_template->AlternateText = $this->_alternateText;
			}
			else
			{
				$this->_template->AlternateText = $this->_image->AlternateText;
			}

			$this->_template->RelContent = $this->_relContent;

	        //Now call our parent's render method to generate the actual output.
	        $returnValue =  parent::Render();

		}

        return $returnValue;
	}

	protected function CalculateTargetFileVersionAndSetHeightWidth()
	{
		//Are we going for the main file or a thumbnail?
		if ($this->IsThumbnailMode)
		{

			//What method are we using to find the thumbnail?
			if (is_set($this->_thumbnailMaxSize))
			{
				//By Max Size
				$targetThumbnail = $this->_image->FindMaxSizeThumbnail($this->_thumbnailMaxSize);
			}
			else
			{
				//By specified size
				$targetThumbnail = $this->_image->FindThumbnail($this->_thumbnailHeight, $this->_thumbnailWidth);
			}

			//Return the current version of the thumbnail.
			$returnValue = $targetThumbnail->File->CurrentVersion;

			//Set the height & width
			$this->_template->Height = $targetThumbnail->Height;
			$this->_template->Width = $targetThumbnail->Width;
		}
		else
		{
			//Full size image
			$returnValue = $this->_image->File->CurrentVersion;

			//Set the height & width
			$this->_template->Height = $this->_image->Height;
			$this->_template->Width = $this->_image->Width;

		}

		return $returnValue;
	}

	protected function GenerateImageURL($FileVersion)
	{
    	//Are we in AWS mode?
		if (Application::Registry()->AWSisActive && Application::Registry()->IsAWShidden == false)
		{
			//Generate the AWS S3 URL
			$s3conn = new S3;
			$returnValue = $s3conn->GenerateFileURL($FileVersion->FileSpec);
		}
		else
		{
			//Build a local URL via our routing rules
			$parameters['imageid'] = $this->_image->ImageID;
			$parameters['filename'] = $this->_image->File->FileName;


			if ($this->IsThumbnailMode)
			{
				$parameters['height'] = $this->_template->Height;
				$parameters['width'] = $this->_template->Width;

				$returnValue = Routing::BuildURLbyRule("thumbnail", $parameters);

			}
			else
			{
				//Full Image Mode
				$returnValue = Routing::BuildURLbyRule("image", $parameters);
			}
		}

		return $returnValue;
	}
}
?>
