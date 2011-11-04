<?php
    require_once 'bin/load_config.php';
	
	echo SITE_URL."</br>";
	echo MAIL_FROM."</br>";
	echo $MAIL_MESSAGES_MAP["invitation"]["subject"]."</br>";
	echo $MAIL_MESSAGES_MAP["invitation"]["body"]."</br>";
?>