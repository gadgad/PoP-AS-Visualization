<?php
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("bin/writeToXML.php");
	include_once("bin/backgrounder.php");
	require_once("bin/DBConnection.php");
	include("verify.php");
				
	// Turn off all error reporting
	error_reporting(0);
	if(($_POST["user"])!="admin")
	{
		echo "You are not permited to this page!";
		die();
	}
	
	function ret_res($message, $type)
	{
		header('Content-type: application/json');
		echo json_encode(array("result"=>$message ,"type"=>$type));
		die();	
	}
	
	// TODO: fix this!!!!! the result returns with all properties null. 
	function parse($mysqli,$query){
		$res = "";
		$strres = "";			
		if ($result = $mysqli->query($query)){
			if ($result->num_rows >0){
				return "1";
			}		
			$strres .= " in the if-result: ".var_dump($result);
			// $result->num_rows.
        	 
        }else {$strres.= " broblem with query results ";}
		$result->close();
		ret_res($strres,"GOOD");
		return $res;
	}
	
	function getTblFromDB($mysqli,$table,$year,$week){
								
		$query1 = "'".$table."\_".$year."\_week_".$week."'";		
		$query2 = "'".$table."\_".$year."\_week_".$week."\_%'";		
		$query3 = "'".$table."\_".$year."\_".$week."'";		
		$query4 = "'".$table."\_".$year."\_".$week."\_%'";					
		
		$query = "select TABLE_NAME from INFORMATION_SCHEMA.TABLES WHERE table_schema='DIMES_DISTANCES' and (table_name like ".$query1." or table_name like ".$query2." or table_name like ".$query3." or table_name like ".$query4.")";
		$res = parse($mysqli,$query);	
		return $res;        
	}
	
	function generateASinfo($table,$schema,$blade){
		// TODO: complete.. 
	}
	 
	 if($_POST["func"]=="updateWeeks")
	{
						
		$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
 		   die();
		}
		
		$nameXML = "xml/weeks.xml";
		
		if (file_exists($nameXML)){
			unlink($nameXML);	
		}		
		$ourFileHandle = fopen("xml/weeks.xml", "w+") or die("can't create weeks.xml");
		fwrite($ourFileHandle,"<DATA></DATA>"); 
		fclose($ourFileHandle);
			 
		$xml = simplexml_load_file($nameXML);

		//$data = $xml->addChild('DATA');
		
		$weeks[] = array();
		//unset($weeks);
		//ret_res($weeks,"GOOD");
		$maxYear = date('Y');	

		for($year=2008;$year<=$maxYear;$year++){ 	
			for($week=1;$week<53;$week++){
					
				$table = $DataTables["ip-edges"]["prefix"];        
				$edges = getTblFromDB($mysqli,$table,$year,$week); ret_res("edges: ".$edges,"GOOD"); 
				if ($edges!=""){					
					$table = $DataTables["pop-locations"]["prefix"];
					$pops = getTblFromDB($mysqli,$table,$year,$week);
					if ($pops!=""){
						$table = $DataTables["popip"]["prefix"];
						$popsIP = getTblFromDB($mysqli,$table,$year,$week);
						if ($popsIP!=""){							
							$weeks[] = $week;
						}
					}
				}
			}
			
			if ($weeks!=null){// not empty , !weeks
				ret_res($weeks,"GOOD");	
				$newyear = $xml->addChild('YEAR');
				$newyear->addChild('year',$year);
				foreach ($weeks as $w){
					$newyear->addChild('WEEK',$w);	
				}
			}
			unset($weeks);
		}
		$xml->asXML($nameXML);
		$mysqli->close();
		ret_res('done',"GOOD");
	}
	
	if($_POST["func"]=="updateAS")
	{
		$table = $_POST["table"];
		$schema = $DataTables["as-info"]["schema"];
		$blade = $GLOBALS["AS_INFO_DEFAULT_BLADE"];
		generateASinfo($table, $schema,$blade );
	}
	
	if($_POST["func"]=="updateASfree")
	{
		$table = $_POST["table"];
		$schema = $_POST["schema"];
		$blade = $_POST["blade"];
		generateASinfo($table, $schema,$blade );
	}
	
	if($_POST["func"]=="accept")
	{
		$path =  "users/".$_POST["userfile"]; 
		$userData = simplexml_load_file($path);
		
		$res = $userData->xpath('/user/status');		
		foreach ($res as $key => $state){
			if ($state == "pending"){			  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    echo 'Error while converting SimpleXMLelement to DOM';
				}		
				$oNode->parentNode->removeChild($oNode); 				
			}
		}
		
		$userData->addChild('status',"authorized");		
		$userData->asXML($path);			
		ret_res('done',"GOOD");
	}
	
	if($_POST["func"]=="deny")
	{
		$path =  "users/".$_POST["userfile"]; 
		$userData = simplexml_load_file($path);
		
		$res = $userData->xpath('/user/status');		
		foreach ($res as $key => $state){
			if ($state == "pending"){			  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    ret_res('Error while converting SimpleXMLelement to DOM',"ERROR"); ;
				}		
				$oNode->parentNode->removeChild($oNode); 				
			}
		}
		
		$userData->addChild('status',"denied");		
		$userData->asXML($path);			
		ret_res('done',"GOOD");
	}
	
	
?>
