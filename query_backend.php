<?php
	include("bin/load_config.php");
	
	function ret_res($message)
	{
		echo json_encode(array("result"=>$message));
		die();	
	}
	
	if($_POST["func"]=="testConnection")
	{
		// Turn off all error reporting
		error_reporting(0);
		$selected_blade = $_POST["blade"];
		$blade = $Blade_Map[$selected_blade];
		$host = (string)$blade["host"].":".(string)$blade["port"];
		$user = (string)$blade["user"];
		$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
		$database = (string)$blade["db"];
		 
		if(isset($pass) && $pass!=""){
			$linkID = mysql_connect($host, $user, $pass) or ret_res("Could not connect to host.");
		} else {
			$linkID = mysql_connect($host, $user) or ret_res("Could not connect to host.");
		} 
		mysql_select_db($database, $linkID) or ret_res("Could not find database.");
		mysql_close($linkID);
		ret_res($selected_blade." connected successfuly!");
	}
?>
