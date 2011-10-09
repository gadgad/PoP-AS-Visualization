<?php
    class testME {
    	public function __construct(){
    		define('FOO','BAR');
    	}
    }
	
	$tm = new testME();
	echo "FOO = ".FOO;
?>