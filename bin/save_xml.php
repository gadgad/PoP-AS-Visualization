<?php
    function save_xml_file($xml_string,$filename){
    	$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml_string);
		$dom->save($filename);
    }
?>