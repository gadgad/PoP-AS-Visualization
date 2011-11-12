<?php
/*
 * the server-side for the admin functions 
 */
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("bin/writeToXML.php");
	include_once("bin/backgrounder.php");
	require_once("bin/DBConnection.php");
	require_once("bin/query_status.php");
	require_once("bin/email_validator.php");
	require_once("bin/save_xml.php");
	require_once("bin/KLogger.php");
	include("verify.php");
	
	function send_mail($to,$type,$user="",$subject="",$body=""){
		global $log;
		global $MAIL_MESSAGES_MAP;
			
		$log->logInfo("to: $to, type: $type, user: $user");
		
		if(!isset($MAIL_MESSAGES_MAP[$type]))
			return false;
		
		if($subject=="") $subject = $MAIL_MESSAGES_MAP[$type]["subject"];
		if($body=="") $body = $MAIL_MESSAGES_MAP[$type]["body"];
		
		$body = str_replace('\n',PHP_EOL,$body);
		$body = str_replace('$url', SITE_URL, $body);
		$body = str_replace('$user', $user, $body);
		$header = "From: ".MAIL_FROM;
		// $header.=PHP_EOL."Return-Path:<popas@post.tau.ac.il>";
		
		$log->logInfo("subject: $subject");
		$log->logInfo("body: $body");
		if (mail($to, $subject, $body, $header)) {
		   $log->logInfo("mail sent successfully!");
		   return true;
		}
		$log->logInfo("send mail failed...");
		return false;	
	}
				
	// Turn off all error reporting
	error_reporting(E_ERROR);
	
	// Initialize Mail Logger
	$log = new KLogger('mail_log', KLogger::INFO );
	
	//preventing any non-admin users to reach this page
	if(($_POST["user"])!="admin")
	{
		echo "You are not permited to this page!";
		die();
	}
	
	// returns a result to the browser (type:GOOD/ERROR, result- free text.)
	function ret_res($message, $type)
	{
		header('Content-type: application/json');
		echo json_encode(array("result"=>$message ,"type"=>$type));
		die();	
	}
	
	// executing the query and checking for non-empty results 
	function parse($mysqli,$query){
		$res = "";			
		if ($result = $mysqli->query($query)){
			if ($result->num_rows >0){
				return "1";
			}		        	 
        }
		$result->close();
		return $res;
	}
	
	// creating the query to find a table by the table name, yer and week. 
	function getTblFromDB($mysqli,$table,$year,$week){
								
		$query1 = "'".$table."\_".$year."\_week_".$week."'";		
		$query2 = "'".$table."\_".$year."\_week_".$week."\_%'";		
		$query3 = "'".$table."\_".$year."\_".$week."'";		
		$query4 = "'".$table."\_".$year."\_".$week."\_%'";					
		
		$query = "select TABLE_NAME from INFORMATION_SCHEMA.TABLES WHERE table_schema='".$GLOBALS["DEFAULT_SCHEMA"]."' and (table_name like ".$query1." or table_name like ".$query2." or table_name like ".$query3." or table_name like ".$query4.")";
		// finding out if such tables exist
		$res = parse($mysqli,$query);	
		return $res;        
	}
	
	// recreating the ASN_info.xml file from the specified parameters 
	function generateASinfo($table,$schema,$blade,$mysqli){
		
		// executing the query
		$query = "SELECT * FROM ".$schema.".".$table.";";		
		if ($result = $mysqli->query($query)){
			
			// deleting the old file.
			$nameXML = "xml/ASN_info.xml";
			if (file_exists($nameXML)){
				unlink($nameXML);	
			}		
			// creating a new empty file.
			$ourFileHandle = fopen($nameXML, "w+") or die("can't create ASN_info.xml");
			fwrite($ourFileHandle,"<DATA></DATA>"); 
			fclose($ourFileHandle);
				
			// processing the query result and writing it to file.	 
			$xml = simplexml_load_file($nameXML);
        	while ($row = $result->fetch_assoc()) {
        		$newRow = $xml->addChild('ROW');
		        foreach($row as $key => $value){
					$newRow->addChild($key,$value);	
				}	
		    }

			$xml->asXML($nameXML);			     		     		     		    
			$result->close();
			$mysqli->close();   
        }else {ret_res("bad query result","ERROR");}
		ret_res('done',"GOOD");
	}
	 
	 // updating the weeks.xml file
	if($_POST["func"]=="updateWeeks")
	{
		updateWeeks($_POST["blade"]);
	}

	function updateWeeks($bladeParam) 
	{
		// setting connection parameters
		$blade = $GLOBALS["Blade_Map"][$bladeParam];
		$host = (string)$blade["host"];
		$port = (int)$blade["port"];
		$user = (string)$blade["user"];
		$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
		
		// connecting to the DB						
		$mysqli = new DBConnection($host,$user,$pass,$schema,$port,5);
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
 		   die();
		}
		
		$nameXML = "xml/weeks.xml";
		$xml = simplexml_load_file($nameXML);
		
		// deleting old info regarding the blade (if exists)
		$res = $xml->xpath('/DATA/blade[@name="'.$bladeParam.'"]');
		if($res!=FALSE){
			foreach ($res as $key => $value){						  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    ret_res('Error while converting SimpleXMLelement to DOM',"ERROR");
				}		
				$oNode->parentNode->removeChild($oNode); 							
			}	
		}
			
		// creating a new tag for the blade
		$newBladeTag = $xml->addChild('blade');
		$newBladeTag->addAttribute('name',$bladeParam.' test');		
					 		
		$weeks[] = array();
		//unset($weeks);
		$maxYear = date('Y');	

		//finding all the weeks that has all three tables
		for($year=2008;$year<=$maxYear;$year++){ 	
			for($week=1;$week<53;$week++){
					
				$table = $DataTables["ip-edges"]["prefix"];        
				$edges = getTblFromDB($mysqli,$table,$year,$week);
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
			
			//for a specific year - writing the weeks to file
			if ($weeks!=null){// not empty , !weeks
				$newyear = $newBladeTag->addChild('date');
				$newyear->addAttribute('year',$year);
				foreach ($weeks as $w){
					$newyear->addChild('week',$w);	
				}
			}
			unset($weeks);
		}
		// saving the file, closing connection to DB.
		$xml->asXML($nameXML);
		$mysqli->close();
		ret_res('done',"GOOD");
	}
	
	// reciving predefined parameters to update AS_info.xml
	if($_POST["func"]=="updateAS")
	{
		$table = $_POST["table"];
		$schema = $DataTables["as-info"]["schema"];
		$blade = $Blade_Map[$GLOBALS["AS_INFO_DEFAULT_BLADE"]];		
		$host = (string)$blade["host"];
		$port = (int)$blade["port"];
		$user = (string)$blade["user"];
		$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
		
		// connecting to the DB						
		$mysqli = new DBConnection($host,$user,$pass,$schema,$port,5);
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error.' blade:'.$blade.' host:'.$host.' port:'.$port,"ERROR");
 		   die();
		} 
		
		// generating the file by parameters
		generateASinfo($table, $schema,$blade,$mysqli);
	}
	
	// reciving freetext parameters to update AS_info.xml
	if($_POST["func"]=="updateASfree")
	{
		$table = $_POST["table"];
		$schema = $_POST["schema"];
		$blade = $Blade_Map[$_POST["blade"]];
		$host = (string)$blade["host"];
		$port = (int)$blade["port"];
		$user = (string)$blade["user"];
		$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
		
		// connecting to the DB						
		$mysqli = new DBConnection($host,$user,$pass,$schema,$port,5);
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
 		   die();
		} 
		
		// generating the file by parameters
		generateASinfo($table, $schema,$blade,$mysqli);
	}
	
	// accepting a user's request to login the site
	if($_POST["func"]=="accept")
	{
		$path =  "users/".$_POST["userfile"];
		//$username = substr($_POST["userfile"],-4) ; 
		$username = basename($_POST["userfile"],".xml");
		$userData = simplexml_load_file($path);
		$res = $userData->xpath('/user/email');
		$to = (string)$res[0];
		// changing the user's statuse from pending to authorized
		$res = $userData->xpath('/user/status');		
		foreach ($res as $key => $state){
			if ($state == "pending"){			  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    //echo 'Error while converting SimpleXMLelement to DOM';
					ret_res('Error while accepting request.',"ERROR");
				}		
				$oNode->parentNode->removeChild($oNode); 				
			}
		}		
		$userData->addChild('status',"authorized");		
		$userData->asXML($path);
		
		// add user's email to authorized_users.xml
		$registered = simplexml_load_file('xml/authorized_users.xml');
		$registered->addChild('email',md5($to));
		//$registered->asXML('xml/authorized_users.xml');
		save_xml_file($registered->asXML(),'xml/authorized_users.xml');			
		
		// sending an email to the user
		if (!send_mail($to,"accept",$username)) {
		   ret_res('user authorized, mail delivery failed.',"ERROR");
		} 
		ret_res('done',"GOOD");
	}
	
	// denying a user's request to login the site
	if($_POST["func"]=="deny")
	{
		$path =  "users/".$_POST["userfile"];
		$username = basename($_POST["userfile"],".xml");
		$userData = simplexml_load_file($path);
		$res = $userData->xpath('/user/email');
		$to = (string)$res[0];
		// changing the user's statuse from pending to denied
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
		
		// sending an email to the user
		if (!send_mail($to,"deny",$username)) {
			ret_res('user denied, mail delivery failed.',"ERROR");
		} 
		ret_res('done',"GOOD");
	}
	
	// adding a new blade to config/config.xml
	if($_POST["func"]=="addBlade")
	{
		$blade =  $_POST["blade"];
		$host =  $_POST["host"];
		$port =  intval($_POST["port"]);
		$hostNport = $host.":".((string)$port);
		$bladeUser =  $_POST["bladeUser"];
		$pass =  $_POST["pass"];
		$db =  $_POST["db"];
		$writedb =  $_POST["writedb"];
		
		// test connection for new blade
		if(isset($pass) && $pass!=""){
			$linkID = mysql_connect($hostNport, $bladeUser, $pass);
		} else {
			$linkID = mysql_connect($hostNport, $user);
		} 
		if(!isset($linkID) || $linkID==false){
			ret_res("Could not connect to blade. verify config parameters (host:$host port:$port user:$bladeUser pass:$pass)", "ERROR");
		}
		mysql_select_db($db, $linkID) or ret_res("Could not find database. verify config parameters (database:$db)", "ERROR");
		mysql_close($linkID);
		
		
		$xml = simplexml_load_file('config/config.xml');							
		$blades = $xml->xpath('/config/blades');		
		if($blades!=FALSE)
		// adding the blade
		{
			$newBlade = $blades[0]->addChild('blade');
			$newBlade->addAttribute("name", $blade);
			$newBlade->addChild('host', $host);
			$newBlade->addChild('port', $port);
			$newBlade->addChild('user', $bladeUser);
			$newBlade->addChild('pass', $pass);
			$newBlade->addChild('db', $db);
			$newBlade->addChild('write-db', $writedb);
			//$xml->asXML('config/config.xml');	
			save_xml_file($xml->asXML(),'config/config.xml');		
		}else ret_res('cant add blade to file',"ERROR");
		
		$GLOBALS["Blade_Map"][$blade] = array("host"=>$host,"port"=>$port,"user"=>$bladeUser,"pass"=>$pass,"db"=>$db,"write-db"=>$writedb);
		updateWeeks($blade);
	}
	
	// removing a blade from config/config.xml
	if($_POST["func"]=="removeBlade")
	{
		$blade =  $_POST["blade"];
		// loading the file and finding the specified blade
		$xml = simplexml_load_file('config/config.xml');
		$res = $xml->xpath('/config/blades/blade[@name="'.$blade.'"]');
		// removing the blade
		if($res!=FALSE){
			foreach ($res as $key => $value){						  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    ret_res('Error while converting SimpleXMLelement to DOM',"ERROR");
				}		
				$oNode->parentNode->removeChild($oNode); 							
			}	
		}else ret_res('The specified blade wasnt found',"ERROR");
		// saving the file				
		$xml->asXML('config/config.xml');
		ret_res('done',"GOOD");	
	}
	
	// changing the default blade at config/config.xml
	if($_POST["func"]=="changeDefaultBlade")
	{
		$newBlade = $_POST["blade"];				
		$xml = simplexml_load_file('config/config.xml');
		//checking for the existance of the 2 blades
		$res = $xml->xpath('/config/blades/blade[@default="true"]');
		$res2 = $xml->xpath('/config/blades/blade[@name="'.$newBlade.'"]');
		
		if($res==FALSE){
			ret_res('The old default blade wasnt found',"ERROR");
			}elseif($res2==FALSE){
				ret_res('The new default blade wasnt found',"ERROR");
			}else {
				//removing the "default" attribute
				foreach ($res as $key => $value){						  
					$theNodeToBeDeleted = $res[$key];								
					$oNode = dom_import_simplexml($theNodeToBeDeleted);				
					if (!$oNode) {
					    ret_res('Error while converting SimpleXMLelement to DOM',"ERROR");
					}		
					$oNode->removeAttribute('default');				 							
				}
				//adding the "default" attribute to the new default blade
				$res2[0]->addAttribute('default','true');	
			}
											
		$xml->asXML('config/config.xml');
		ret_res('done',"GOOD");
	 }
	
	// chanchig a data-table parameter at config/config.xml
	if($_POST["func"]=="changeDT")
	{
		$dataTable = $_POST["dataTable"];
		$SP = $_POST["SP"];
		$paramValue = $_POST["paramValue"];
		
		$xml = simplexml_load_file('config/config.xml');
		$res = $xml->xpath('/config/data-tables/'.$dataTable.'/'.$SP);
		
		// removing the old parameter
		if($res!=FALSE){
			foreach ($res as $key => $value){						  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    ret_res('Error while converting SimpleXMLelement to DOM',"ERROR");
				}		
				$oNode->parentNode->removeChild($oNode); 							
			}	
		}else ret_res('The specified parameter wasnt found',"ERROR");
		
		// inserting the new parameter
		$res = $xml->xpath('/config/data-tables/'.$dataTable);
		$res[0]->addChild($SP,$paramValue);
								
		$xml->asXML('config/config.xml');		
		ret_res('done',"GOOD");	
	}	

	// chanchig a parameter from the paramters at config/config.xml
	if($_POST["func"]=="changeParamVal")
	{
		$parameter = $_POST["param"];
		$attribute = $_POST["attribute"];
		$newValue = $_POST["value"];
		
		$xml = simplexml_load_file('config/config.xml');
		$res = $xml->xpath('/config/config-parameters/parameter[name="'.$parameter.'"]/'.$attribute);
		// removing the old parameter
		if($res!=FALSE){
			foreach ($res as $key => $value){						  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    ret_res('Error while converting SimpleXMLelement to DOM',"ERROR");
				}		
				$oNode->parentNode->removeChild($oNode); 							
			}	
		}else ret_res('The specified parameter wasnt found',"ERROR");
		
		// inserting the new parameter
		$res = $xml->xpath('/config/config-parameters/parameter[name="'.$parameter.'"]');
		if($res!=FALSE){
			$res[0]->addChild($attribute,$newValue);
		}else ret_res('The specified parameter wasnt found',"ERROR");						
		$xml->asXML('config/config.xml');		
		ret_res('done',"GOOD");	
	}

	if($_POST["func"]=="changePassword")
	{
		$oldPass = $_POST["oldPass"];
		$newPass = $_POST["newPass"];
		$confirmPass = $_POST["confirmPass"];
		
		$xml = simplexml_load_file('users/admin.xml');
		$res = $xml->xpath('/user/password');
		
		if($res!=FALSE){
			
			//checking that new password and confirm password are the same
			if($newPass!=$confirmPass){
				ret_res('new password does not match confirm password',"ERROR");
			}
			
			//checking that old password is the same as in file			
			if(hash("sha256",$oldPass)!=$res[0]){
				ret_res('old password is incorrect',"ERROR");
			}			
			
			// removing the old password
			foreach ($res as $key => $value){						  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    ret_res('Error while converting SimpleXMLelement to DOM',"ERROR");
				}		
				$oNode->parentNode->removeChild($oNode); 							
			}	
		}else ret_res('old password was not found',"ERROR");
		
		// inserting the new parameter
		$res = $xml->xpath('/user');
		if($res!=FALSE){
			$res[0]->addChild('password', hash("sha256",$newPass));
		}else ret_res('Error while searching file',"ERROR");						
		$xml->asXML('users/admin.xml');		
		ret_res('done',"GOOD");	
	}
	
	// inviting new users to the site
	if($_POST["func"]=="inviteUser")
	{
		$to = $_POST["email"];
		$invaitee = $_POST["invaitee"];
		$subject = $_POST["subject"];
		$body = $_POST["body"];
		
		// validating email address
		if(!check_email_address($to)){
			ret_res('inavalid email address',"ERROR");
		}
		
		// checking if email is not already registered
		$hashed_email = md5($to);
		$registered = simplexml_load_file('xml/authorized_users.xml');
		$res = $registered->xpath('/DATA[email="'.$hashed_email.'"]');
		if(!empty($res)){
			ret_res('user is already registered in the system.',"ERROR");
		}
		
		// adding the user to the invited users list.			
		$invited = simplexml_load_file('xml/invited_users.xml');
		$res = $invited->xpath('/DATA[email="'.$hashed_email.'"]');
		if(!empty($res)){
			ret_res('user has already been invited into the system.',"ERROR");
		}
		
		$invited->addChild('email',$hashed_email);
		save_xml_file($invited->asXML(),'xml/invited_users.xml');
		
		// sending an email to the user
		if (!send_mail($to,"invitation",$invaitee,$subject,$body)) {
		   ret_res('mail delivery failed.',"ERROR");
		} 
		ret_res('done',"GOOD");		
	}
	
?>
