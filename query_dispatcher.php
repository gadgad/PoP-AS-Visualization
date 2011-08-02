<?php
	require("xml2array.php");
	
	$xml_filename = "config\config.xml";
	$arr = xmlstr_to_array($xml_filename);
	
	$Blades = $arr["blades"]["blade"];
	$DataTables = $arr["data-tables"];
	
	/*
	foreach($Blades as $blade)
	{
		echo "name = ".$blade["@attributes"]["name"]."</BR>";
		foreach($blade as $key=>$value)
		{
			echo "$key = $value</BR>";
		}
		echo "</BR>";
	}
	echo "</BR>";
	 * *
	 */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>query dispatcher</title>
		<meta name="author" content="Gadi">
		<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
		<style type="text/css"></style>
	</head>
	<body>
		
		<input type="button" id="testConnection" value="test connection" />
		<div id="myContainer"></div>
	</body>
</html>
