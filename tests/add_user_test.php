<?php

function ret_res($message, $type)
{
	header('Content-type: application/json');
	echo json_encode(array("result"=>$message ,"type"=>$type));
	die();	
}


function edit_xml(){
	$xml = new DOMDocument('1.0', 'utf-8');
	$xml->formatOutput = true;
	$xml->preserveWhiteSpace = false;
	$xml->load('xml\test.xml');
	
	
}

$username = 'keren';
$queryID = '9832929bba3ad127476b0cc880f3f0dc';
$queries = simplexml_load_file('xml\test.xml');							
$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users');

if($result!=FALSE) // this query already exists
{
	if($result[0]->user!=$username){
		$result[0]->addChild('user', $username);
		$queries->asXML('xml\test_output.xml');
		ret_res("query already assigned to a different user,adding current user to record","GOOD");
	} else {
		ret_res("query already exists!","ALL_COMPLETE");
	}
}
	

?>
