<?php
	function myFlush (){
	    echo(str_repeat(' ',256));
	    // check that buffer is actually set before flushing
	    if (ob_get_length()){           
	        @ob_flush();
	        @flush();
	        @ob_end_flush();
	    }   
	    @ob_start();
	}
	
	include_once("parsing_args.php");
	$args = parseArgs($argv);
	
	$foo = $args['foo'];
	sleep(10);
	echo "hello world , foo = " . $foo . "\n";


	

?>
