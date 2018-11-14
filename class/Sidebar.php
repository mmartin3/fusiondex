<?php

/**
 * Fusion Dex sidebar rendering
 */
class Sidebar {
	var $html;
	
	function __construct(&$data, $fusion)
	{
		$this->html = "";
		
		foreach ( $data as $obj )
		{
			$icon = "<img src='icons/{$obj->index}.png' class='ficon' />";
			$active = $obj->dexno == $fusion->dexno ? (" class='active ".strtolower($obj->Type1)."'") : "";
			$active_icon = $active ? " $icon " : "";
			$this->html .= "<a href='dex.php?no=$obj->index'><li$active>$obj->dexno $obj->Name$active_icon</li></a>";
		}
	}
	
	function render()
	{
		return $this->html;
	}
}
