<?php
	require_once("verify.php");
    require_once("bin/xml_writer.php");
    require_once("bin/kml_writer.php");
    require_once("bin/idgen.php");
    
    $asList = array(174,209);
    $popTbl = 'PoPLocationTbl_2010_week_31';
    $edgeTbl = 'IPEdgesMedianTbl_2010_31';
    $blade = 'B4';
    
    //$idg = new idGen($edgeTbl,$popTbl,$asList);
	$idg = new idGen('25210365d7d16aab0e6b87d890e9526e');
    $queryID = $idg->getDirName();
    echo $idg->getDirName()."</BR>";
    echo $idg->getPoPTblName() . "</BR>";
    echo $idg->getEdgeTblName() . "</BR>";
    
    /////////////////////////////////////////////
    
    /*
    $xw = new xml_Writer($blade,$queryID);
    if($xw->writeXML()){
    	echo "success!";
    }
	else {
		echo "failure :(";
	}
	
	echo "</BR>";
	*/
	
	//write generated KML file to disk
	$kmlWriter = new kmlWriter($queryID);
	if($kmlWriter->writeKMZ())
	{
		$filename=$kmlWriter->getFileName();
		echo "generated $filename successfully! :)";
	}
		 
?>