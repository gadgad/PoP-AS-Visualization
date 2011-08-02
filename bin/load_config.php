<?php
	require("xml2array.php");
	
	$xml_filename = "config\config.xml";
	$arr = xmlstr_to_array($xml_filename);
	
	$Blades = $arr["blades"]["blade"];
	$DataTables = $arr["data-tables"];
	
	
	foreach($Blades as $blade)
	{
		$Blade_Map[$blade["@attributes"]["name"]] = $blade;
	}

?>