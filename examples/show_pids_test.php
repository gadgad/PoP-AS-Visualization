<?php

	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
				
	// Turn off all error reporting
	//error_reporting(0);
			
	
	// globals
	$selected_blade = "B4";
	$blade = $Blade_Map[$selected_blade];
	$host = (string)$blade["host"];
	$port = (int)$blade["port"];
	$hostNport = (string)$blade["host"].":".(string)$blade["port"];
	$user = (string)$blade["user"];
	$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
	$database = (string)$blade["db"];
    $mysqli = new mysqli($host,$user,$pass,$database,$port);
					
	if ($mysqli->connect_error) {
	   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
	}
	
	$result = $mysqli->query('SHOW FULL PROCESSLIST;');
	
	$num = $result->num_rows;
	for($x = 0 ; $x < $num ; $x++){ 
	    $row = $result->fetch_assoc();
	    if($row['State']!=NULL){
	    	//echo $row['Id']." ".$row['Info']."\n";
	    	$str = strstr(strstr( $row['Info'] ,'DPV_'),'`',true);
	    	echo $str."\n";
	    }
	    /*
		foreach($row as $key => $value){
			$xml_output .= "\t\t<".$key.">" .$value . "</".$key.">\n";
		}
		*/
	} 
	$mysqli->close();
?>