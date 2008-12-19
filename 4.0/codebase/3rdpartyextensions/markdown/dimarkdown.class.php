<?php
/*
DImarkDown Class File

@package Sandstone
@subpackage 3rdPartyExtenstions
 */

Namespace::Using("Sandstone.Image");
Namespace::Using("Sandstone.File");

class DImarkDown extends Module
{

	protected $_entities;

	public function __construct()
	{
		$this->_entities['file'] = 'download';
	}

	public function Markdown($Value)
	{

		$returnValue = $this->ParseDItags($Value);

		$returnValue = Markdown($returnValue);

		$returnValue = str_replace(Array("\n", "\t"), "", $returnValue);

		return $returnValue;
	}

	protected function ParseDItags($Value)
	{

		$returnValue = $this->ParseImageTags($Value);
		$returnValue = $this->ParseThumbnailTags($returnValue);

		$returnValue = $this->ParseEntityURLs($returnValue);

		return $returnValue;
	}

	protected function ParseImageTags($Value)
	{

		$returnValue = $Value;

		//Image tag = {image:ID}
		$pattern = '/(\{image:)([0-9]+)(\})/';
        preg_match_all($pattern, $Value, $matches, PREG_SET_ORDER);

        foreach ($matches as $tempMatch)
        {
            $tempToken = $tempMatch[0];
            $imageID = strtolower($tempMatch[2]);

			$image = new Image($imageID);

			if ($image->IsLoaded)
			{
				$url = Routing::BuildURLbyEntity($image, "download");
				$imageTag = "<img src='{$url}' width='{$image->Width}' height='{$image->Height}' alt='{$image->AlternateText}'>";
			}
			else
			{
				$imageTag;
			}

            $returnValue = str_replace($tempToken, $imageTag, $returnValue);
		}

		return $returnValue;
	}

	protected function ParseThumbnailTags($Value)
	{
		$returnValue = $Value;

		//Image Thumbnail tag = {file:ID|size|(size)}
		$pattern = '/(\{thumbnail:)([0-9|]+)(\})/';
        preg_match_all($pattern, $Value, $matches, PREG_SET_ORDER);

        foreach ($matches as $tempMatch)
        {
            $tempToken = $tempMatch[0];
            $data = explode("|", ($tempMatch[2]));

            $imageID = $data[0];

            $image = new Image($imageID);

            if ($image->IsLoaded)
            {

				$parameters['imageid'] = $imageID;
				$parameters['filename'] = $image->Filename;

				switch (count($data))
				{
					case 2:
						//Max Size
						$parameters['maxsize'] = $data[1];
						$url = Routing::BuildURLbyRule("maxsizethumbnail", $parameters);

						$heightWidth = null;

						break;

					case 3:
						//Height and Width
						$parameters['width'] = $data[1];
						$parameters['height'] = $data[2];
						$url = Routing::BuildURLbyRule("thumbnail", $parameters);

						$heightWidth = "height='{$data[1]}' width='{$data[2]}'";

						break;

					default:
						$url = null;
						break;
				}

				if (is_set($url))
				{
					$imageTag = "<img src='{$url}' {$heightWidth} alt='{$image->AlternateText}'>";
				}
			}
			else
			{
				$imageTag = null;
			}

            $returnValue = str_replace($tempToken, $imageTag, $returnValue);
		}


		return $returnValue;
	}

	protected function ParseEntityURLs($Value)
	{
		$returnValue = $Value;

		foreach ($this->_entities as $tempName=>$tempAction)
		{
			$returnValue = $this->ParseEntityURL($tempName, $tempAction, $returnValue);
		}

		return $returnValue;
	}

	protected function ParseEntityURL($EntityName, $Action, $Value)
	{
		$returnValue = $Value;

		$pattern = '/(\{' . strtolower($EntityName) . 'url:)([0-9]+)(\})/';
        preg_match_all($pattern, $Value, $matches, PREG_SET_ORDER);

        foreach ($matches as $tempMatch)
        {
            $tempToken = $tempMatch[0];
            $entityID = strtolower($tempMatch[2]);

			$tempEntity = new $EntityName ($entityID);

			if ($tempEntity->IsLoaded)
			{
				$url = Routing::BuildURLbyEntity($tempEntity, $Action);
			}
			else
			{
				$url = null;
			}

            $returnValue = str_replace($tempToken, $url, $returnValue);
		}


		return $returnValue;
	}

}
?>