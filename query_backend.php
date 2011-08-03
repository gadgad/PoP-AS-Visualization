<?php
	include("bin/load_config.php");
	
	////// globals
	if(!isset($_POST["blade"]))
	{
		echo "yes..how can I help you?";
		die();
	}
	
	$selected_blade = $_POST["blade"];
	$blade = $Blade_Map[$selected_blade];
	$host = (string)$blade["host"];
	$port = (int)$blade["port"];
	$hostNport = (string)$blade["host"].":".(string)$blade["port"];
	$user = (string)$blade["user"];
	$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
	$database = (string)$blade["db"];
	
	function ret_res($message)
	{
		echo json_encode(array("result"=>$message));
		die();	
	}
	
	if($_POST["func"]=="testConnection")
	{
		// Turn off all error reporting
		error_reporting(0);
		
		if(isset($pass) && $pass!=""){
			$linkID = mysql_connect($hostNport, $user, $pass) or ret_res("Could not connect to host.");
		} else {
			$linkID = mysql_connect($hostNport, $user) or ret_res("Could not connect to host.");
		} 
		mysql_select_db($database, $linkID) or ret_res("Could not find database: ".$database);
		mysql_close($linkID);
		ret_res($selected_blade." connected successfuly!");
	}
	

	if($_POST["func"]=="getProcessListXML")
	{
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
		if ($mysqli->connect_error) {
 		   die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
		
		$query = "show processlist"; 
		$result = $mysqli->query($query) or die("Data not found."); 

		$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
		$xml_output .= "<DATA>\n"; 
		
		$num = $result->num_rows;
		for($x = 0 ; $x < $num ; $x++){ 
		    $row = $result->fetch_assoc();
		    $xml_output .= "\t<ROW>\n"; 
			foreach($row as $key => $value){
				$xml_output .= "\t\t<".$key.">" .$value . "</".$key.">\n";
			}
		    $xml_output .= "\t</ROW>\n"; 
		} 
		$xml_output .= "</DATA>"; 
		
		if ($num != 0) {
			header('Content-Type: application/xml; charset=ISO-8859-1');
			echo $xml_output;
			
		}
	}

?>
