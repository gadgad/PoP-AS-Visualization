<?php
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("writeToXML.php");
	include_once("bin/win_backgrounder.php");
				
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
		// TODO: create a new weeks.xml
		$ourFileHandle = fopen("xml\weeks.xml", 'rw') or die("can't create weeks.xml");
		fclose($ourFileHandle);		
		
	}
	
	if($_POST["func"]=="updateAS")
	{
		// TODO: update AS_info.xml		
	}
	
	if($_POST["func"]=="showQueries")
	{
		// ? 		
	}