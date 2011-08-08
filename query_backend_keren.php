<?php
	include("bin/load_config.php");
	
	function ret_res($message, $type)
	{
		echo json_encode(array("result"=>$message ,"type"=>$type));
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
			$linkID = mysql_connect($host, $user, $pass) or ret_res("Could not connect to host. Try again later.", "ERROR");
		} else {
			$linkID = mysql_connect($host, $user) or ret_res("Could not connect to host.  Try again later.", "ERROR");
		} 
		mysql_select_db($database, $linkID) or ret_res("Could not find database. Choose another blade.", "ERROR");
		mysql_close($linkID);
		ret_res($selected_blade."", "GOOD");
	}
        
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------        
    
    
    function getYear($tableName){
    	return "2010";
    }
    
    function getWeek($tableName){
    	return "32";
    }
    
    
	if($_POST["func"]=="showYear")
	{
		// Turn off all error reporting		
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
                
        $tables = mysql_query("SHOW TABLES");
        $years = "";
		
	    while($table = mysql_fetch_row($tables)){
	      	 
	      $newYear = getYear($table[0]);  	     
	      if (strrchr($years,$newYear) == FALSE)
		  {		  
		  		$years .= " ".$newYear ;
		  }  
	        
	    } 
        
        echo json_encode(array("result"=>$weeks));                                    
		mysql_close($linkID);	
	}
	
	
    if($_POST["func"]=="showWeek")
	{
		// Turn off all error reporting		
		$selected_blade = $_POST["blade"];
		$selected_Year = $_POST["year"];
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
                
        $tables = mysql_query("SHOW TABLES");
        $weeks = "";
		
	    while($table = mysql_fetch_row($tables)){ 
	       	     
	      if (getYear($table[0])== $selected_Year)
		  {
		  		$newWeek = getWeek($table[0]);	
		  		$weeks .= " ".$newWeek ;
		  }  
	        
	    } 
        
        echo json_encode(array("result"=>$weeks));                                    
		mysql_close($linkID);	
	}
		
	
	if($_POST["func"]=="getASlist")
	{
		// Turn off all error reporting		
		$selected_blade = $_POST["blade"];
		$selected_Year = $_POST["year"];
		$selected_week = $_POST["week"];
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
        
		$query ="" ; // COMPLETE       
        $results = mysql_query($query);
        $AS = "";
		
	    while($result = mysql_fetch_row($results)){
	  		$AS .= " ".$result ;		  	        
	    } 
        
        echo json_encode(array("result"=>$AS));                                    
		mysql_close($linkID);	
	}
	
?>
