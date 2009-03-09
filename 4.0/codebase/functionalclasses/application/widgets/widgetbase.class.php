<?php
/*
Widget Base Class File

@package Sandstone
@subpackage Application
*/


class WidgetBase extends Renderable
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_template->Filename = strtolower(get_class($this));
        
    }

}
?>