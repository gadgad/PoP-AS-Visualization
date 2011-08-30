<?php
    //phpinfo();
    
    if (stristr(PHP_OS, 'WIN')) { 
	 echo "running on windows!\n";
	} else {
	 echo PHP_OS . "\n"; 
	 echo "running on linux?";
	}
?>