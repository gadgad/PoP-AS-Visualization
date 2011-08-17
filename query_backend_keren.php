<?php
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("writeToXML.php");
				
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
	
	function parse($mysqli,$query,$prev){
		$res = "";			
		if ($result = $mysqli->query($query)){
        	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$pos = strpos($prev,$value);					
					if($pos === false) {
					 	$res .= $value . " ";
					}
				}	
		     }
        }
		$result->close();
		return $res;
	}
	
	function getTblFromDB($mysqli,$table,$year,$week){

		$res = "";	
		$query = "show tables like '".$table."\_".$year."\_week_".$week."'";
		$res.= parse($mysqli,$query,$res); 			    
		$query = "show tables like '".$table."\_".$year."\_week_".$week."\_%'";
		$res.= parse($mysqli,$query,$res);
		$query = "show tables like '".$table."\_".$year."\_".$week."'";
		$res.= parse($mysqli,$query,$res);
		$query = "show tables like '".$table."\_".$year."\_".$week."\_%'";
		$res.= parse($mysqli,$query,$res);
		return $res;        
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
		$edges = getTblFromDB($mysqli,$table,$year,$week);
		
		$table = $DataTables["pop-locations"]["prefix"];
		$pops = getTblFromDB($mysqli,$table,$year,$week);
		
		$table = $DataTables["popip"]["prefix"];
		$popsIP = getTblFromDB($mysqli,$table,$year,$week);
		
			    
		header('Content-type: application/json');
        echo json_encode(array("edge"=>$edges,"pop"=>$pops,"popIP"=>$popsIP));                                    
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
		
		$query ="SELECT distinct ASN FROM `".$database."`.`".$popTbl."` order by ASN limit 20" ;		
		
		$AS = "";
		$ASinfo = simplexml_load_file("xml\ASN_info.xml");
		
		if ($result = $mysqli->query($query)){			
	    	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$AS .= $value . " ";											 									
					$res = $ASinfo->xpath('/DATA/ROW[ASNumber="'.$value.'"]');		
					if($res!=FALSE)
					{
						$AS.=$res[0]->Country." ".$res[0]->ISPName;
					}
					$AS.= "*";							  
				}
		     }
			 $result->close();
    	}else $AS = "no result";       
			   			   
		header('Content-type: application/text');        
        echo json_encode(array("result"=>$AS));                                    
		$mysqli->close();
	}
	
	
	if($_POST["func"]=="sendQuery")
	{
		$blade = $_POST["blade"];       		
		$username = $_POST["username"];			
		
		$pop = $_POST["pop"];
		$popIP = $_POST["popIP"];
		$edge = $_POST["edge"];
		$as = $_POST["as"];
		$idg = new idGen($edge,$pop,$as,$popIP);
		$queryID = $idg->getqueryID();
		
		$queries = simplexml_load_file("xml\query.xml");							
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users');		
		if($result!=FALSE) // this query already exists
		{
			$result->addChild('user', $username);
			
		}else { // making a new quary 
		
			$thisdir = getcwd(); 
			$querydir = $thisdir."/queries";			 		
			if(mkdir($querydir ."/".$queryID , 0777)) 
			{ 
			   //echo "Directory has been created successfully..."; 
			} 
			else 
			{ 
			   echo "Failed to create directory..."; 
			} 
			
			$asp = $_POST["as"];			
			$as = "'";
			$as .= join("','", $asp);						
			$as .= "'";
			
			// pop query
			$query1 = 'create table `DIMES_POPS_VISUAL`.`'.$idg->getPoPTblName().'` (select * from `'.$database.'`.`'.$pop.'` where ASN in('.$as.')) order by ASN';
			
			// edge query			
			$query2 = 'create table `DIMES_POPS_VISUAL`.`'.$idg->getEdgeTblName().'` (select edges.*, src.PoPID Source_PoPID, dest.PoPID Dest_PoPID FROM '.$edge.' edges inner join '.$popIP.' src on(edges.SourceIP = src.IP) inner join '.$popIP.' dest on(edges.DestIP = dest.IP) where edges.SourceAS in ('.$as.') AND edges.DestAS in ('.$as.'))';
			 
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
			if ($mysqli->connect_error) {
	 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
			} 
			 
			$result1 = $mysqli->query($query1);					
			$result2 = $mysqli->query($query2);			  		               			   
			
			$processID = $mysqli->thread_id;
			//$processID="dummyPID";
						
			AddQuery($queryID,$processID,$username,$edge, $pop);
			
		}
				
		header('Content-type: application/text');        
        echo json_encode(array("queryID"=>$queryID));		
		                                    
		$mysqli->close();
	}
		
	
?>
