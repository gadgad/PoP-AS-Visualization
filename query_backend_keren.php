<?php
	include("bin/load_config.php");	
		
	// Turn off all error reporting
	error_reporting(0);
		
	
	if(!isset($_POST["blade"]))
	{
		echo "You are not permited to this page!";
		die();
	}
	
	// globals
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
	
	function parse($mysqli,$query){
		$res = "";			
		if ($result = $mysqli->query($query)){
        	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$res .= $value . " ";
				}
		     }
        }
		return $res;
	}
	
	function getTblFromDB($mysqli,$table,$year,$week){
			//$query = "show tables like 'IPEdgesMedianTbl_".$year."_week_".$week."%'";
		$query = "show tables like '".$table."_".$year."_week_".$week."%'"; 			    
		$query = "show tables like '".$table."_".$year."_week_".$week."_%'";
		$query = "show tables like '".$table."_".$year."_".$week."%'";
		$query = "show tables like '".$table."_".$year."_".$week."_%'";
		
        
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
		$blade = $_POST["blade"];		 
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}	
		
		$year = $_POST["year"];
		$week = $_POST["week"];
		
		$table = $DataTables["ip-edges"]["prefix"];        
		$edges = "";
		//$query = "show tables like 'IPEdgesMedianTbl_".$year."_week_".$week."%'";
		$query = "show tables like '".$table."_".$year."_week_".$week."%'"; 			    
		$query = "show tables like '".$table."_".$year."_week_".$week."_%'";
		$query = "show tables like '".$table."_".$year."_".$week."%'";
		$query = "show tables like '".$table."_".$year."_".$week."_%'";
		
        if ($result = $mysqli->query($query)){
        	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$edges .= $value . " ";
				}
		     }
        }
		
		$table = $DataTables["pop-locations"]["prefix"];
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
		$blade = $_POST["blade"];
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
        
		$edgeTbl = $_POST["edge"];
		$popTbl = $_POST["pop"];
		
		$query ="" ; // TODO COMPLETE
		
		$AS = "";
		$ASinfo = simplexml_load_file("xml\ASN_info.xml");       
        if ($result = $mysqli->query($query)){
        	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$AS .= $value . " ";		 										
					$result = $ASinfo->xpath("/DATA/ROW[ASNumber=".$value."]");		
					if($result!=FALSE)
					{
						 $AS.=$result[0]->Country." ".$result[0]->ISPName;
					}
					$AS.= "*";		  
				}
		     }
        }
        	
		//$AS = "172 90";
		header('Content-type: application/text');        
        echo json_encode(array("result"=>$AS));                                    
		$mysqli->close();
	}
	
	
	if($_POST["func"]=="sendQuery")
	{
		$blade = $_POST["blade"];
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
        
		$edgeTbl = $_POST["edge"];
		$popTbl = $_POST["pop"];
		$ASlist = $_POST["as"]; // an array of AS
		$username = $_POST["username"];
		
		$tmp = "";
		foreach ($ASlist as &$as) {
		    $tmp.= "_".($as);
		}
		
		$queryID = md5($edgeTbl."_".$popTbl.$tmp);
		
		$queries = simplexml_load_file("queries\query.xml");
		//print_r($queries);					
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');		
		if($result!=FALSE) // this query already exists
		{
			// TODO ->add user to users
			/*
			foreach ($result as $i => $value) {												
				echo "<tr>";
				echo "<td>".$result[$i]->queryID."</td>" . "<td>my query</td>" . "<td>".$result[$i]->lastKnownStatus."</td>" . 
				'<td> <button type="button" id="abort" value="'.$result[$i]->queryID.'">X</button></td>';
				// change id to unique value
				echo "</tr>";
			} 
			 * */
		}else { 
		
		/* Step 1. I need to know the absolute path to where I am now, ie where this script is running from...*/ 
		$thisdir = getcwd(); 
		$querydir = $thisdir."/queries";
		
		/* Step 2. From this folder, I want to create a subfolder called "myfiles".  Also, I want to try and make this folder world-writable (CHMOD 0777). Tell me if success or failure... */ 		
		if(mkdir($thisdir ."/".$queryID , 0777)) 
		{ 
		   echo "Directory has been created successfully..."; 
		} 
		else 
		{ 
		   echo "Failed to create directory..."; 
		} 
		
		/*
		$query1 ="" ; // TODO COMPLETE
		$query2 ="" ; // TODO COMPLETE
		$result1 = $mysqli->query($query1);
		$result2 = $mysqli->query($query2)  		               			   
		*/
		
		// update query.xml file	
		}
				
		header('Content-type: application/text');        
        echo json_encode(array("queryID"=>$queryID));
		                                    
		$mysqli->close();
	}
	
	if($_POST["func"]=="abort")
	{
		$queryID = $_POST["query"];
		/*
		 TODO: check if ther is only 1 user for the query, if so:
		 * 1) cancel it
		 * 2) update query.xml
		 * remove user from users list
		 */ 
	}
	
?>
