<?php
	require_once("xml2array.php");
	
	$xml_filename = "config/config.xml";
	$arr = xmlstr_to_array($xml_filename);
	
	$Blades = $arr["blades"]["blade"];
	$DataTables = $arr["data-tables"];
	$DEFAULT_BLADE;
	$AS_INFO_DEFAULT_BLADE;
	
	foreach($Blades as $blade)
	{
		$name = $blade["@attributes"]["name"];
		$Blade_Map[$name] = $blade;
		if(isset($blade["@attributes"]["default"]) && ($blade["@attributes"]["default"] == "true"))
			$DEFAULT_BLADE = $name;
		if($blade["db"]==$DataTables["as-info"]["schema"])
			$AS_INFO_DEFAULT_BLADE = $name;
	}

?>