<?php
include("../bin/load_config.php");

$host = "localhost:5554"; 
$user = "codeLimited"; 
$pass = ""; 
$database = "DIMES_DISTANCES";
 
if(isset($pass) && $pass!="" ){
	$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
} else {
	$linkID = mysql_connect($host, $user) or die("Could not connect to host.");
} 

mysql_select_db($database, $linkID) or die("Could not find database."); 

$query = "show processlist"; 
$resultID = mysql_query($query, $linkID) or die("Data not found."); 

$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
$xml_output .= "<DATA>\n"; 

$num = mysql_num_rows($resultID);
for($x = 0 ; $x < $num ; $x++){ 
    $row = mysql_fetch_assoc($resultID);
    $xml_output .= "\t<ROW>\n"; 
	foreach($row as $key => $value){
		$xml_output .= "\t\t<".$key.">" .$value . "</".$key.">\n";
	}
    $xml_output .= "\t</ROW>\n"; 
} 
$xml_output .= "</DATA>"; 

if ($num != 0) {
/*
//////////////////////////////////////////////////////////////////////////////////////////////
	$day = date("m-d-y-");
	srand( microtime() * 1000000);
	$randomnum = rand(10000,99999);
	$file_ext = $day.$randomnum.'.xml';
	$filename = ('xml/'.$file_ext);
	
	// define initial write and appends
	$filewrite = fopen($filename, "w");
	//$fileappend = fopen($filename, "a");
	
	// open file and write header:
	fwrite($filewrite, $xml_output);
	fclose($filewrite);
//////////////////////////////////////////////////////////////////////////////////////////////
 * 
 */
header('Content-Type: application/xml; charset=ISO-8859-1');
echo $xml_output;
}


?> 
