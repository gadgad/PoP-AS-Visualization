<?php
$filename = 'ASN_info.xml';
if (file_exists($filename)) {
    $asn_info_xml = simplexml_load_file($filename);
    //print_r($asn_info_xml);
} else {
    exit('Failed to open '.$filename);
}
    $asn_info_xml = simplexml_load_file("ASN_info.xml");
	$result = $asn_info_xml->xpath("/DATA/ROW[ASNumber=17654]");
	
	print_r($result);
	var_dump($result);
	
	foreach($result as $asn)
	{
		echo "country: $asn->Country , ISPName: $asn->ISPName </BR>";
	}
	
	var_dump(empty($result));
	echo($result[0]->Country ."</BR>");
	echo($result[0]->ISPName);
?>