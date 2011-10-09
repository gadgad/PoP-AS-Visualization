<?php
	$valid_requests = array('index.php','admin.php','visual_frontend.php');
    echo in_array(basename($_SERVER['REQUEST_URI']), $valid_requests)? 'true':'false';
    echo "</br>";
    echo in_array('index.php', $valid_requests)? 'true':'false';
?>