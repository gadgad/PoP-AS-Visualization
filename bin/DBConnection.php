<?php
	
	class DBConnection extends mysqli {
		
		private $retries;
		private $limit;
		private $err_lst;
		private $sleep_time;
		
	    public function __construct($host, $user, $pass, $db, $port, $limit) {
	        parent::init();
			
			$this->retries = 0;
			$this->limit = isset($limit)? $limit : 10;
			$this->sleep_time = 3;
			$this->err_lst = array(2006,2013);
			
			/*
	        if (!parent::options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
	            die('Setting MYSQLI_INIT_COMMAND failed');
	        }

	
	        if (!parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, 30)) {
	            die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
	        }
			 * 
			 */
			
			//$mysqli->options("max_allowed_packet=50M");
			//$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 300);
			
			$result = $this->real_connect($host, $user, $pass, $db, (int)$port); 
	        while(!$result || $this->connect_error) {
				if(in_array($this->connect_errno,$this->err_lst) && ($this->retries < $this->limit)){
					//echo "retrying...\n";
					$this->close();
					$this->init();
					$this->retries++;
					sleep($this->sleep_time);
					$result = $this->real_connect($host, $user, $pass, $db, (int)$port);
				} else {
					//die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
					//die('Connect Error (' . $this->connect_errno . ') '. $this->connect_error);
					return NULL;
				}

	    	}	
		}
	}
?>