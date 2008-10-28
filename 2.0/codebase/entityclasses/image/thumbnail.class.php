<?php
/**
 * Thumbnail Class
 * 
 * @package Sandstone
 * @subpackage Image
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

/**
 * This class takes a given source image, and new dimensions.  Then it returns a 
 * server generated thumbnail image.
 */
class Thumbnail extends Module
{
	/**
	 * The filename referencing the source file.
	 *
	 * @var string
	 */
	protected $_originalImage;
	
	/**
	 * The new horizontal dimension
	 *
	 * @var integer
	 */
	protected $_x;
	
	/**
	 * The new vertical dimension
	 *
	 * @var integer
	 */
	protected $_y;
	
	/**
	 * The original binary image data of the source file.
	 *
	 * @var binary
	 */
	protected $_image;
	
	/**
	 * The binary image data of the new thumbnail image.
	 *
	 * @var binary
	 */
	protected $_thumbnail;
	
	/**
	 * The original horizontal dimension of the source image.
	 *
	 * @var integer
	 */
	protected $_oldX;
	
	/**
	 * The original vertical dimension of the source image.
	 *
	 * @var integer
	 */
	protected $_oldY;
	
	/**
	 * The type of image the source and thumbnails images are.
	 * GIF, JPEG, or PNG
	 *
	 * @var string
	 */
	protected $_filetype;
	
	/**
	 * The prefix of the filename needed to find the source image
	 *
	 * @var unknown_type
	 */
	protected $_filePrefix;
	
	/**
	 * Constructor populates the image Property with an image object
	 */
	public function __construct($ImageID)
	{
		$this->_originalImage = new Image($ImageID);
		$this->CheckSourceImageExists();
	}
	
	/**
	 * Filename property
	 *
	 * @return string
	 */
	public function getOriginalImage()
	{
		return $this->_originalImage;
	}
	
	/**
	 * Filename property
	 *
	 * @param string $Value
	 */
	public function setOriginalImage($Value)
	{
		$this->_originalImage = $Value;
	}
	
	/**
	 * Horizontal Dimension Property
	 *
	 * @return integer
	 */
	public function getX()
	{
		return $this->_x;
	}
	
	/**
	 * Horizontal Dimension Property
	 *
	 * @param Integer $Value
	 */
	public function setX($Value)
	{
		$this->_x = $Value;
	}
	
	/**
	 * Vertical Dimension property
	 *
	 * @return integer
	 */
	public function getY()
	{
		return $this->_y;
	}
	
	/**
	 * Vertical Dimension Property
	 *
	 * @param integer $Value
	 */
	public function setY($Value)
	{
		$this->_y = $Value;
	}
	
	/**
	 * Original source Image property
	 *
	 * @return binary
	 */
	public function getImage()
	{
		return $this->_image;
	}
	
	/**
	 * Newly generated Thumbnail Image Property
	 *
	 * @return binary
	 */
	public function getThumbnail()
	{
		return $this->_thumbnail;
	}
	
	/**
	 * X dimension Property of the source file
	 *
	 * @return integer
	 */
	public function getOldX()
	{
		return $this->_oldX;
	}
	
	/**
	 * Y dimension Property of the source file
	 *
	 * @return unknown
	 */
	public function getOldY()
	{
		return $this->_oldY;
	}
	
	/**
	 * Filetype Property
	 *
	 * @return string
	 */
	public function getFiletype()
	{
		return $this->_filetype; 
	}

	/**
	 * Populates the Image property binary data from the source file.
	 * 
	 * Also populates the OldX and OldY properties
	 *
	 * @param string $Filename
	 */
	public function Open() 
	{
		$imageinfo = getimagesize($this->_originalImage->File->URL, $imageinfo);

		// Get original dimensions
		$this->_oldX = $imageinfo[0];
		$this->_oldY = $imageinfo[1];
					
		// Check image type, GIF, JPEG, or PNG
		// and load image accordingly
		switch ($imageinfo[2]) 
		{
			case "1": 
				$this->_image = imagecreatefromgif($this->_originalImage->File->URL); 
				$this->_filetype = "GIF";
				break;
			case "2": 
				$this->_image = imagecreatefromjpeg($this->_originalImage->File->URL); 
				$this->_filetype = "JPEG";
				break;
			case "3": 
				$this->_image = imagecreatefrompng($this->_originalImage->File->URL); 
				$this->_filetype = "PNG";
				break;
		}
	}

	/**
	 * Create a thumbnail based on the dimensions specified in the X and Y properties.
	 * The thumbnail is automatically scaled based on the input dimension.
	 * 
	 * - If both X and Y are inputted, the thumbnail is created using those dimensions
	 * - If only X is set, then scale Y to stay proportionate
	 * - If only Y is set, then scale X to stay proportionate
	 *
	 */
	public function Generate() 
	{
		// BOTH X and Y specified, use specified dimensions
		if ($this->_x > 0 && $this->_y > 0) 
		{
			$newX = $this->_x;
			$newY = $this->_y;
		} 
		// Only X was specified, scale Y
		elseif ($this->_x > 0 && $this->_x != "") 
		{
			$newX = $this->_x;
			$newY = ($this->_x / $this->_oldX) * $this->_oldY;
		} 
		// Only Y was specified, scale X
		else 
		{
			$newX = ($this->_y / $this->_oldY) * $this->_oldX;
			$newY = $this->_y;
		}
	
		$newX = round($newX);
		$newY = round($newY);
		
		// Create new Image, and fill with white
		$this->_thumbnail = imagecreatetruecolor($newX, $newY);
		$white = imagecolorallocate($this->_thumbnail, 255, 255, 255);
		imagefill($this->_thumbnail,0,0,$white);

		// Resample original image into the thumbnail
		imagecopyresampled($this->_thumbnail, $this->_image, 0, 0, 0, 0, $newX, $newY, $this->_oldX, $this->_oldY);
						
		$this->SaveThumbnail($newX, $newY);
	}
	
	public function SaveThumbnail($x, $y)
	{
		$License = Application::License();
		
		// Generate Filename
		$filename = "thumb_" . $x . "-" . $y . "-" . $this->_originalImage->File->PhysicalFileName;

		if (file_exists(Application::License()->ImagesPath . $filename) == false)
		{
			// Create the new file
			$fp = fopen(Application::License()->ImagesPath . $filename, "w");

			fwrite($fp, $this->_thumbnail);
			fclose($fp);
		}
		
		if ($this->_originalImage->FindThumbnail($this->_x, $this->_y) == false)
		{
			// Add thumbnail to image
			$NewThumbnail = new ImageThumbnail();
			$NewThumbnail->Height = $y;
			$NewThumbnail->Width = $x;
		
			$tempFile = new File();
			$tempFile->FileName = "thumb_" . $x . "-" . $y . "-" . $this->_originalImage->File->FileName;
			$tempFile->URL = Application::License()->ImagesPath . $filename;
			$tempFile->Save();
		
			$NewThumbnail->File = $tempFile;
		
			$this->_originalImage->AddThumbnail($NewThumbnail);
		}
		
		switch ($this->_filetype)
		{
			case "GIF":
				header("Content-type: image/gif;");
				$this->ImageGif(Application::License()->ImagesPath . $filename);
				break;
				
			case "JPEG":
				header("Content-type: image/jpeg;");
				$this->ImageJpeg(Application::License()->ImagesPath . $filename,100);
				break;
				
			case "PNG":
				header("Content-type: image/png;");
				$this->ImagePng(Application::License()->ImagesPath . $filename);
				break;
		}	
	}

	/**
	 * Routes the render process to the correct display routine.  ImageGif(), ImageJpeg(), or ImagePng()
	 *
	 * @param integer $Quality
	 */
	public function ShowImage($Quality = 80)
	{
		$License = Application::License();
		
		// Check for existing thumbnail with these dimensions.
		$ExistingThumbnail = $this->_originalImage->FindThumbnail($this->_x, $this->_y);
		if (is_set($ExistingThumbnail) && file_exists($ExistingThumbnail->File->URL))
		{
			switch ($this->_filetype)
			{
				case "GIF":
					$this->_thumbnail = imagecreatefromgif($ExistingThumbnail->File->URL);
					break;

				case "JPEG":
					$this->_thumbnail = imagecreatefromjpeg($ExistingThumbnail->File->URL); 
					break;

				case "PNG": 
					$this->_thumbnail = imagecreatefrompng($ExistingThumbnail->File->URL);
					break;
			}

		}
		else
		{
			$this->Generate();
		}

		switch ($this->_filetype)
		{
			case "GIF":
				header("Content-type: image/gif;");
				$this->ImageGif();
				break;
				
			case "JPEG":
				//header("Content-type: image/jpeg;");
				$this->ImageJpeg("",$Quality);
				break;
				
			case "PNG":
				header("Content-type: image/png;");
				$this->ImagePng();
				break;
		}
	}
	
	/**
	 * Outputs a gif image to the browser
	 *
	 * @param string $Filename
	 */
	public function ImageGif($Filename = "") 
	{
		imagetruecolortopalette($this->_thumbnail, 0, 256);
		
		if ($Filename == "") 
		{
			imagegif($this->_thumbnail);
		} 
		else 
		{
			imagegif($this->_thumbnail, $Filename);
		}
	}

	/**
	 * Outputs a jpeg image to the browser
	 *
	 * @param string $Filename
	 * @param integer $Quality
	 */
	public function ImageJpeg($Filename = "", $Quality = 80) 
	{
		imagejpeg($this->_thumbnail, $Filename, $Quality);
	}

	/**
	 * Outputs a png image to the browser
	 *
	 * @param string $Filename
	 */
	public function ImagePng($Filename = "") 
	{
		if ($filename == "") 
		{
			imagepng($this->_thumbnail);
		} 
		else 
		{
			imagepng($this->_thumbnail, $Filename);
		}
	}

	public function CheckSourceImageExists()
	{	
		// VALIDATE FILENAME
		if (! file_exists($this->OriginalImage->File->URL))
		{
			throw new FileNotFoundException("File: {$this->OriginalImage->File->URL} was not found");
		}
	}
}

?>