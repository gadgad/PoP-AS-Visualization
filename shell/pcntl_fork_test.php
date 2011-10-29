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

if( !function_exists('ftok') ){
    function ftok($filename = "", $proj = "")
    {
        if( empty($filename) || !file_exists($filename) )
        {
            return -1;
        }
        else
        {
            $filename = $filename . (string) $proj;
            for($key = array(); sizeof($key) < strlen($filename); $key[] = ord(substr($filename, sizeof($key), 1)));
            return dechex(array_sum($key));
        }
    }
}
	
include_once("parsing_args.php");
$args = parseArgs($argv);

$tmp = tempnam('tmp', 'PHP');
$shm_key = ftok($tmp, 'a');
echo "key: $shm_key\n";
//$shm_key = 0xff3;
//@$shm_id = shmop_open($shm_key, "c", 0666, 1024);
$shm_id = shm_attach($shm_key);
if (!empty($shm_id)) {
           echo "shared memory exists!\n";
} else {
           echo "shared memory doesnt exist!\n";
}	
shm_remove($shm_id);

//myFlush();

/*	
$pid=pcntl_fork();
if($pid == -1){
	exit("error during fork()!");
}
if($pid) {
     pcntl_waitpid($pid,$status,WUNTRACED);
     echo "In parent process! child pid is: $pid\n";
     
     // parent is responsible for closing shared mem!
     shmop_close($shm_id);
} else {
     echo "In child process! my pid is: ".posix_getpid()."\n";
}
*/

?>
