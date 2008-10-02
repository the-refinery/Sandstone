<?php

class Prototype extends Module
{
    public function Observe($ElementID, $Event, $ObservingFunction)
    {
        return "Event.observe('{$ElementID}', '{$Event}', {$ObservingFunction});";
    }
}

?>