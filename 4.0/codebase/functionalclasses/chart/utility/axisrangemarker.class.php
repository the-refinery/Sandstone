<?php

class AxisRangeMarker extends Module
{

	const HORIZONTAL_DIRECTION = "r";
	const VERTICAL_DIRECTION = "R";

	protected $_start;
	protected $_end;
    protected $_direction;
	protected $_color;

	public function __construct($Start, $End, $Direction = AxisRangeMarker::HORIZONTAL_DIRECTION, $Color)
	{

        $this->_start = $Start;
        $this->_end = $End;
        $this->_direction = $Direction;
		$this->_color = $Color;
	}


    public function GenerateQueryParameterData()
    {
        $returnValue = "{$this->_direction},{$this->_color},0,{$this->_start},{$this->_end}";        
        
        return $returnValue;
    }
}
?>