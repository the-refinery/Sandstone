<?php
/**
 * Helper Class Extension File
 * 
 * @package Sandstone
 * @subpackage Smarty
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */
SandstoneNamespace::Using("Sandstone.GUI");
SandstoneNamespace::Using("Sandstone.Utilities.Javascript");
SandstoneNamespace::Using("Sandstone.Markdown");


class SmartyHelper extends Module
{
	protected $_scriptaculous;
	protected $_prototype;
	
	public function __construct()
	{
		$this->_scriptaculous = new Scriptaculous();
		$this->_prototype = new Prototype();
	}
	
	public function getScript()
	{
		return $this->_scriptaculous;
	}
	
	public function getPrototype()
	{
		return $this->_prototype;
	}

	

	// the url is expressed in the unlimited function parameters
	// LinkTo("User","Load",32)
	// This is to compensate for being unable to (elegantly) contantinate
	// Strings in smarty within function arguments.
	public function LinkTo()
	{

		// Check if link is external
		$externalURLCheck = func_get_arg(0);
		if (strtolower(substr($externalURLCheck = func_get_arg(0), 0, 4)) == "http") 
		{
			$URL = $externalURLCheck = func_get_arg(0);
		}
		else
		{		
			for($i = 0; $i < func_num_args(); $i++) 
			{
				$newElements[] = GUI::GetSEOFriendly(func_get_arg($i));
			}
		
			$URL = implode("/", $newElements);
		}
		
		$href = "href=\"{$URL}\"";
		
		$link = "<a {$href}>";
		
		echo $link;
	}
	
	public function Image($ImageID, $CSSClass = "", $FadeInOnLoad = false)
	{
		$id = "id=\"Image_{$ImageID}\"";
		$source = "src=\"Image/{$ImageID}\"";
		$class = "class=\"{$CSSClass}\"";
		
		if ($FadeInOnLoad == true)
		{
			$onLoad = "onload=\"new Effect.Appear('Image_{$ImageID}');\"";
		}
		
		echo "<img {$id} {$source} {$class} {$onLoad} />";
	}	
	
	public function MaxSizeThumbnail($ImageID, $MaximumSize, $CSSClass = "", $Lightbox = false, $FadeInOnLoad = false)
	{	
		$id = "id=\"Image_{$ImageID}\"";
		$source = "src=\"Image/Max/{$ImageID}/{$MaximumSize}/\"";
		$class = "class=\"{$CSSClass}\"";
		
		if ($FadeInOnLoad == true)
		{
			$onLoad = "onload=\"new Effect.Appear('FadeIn_{$ImageID}');\"";
			$imageTag = "<div id=\"FadeIn_{$ImageID}\" style=\"display:none;\"><img {$id} {$source} {$class} {$onLoad} /></div>";
		}
		else
		{
			$imageTag = "<img {$id} {$source} {$class} {$onLoad} />";
		}
		
		if ($Lightbox == true)
		{
			echo "<a href=\"Image/{$ImageID}/\" rel=\"lightbox\">";
			echo $imageTag;
			echo "</a>";
		}
		else
		{
			echo $imageTag;
		}
	}	
	
	public function FixedSizeThumbnail($ImageID, $Width, $Height, $CSSClass = "", $Lightbox = false, $FadeInOnLoad = false)
	{	
		$id = "id=\"Image_{$ImageID}\"";
		$source = "src=\"Thumbnail/{$ImageID}/$Width/$Height/\"";
		$class = "class=\"{$CSSClass}\"";
		
		if ($FadeInOnLoad == true)
		{
			$onLoad = "onload=\"new Effect.Appear('FadeIn_{$ImageID}');\"";
			$imageTag = "<div id=\"FadeIn_{$ImageID}\" style=\"display:none;\"><img {$id} {$source} {$class} {$onLoad} /></div>";
		}
		else
		{
			$imageTag = "<img {$id} {$source} {$class} {$onLoad} />";
		}
		
		if ($Lightbox == true)
		{
			echo "<a href=\"Image/{$ImageID}/\" rel=\"lightbox\">";
			echo $imageTag;
			echo "</a>";
		}
		else
		{
			echo $imageTag;
		}
	}	
	
	public function JavascriptLink($OnClickEvent)
	{
		$href = "href=\"javascript:void(0);\"";
		$onclick = "onclick=\"{$OnClickEvent}\"";
		
		$link = "<a {$href} {$onclick} >";
		
		echo $link;
	}
	
	// Render static content from Markdown / HTML file without re-parsing it through Smarty.
	public function StaticContent($File)
	{
		$Content = file_get_contents(Application::License()->TemplateDirectory . "/" . $File);
		
		echo Markdown($Content);
	}
	
	public function FormatCurrency($Value)
	{
		echo "\$" . number_format($Value,2);
	}
	
	public function FormatNumber($Value, $DecimalPlaces = 0)
	{
		echo number_format($Value, $DecimalPlaces);
	}
	
	public function Markdown($Content)
	{
		echo DIMarkdown($Content);
	}

}

?>