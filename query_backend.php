<?php
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("writeToXML.php");
	include_once("bin/win_backgrounder.php");
				
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
					
		$query1 = "'".$table."\_".$year."\_week_".$week."'";		
		$query2 = "'".$table."\_".$year."\_week_".$week."\_%'";		
		$query3 = "'".$table."\_".$year."\_".$week."'";		
		$query4 = "'".$table."\_".$year."\_".$week."\_%'";					
		
		$query = "select TABLE_NAME from INFORMATION_SCHEMA.TABLES WHERE table_schema='DIMES_DISTANCES' and (table_name like ".$query1." or table_name like ".$query2." or table_name like ".$query3." or table_name like ".$query4.")";
		$res = parse($mysqli,$query,$res);
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
    
    if($_POST["func"]=="getWeeks")
	{
		$blade = $_POST["blade"];
		$year = $_POST["year"];
		
		$weeks[] = array();
		$xml = simplexml_load_file("xml\weeks.xml");
		$result = $xml->xpath('/DATA/YEAR[year="'.$year.'"]/WEEK');					
		if($result!=FALSE)
		{
			foreach($result as $index=>$object){
				$weeks[] = (string)$result[$index];					
			}								
		}
		else{
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
			if ($mysqli->connect_error) {
	 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
			}
			
			for($i=1;$i<53;$i++){
				
				$table = $DataTables["ip-edges"]["prefix"];        
				$edges = getTblFromDB($mysqli,$table,$year,$i);
				if ($edges!=""){
					$table = $DataTables["pop-locations"]["prefix"];
					$pops = getTblFromDB($mysqli,$table,$year,$i);
					if ($pops!=""){
						$table = $DataTables["popip"]["prefix"];
						$popsIP = getTblFromDB($mysqli,$table,$year,$i);
						if ($popsIP!=""){
							$weeks[] = $i;
						}
					}
				}				
			}			
		}   
		header('Content-type: application/json');
    	echo json_encode(array("weeks"=>$weeks));      
		
	}
			
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
		
		$mysqli->close();	    
		header('Content-type: application/json');
        echo json_encode(array("edge"=>$edges,"pop"=>$pops,"popIP"=>$popsIP));                                    		
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
		$stage = intval($_POST["stage"]);			
		
		$pop = $_POST["pop"];
		$popIP = $_POST["popIP"];
		$edge = $_POST["edge"];
		$as = $_POST["as"];
		$idg = new idGen($edge,$pop,$as,$popIP,$blade);
		$queryID = $idg->getqueryID();
		
		$asp = $_POST["as"];			
		$as = "'";
		$as .= join("','", $asp);						
		$as .= "'";
		
		// pop query
		$query1 = 'create table `DIMES_POPS_VISUAL`.`'.$idg->getPoPTblName().'` (select * from `'.$database.'`.`'.$pop.'` where ASN in('.$as.')) order by ASN';
			
		// edge query			
		$query2 = 'create table `DIMES_POPS_VISUAL`.`'.$idg->getEdgeTblName().'` (select edges.*, src.PoPID Source_PoPID, dest.PoPID Dest_PoPID FROM '.$edge.' edges left join '.$popIP.' src on(edges.SourceIP = src.IP) left join '.$popIP.' dest on(edges.DestIP = dest.IP) where edges.SourceAS in ('.$as.') AND edges.DestAS in ('.$as.'))';
		
		chdir( dirname ( __FILE__ ) );
		$thisdir = str_replace('\\','/',getcwd());
	
		if($stage==1)
		{
			$queries = simplexml_load_file("xml\query.xml");							
			$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users');		
			if($result!=FALSE) // this query already exists
			{
				if($result[0]->user!=$username){
					$result->addChild('user', $username);
					ret_res("query already assigned to a different user,adding current user to record","ALL_COMPLETE");
				} else {
					ret_res("query already exists!","ALL_COMPLETE");
				}
			}
			ret_res("stage1 complete","STAGE1_COMPLETE");
		}
		
		// execute query on mysql server
		if($stage==2)
		{
			$cmd = "send_query.php --host=".$host." --user=".$user." --pass=".$pass." --database=".$database." --port=".$port." --PoPTblName=".$idg->getPoPTblName()." --pop=".$pop." --as=".$as." --EdgeTblName=".$idg->getEdgeTblName()." --edge=".$edge." --popIP=".$popIP." --query=";
			win_backgrounder($cmd."1",'query1',$queryID);
			//sleep(2);
			win_backgrounder($cmd."2",'query2',$queryID);
			//sleep(2);
			ret_res("stage2 complete","STAGE2_COMPLETE");
			/*
			$sub_stage = intval($_POST["sub_stage"]);
			 
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
					
			if ($mysqli->connect_error) {
	 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
			}
			
			$processID = $mysqli->thread_id;
			
			header('Content-type: application/json');
			echo json_encode(array("result"=>"sending query..." ,"type"=>"STAGE2_COMPLETE","queryID"=>$queryID,"processID"=>$processID));
			session_start();
			myFlush();
			
			//$mysqli->autocommit(FALSE);	
			$mysqli->select_db($database);
			//$mysqli->options("max_allowed_packet=50M");
			//$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 300);
			
			if($sub_stage==1){
				$_SESSION['pid1'] = $processID;
				$result = $mysqli->query($query1);
			}
			else{ // stage2.2
				//$mysqli->ping();
				$_SESSION['pid2'] = $processID;
				$result = $mysqli->query($query2);
			}
			
			if($mysqli->error){
				ret_res('Mysqli Error (' . $mysqli->errno . '): '. $mysqli->error,"ERROR");
			}
			  		               			   
			//$mysqli->commit();
			
			$mysqli->close();
			*/
		}
		
		// check if present in 'SHOW PROCESSLIST' and/or table exist, if so add to query.xml 
		if($stage==3)
		{
			session_start();
			$pid1 = isset($_SESSION['pid1'])?$_SESSION['pid1']:'null';
			$pid2 = isset($_SESSION['pid2'])?$_SESSION['pid2']:'null';
			
			// 0 - error , 1 - running , 2 - complete
			$edge_state = 0;
			$pop_state = 0;
			
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
					
			while($mysqli->connect_error) {
					sleep(3);
					//$mysqli->ping();
					$mysqli = new mysqli($host,$user,$pass,$database,$port);
	 		    	//ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
			}
			
			$result = $mysqli->query('SHOW FULL PROCESSLIST;');
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_assoc();
			    if($row['State']!=NULL){
			    	//echo $row['Id']." ".$row['Info']."\n";
			    	$tbl = strstr(strstr( $row['Info'] ,'DPV_'),'`',true);
					if($idg->getPoPTblName()==$tbl){
						$pop_state = 1;
					} else if($idg->getEdgeTblName()==$tbl){
						$edge_state = 1;
					}
			    }
			}
			if($pop_state==0){
				$result = $mysqli->query("show tables from DIMES_POPS_VISUAL like '".$idg->getPoPTblName()."'");
				$num = $result->num_rows;
				if($num>0) $pop_state = 2;	
			}
			if($edge_state==0){
				$result = $mysqli->query("show tables from DIMES_POPS_VISUAL like '".$idg->getEdgeTblName()."'");
				$num = $result->num_rows;
				if($num>0) $edge_state = 2;	
			}
			$mysqli->close();
			
			if($pop_state==0 || $edge_state==0){
				//ret_res("sql query failed to execute properly...</BR>try running the following queries directly:</BR>".(($pop_state==0)?$query1:"")."</BR>".(($edge_state==0)?$query2:""),"ERROR");
				ret_res("sql query failed to execute properly...</BR>","ERROR");
			} else {
				AddQuery($queryID,$processID,$username,$edge, $pop,count($asp),$asp,$blade);
				
				// making a new dir to hold query results 
				$querydir = $thisdir."/queries"."/".$queryID;
				if(!file_exists($querydir)){			 		
					if(!mkdir($querydir, 0777)) { 
					   ret_res("Failed to create directory: ".$querydir,"ERROR"); 
					}
				}
				header('Content-type: application/json');
				echo json_encode(array("result"=>"query is now running..." ,"type"=>"GOOD","queryID"=>$queryID,"pid1"=>$pid1,"pid2"=>$pid2));
			}
		}	
	}
	
?>
