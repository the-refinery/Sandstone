<?php
/*
Widget Control Class File

@package Sandstone
@subpackage Application
*/

class WidgetControl extends BaseControl
{
    
    protected $_widget;
    
    
    public function getWidget()
    {
        return $this->_widget;
    }
    
    public function setWidget($Value)
    {
        if ($Value instanceof WidgetBase)
        {
            $this->_widget = $Value;
        }
        else
        {
            $this->_widget = null;
        }
    }
    
    public function Render()
    {
        if (is_set($this->_widget))
        {
            $this->_widget->Template->RequestFileType = $this->_template->RequestFileType;
            $returnValue = $this->_widget->Render();
        }
        
        if (is_set($returnValue) == false || $returnValue == "")
        {
            $returnValue = parent::Render();           
        }
        
        return $returnValue;
    }
}
?>