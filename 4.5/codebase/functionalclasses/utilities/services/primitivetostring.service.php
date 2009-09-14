<?

class PrimitiveToString extends Service
{
	static public function _($Primitive)
	{
		$divColor = "#ccf";

		$className = get_class($Primitive);

		$randomID = rand();
		$anchorID = "{$className}_{$randomID}";

		$returnValue = "<a id=\"{$anchorID}\"></a>";

		$returnValue .= "<div id=\"{$anchorID}_summary\" style=\"border: 0; background-color: {$divColor}; padding: 6px;\">";

		$detailJS = "	document.getElementById('{$anchorID}_summary').style.display = 'none';
		document.getElementById('{$anchorID}_detail').style.display = 'block';";

		$summaryJS = "	document.getElementById('{$anchorID}_detail').style.display = 'none';
		document.getElementById('{$anchorID}_summary').style.display = 'block';";

		$returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$detailJS}\"><b>{$className}</a></b>";

		$returnValue .= "</div>";

		$returnValue .= "<div id=\"{$anchorID}_detail\" style=\"border: 0; background-color: {$divColor}; padding: 6px; display:none;\">";

		$returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">{$className}</h1>";

		$returnValue .= self::OutputProperties($Primitive);

		$returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$summaryJS}\">Close</a>";

		$returnValue .= "</div>";

		return $returnValue;
	}

	protected static function OutputProperties($Primitive)
	{

				$returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

				foreach ($Primitive->AllProperties as $name=>$value)
				{
					$name = self::FormatPropertyName($name);
					$value = self::FormatPropertyValue($value);

					$returnValue .= "<li style=\"border: 1px solid #fcc; margin: 2px; padding: 4px; background-color: #ffc;\">";
					$returnValue .= "<b>{$name}:</b> {$value}";
					$returnValue .= "</li>";
				}

				$returnValue .= "</ul>";

		return $returnValue;
	}

	protected static function FormatPropertyName($Name)
	{
		$returnValue = ucfirst(substr($Name, 1));

		return $returnValue;
	}

	protected static function FormatPropertyValue($Value)
	{

		if (is_set($Value))
		{
			if (is_numeric($Value))
			{
				$returnValue = "{$Value}";
			}
			elseif (is_string($Value))
			{
				$returnValue = "\"{$Value}\"";
			}
			else
			{
				$returnValue = $Value;
			}
		}
		else
		{
			$returnValue = "<i>null</i>";
		}

		return $returnValue;
	}

}
