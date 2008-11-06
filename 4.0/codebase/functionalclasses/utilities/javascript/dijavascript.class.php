<?php

class DIJavascript extends Scriptaculous
{
    public function Alert($Message)
    {
        return "alert('{$Message}');";
    }
}

?>