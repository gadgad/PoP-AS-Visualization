<?php
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("writeToXML.php");
	include_once("bin/backgrounder.php");
				
	// Turn off all error reporting
	error_reporting(0);
	if(($_POST["user"])!="admin")
	{
		echo "You are not permited to this page!";
		die();
	}
	
	 
	 if($_POST["func"]=="updateWeeks")
	{
		unlink("xml\weeks.xml");
		$ourFileHandle = fopen("xml\weeks.xml", 'rw') or die("can't create weeks.xml");
		fclose($ourFileHandle);		
		
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		if ($mysqli->connect_error) {
 		   //ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
 		   die();
		}
		
		$nameXML = "xml/weeks.xml";	 
		$xml = simplexml_load_file($nameXML);
		$data = $xml->addChild('DATA');
		
		$weeks[] = array();
		unset($weeks);
		$maxYear = date('Y');	
		for($year=2004;$year<=$maxYear;$year++){ 	
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
			if (!$weeks){// not empty
				$newyear = $data->addChild('YEAR');
				$newyear->addChild('year',$year);
				foreach ($weeks as $w){
					$newyear->addChild('WEEK',$w);	
				}
			}
			unset($weeks);
		}
		$xml->asXML($nameXML);
	}
	
	if($_POST["func"]=="updateAS")
	{
		// TODO: update AS_info.xml		
	}
	
	if($_POST["func"]=="showQueries")
	{
		// ? 		
	}
?>
