<?php
require_once("bin/load_config.php");
require_once("bin/idgen.php");
require_once("bin/DBConnection.php");

error_reporting(E_ALL);

function sql2xml($sql,$filename)
{
	global $thismysqli;
	
	$bufferSize = 4096; // Bytes
	$pageSize = 1000; // Records
		
	$selected_blade = 'B1';
	$host = '127.0.0.1';
	$port = 5551;
	//$hostNport = (string)$blade["host"].":".(string)$blade["port"];
	$user = 'codeLimited';
	$pass = '';
	$database = 'DIMES';
	
	$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
	if($mysqli->connect_error) exit("can't connect to DB!");

	$filepath = ('xml/'.$filename);
	$filewrite = fopen($filepath, "w");
	if(!$filewrite) return false;
	
	$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
	$xml_output .= "<DATA>\n"; 
	
	$query = str_replace("*", "count(*)", $sql);
	$result = $mysqli->query($query) or die("SQL Query Failed.");
	$row = $result->fetch_row();
	$num = (int)$row[0];
	$numOfPages = ceil($num/$pageSize);
	
	for($currPage = 0; $currPage<$numOfPages; $currPage++){
		$pageOffset = $currPage*$pageSize;
		
		$query = $sql." limit $pageOffset,$pageSize";
		$result = $mysqli->query($query) or die("SQL Query Failed.");
		$numOfRecords = $result->num_rows;
		for($x = 0 ; $x < $numOfRecords ; $x++){
		    $row = $result->fetch_assoc();
		    $xml_output .= "\t<ROW>\n"; 
			foreach($row as $key => $value){
				$xml_output .= "\t\t<".$key.">" .$value . "</".$key.">\n";
			}
		    $xml_output .= "\t</ROW>\n"; 
		}
		
		if(strlen($xml_output)>=$bufferSize){
			fwrite($filewrite, $xml_output);
			unset($xml_output);
			$xml_output = '';
		}
		
	} 
	
	$xml_output .= "</DATA>"; 
	fwrite($filewrite, $xml_output);
	fclose($filewrite);
	//$mysqli->close();
	return true;
}


sql2xml("SELECT * FROM `DIMES`.`GeoInfoTbl`",'test.xml');



?>