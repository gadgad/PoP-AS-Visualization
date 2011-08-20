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
	
	$host = $args['host'];
	$user = $args['user'];
	$pass = $args['pass'];
	$database = $args['database'];
	$port = $args['port'];
	
	$query = $args['query'];
	$PoPTblName=$args['PoPTblName'];
	$pop=$args['pop'];
	$as=$args['as'];
	$EdgeTblName=$args['EdgeTblName'];
	$edge=$args['edge'];
	$popIP=$args['popIP'];

	
	// pop query
	$query1 = 'create table `DIMES_POPS_VISUAL`.`'.$PoPTblName.'` (select * from `'.$database.'`.`'.$pop.'` where ASN in('.$as.')) order by ASN';
			
	// edge query			
	$query2 = 'create table `DIMES_POPS_VISUAL`.`'.$EdgeTblName.'` (select edges.*, src.PoPID Source_PoPID, dest.PoPID Dest_PoPID FROM '.$edge.' edges left join '.$popIP.' src on(edges.SourceIP = src.IP) left join '.$popIP.' dest on(edges.DestIP = dest.IP) where edges.SourceAS in ('.$as.') AND edges.DestAS in ('.$as.'))';
	
	$selected_query	= ($query==1)?$query1:$query2;	
		
	echo "host:$host\n user:$user\n pass:$pass\n db:$database\n port:$port\n";
	
	try {		 
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
				
		if ($mysqli->connect_error) {
		   exit('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
		
		$processID = $mysqli->thread_id;
		echo "processID:".$processID."\n";
		myFlush();
		//$mysqli->autocommit(FALSE);	
		$mysqli->select_db($database);
		//$mysqli->options("max_allowed_packet=50M");
		//$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 300);
		
		$result = $mysqli->query($selected_query);
		if($mysqli->error) throw new Exception('error message');
		
		//$mysqli->commit();
		//$mysqli->close();
		//exit();
	} catch (Exception $e) {
        //var_dump($e->getMessage());
        echo('Mysqli Error (' . $mysqli->errno . '): '. $mysqli->error);
    }

?>
