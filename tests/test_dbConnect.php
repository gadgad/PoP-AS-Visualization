<?php
	include_once("bin/load_config.php");
	include ("bin/DBConnection.php");
	
	$blade = $Blade_Map['B4'];
	$host = (string)$blade["host"];
	$port = (int)$blade["port"];
	$hostNport = (string)$blade["host"].":".(string)$blade["port"];
	$user = (string)$blade["user"];
	$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
	$database = (string)$blade["db"];
	
    $dbc = new DBConnection($host, $user, $pass, $database, $port, 5);
	
	$dbc->close();
?>