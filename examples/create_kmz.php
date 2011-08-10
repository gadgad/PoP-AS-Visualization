
                
<?php
$zip = new ZipArchive();
$filename = "temp/test.kmz";

if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
   exit("cannot open <$filename>\n");
}

$zip->addFile("08-09-11-28772.kml","doc.kml");
echo "numfiles: " . $zip->numFiles . "\n";
$zip->close();
?>