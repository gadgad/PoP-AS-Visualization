<?php
	require_once("xml2array.php");
	
	$xml_filename = "config/config.xml";
	$os_specific_xml = 'config/'.PHP_OS.'_config.xml';
	if(file_exists($os_specific_xml))
		$xml_filename = $os_specific_xml;
	
	$config_arr = xmlstr_to_array($xml_filename);
	
	$ConfigParams = $config_arr["config-parameters"]["parameter"];
	$API_Keys = $config_arr["ge-api-keys"]["key"];   
	$Blades = $config_arr["blades"]["blade"];
	$DataTables = $config_arr["data-tables"];
	$DEFAULT_BLADE;
	$AS_INFO_DEFAULT_BLADE;
	$API_KEY;
	
	$isCLI = ( php_sapi_name() == 'cli' );
	
	foreach($Blades as $blade)
	{
		$name = $blade["@attributes"]["name"];
		$Blade_Map[$name] = $blade;
		if(isset($blade["@attributes"]["default"]) && ($blade["@attributes"]["default"] == "true"))
			$DEFAULT_BLADE = $name;
		if($blade["db"]==$DataTables["as-info"]["schema"])
			$AS_INFO_DEFAULT_BLADE = $name;
	}
	
	if(!$isCLI){
		foreach($API_Keys as $key){
			if(stristr($key["domain"],$_SERVER["SERVER_NAME"]))
				$API_KEY = $key["string"];
		}
	}
	
	foreach($ConfigParams as $param){
		define($param["name"],is_numeric($param["value"])? floatval($param["value"]):(string)$param["value"]);
	}

?>