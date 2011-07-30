<?php
ini_set('display_errors', '0');     # don't show any errors...
error_reporting(E_ALL | E_STRICT);  # ...but do log them

// include required files
require_once 'XML/Query2XML.php';
require_once 'MDB2.php';

try {
    // initialize Query2XML object
    $q2x = XML_Query2XML::factory(MDB2::factory('mysql://codeLimited@localhost:5554/DIMES_DISTANCES'));
    
    // generate SQL query
    // get results as XML
    $sql = "SELECT 
                    edges.*,
                    src.PoPID src_pop,
                    dest.PoPID dest_pop,
                    locations1.LAT2 Source_LAT,
                    locations1.LNG2 Source_LNG,
                    locations2.LAT2 Dest_LAT,
                    locations2.LNG2 Dest_LNG
                    FROM IPEdgesMedianTbl_2010_31 edges
                    left join PoPIPTbl_2010_week_31 src on(edges.SourceIP = src.IP)
                    left join PoPIPTbl_2010_week_31 dest on(edges.DestIP = dest.IP)
                    left join PoPLocationTbl_2010_week_31 locations1 on (src.PoPID = locations1.PoPID)
                    left join PoPLocationTbl_2010_week_31 locations2 on (dest.PoPID = locations2.PoPID)
                    where edges.SourceAS in (174,209) AND edges.DestAS in (174,209)
                    and locations1.LAT2 is not null and locations2.LAT2 is not null
                    limit 1000;";
    $xml = $q2x->getFlatXML($sql);
    
    // send output to browser
    header('Content-Type: text/xml');
    $xml->formatOutput = true;
    echo $xml->saveXML();
} catch (Exception $e) {
    echo $e->getMessage();
}

?> 
