<?php
	include("bin/load_config.php");	
		
	// Turn off all error reporting
	error_reporting(0);
		
	
	////// globals
	if(!isset($_POST["blade"]))
	{
		echo "You are not permited to this page!";
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
	
	function ret_res($message, $type)
	{
		header('Content-type: application/json');
		echo json_encode(array("result"=>$message ,"type"=>$type));
		die();	
	}
	
	if($_POST["func"]=="testConnection")
	{ 
		if(isset($pass) && $pass!=""){
			$linkID = mysql_connect($hostNport, $user, $pass) or ret_res("Could not connect to host. Try again later.", "ERROR");
		} else {
			$linkID = mysql_connect($hostNport, $user) or ret_res("Could not connect to host.  Try again later.", "ERROR");
		} 
		mysql_select_db($database, $linkID) or ret_res("Could not find database. Choose another blade.", "ERROR");
		mysql_close($linkID);
		ret_res($selected_blade."", "GOOD");
	}
        
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------        
    
    
	if($_POST["func"]=="showTables")
	{
				 
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
		
		$year = $_POST["year"];
		$week = $_POST["week"];
		
		$table = $DataTables["ip-edges"]["prefix"]; //TODO chage table name              
		$edges = "";
		//$query = "show tables like 'IPEdgesMedianTbl_".$year."_week_".$week."%'";
		$query = "show tables like '".$table."_".$year."%_".$week."%'"; 			    
        if ($result = $mysqli->query($query)){
        	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$edges .= $value . " ";
				}
		     }
        }
		
		$table = $DataTables["pop-locations"]["prefix"]; //TODO chage table name
		$pops = "";
		$query = "show tables like '".$table."_".$year."%_".$week."%'";
		
		if ($result = $mysqli->query($query)){
        	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$pops .= $value . " ";
				}
		     }
        }
	    
		header('Content-type: application/json');
        echo json_encode(array("edge"=>$edges,"pop"=>$pops));                                    
		$mysqli->close();
	}
	
	
	if($_POST["func"]=="getASlist")
	{
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
        
		// TODO get parameters from POST 
		
		$query ="" ; // TODO COMPLETE       
        $results = mysql_query($query);
        $AS = "";
		
	    while($result = mysql_fetch_row($results)){
	  		$AS .= " ".$result ;		  	        
	    } 
		
		header('Content-type: application/text');        
        echo json_encode(array("result"=>$AS));                                    
		$mysqli->close();
	}
	
?>
