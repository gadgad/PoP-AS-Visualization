<?php
    class userData {
    	public $user_data;
		private $username;
		private $filename;
		private $queryID;
		
    	public function __construct($username,$queryID){
    		$this->username = $username;
			$this->filename = 'users/'.$username.'.data';
			$this->queryID = (isset($queryID))? $queryID : 'global';
			$this->load_data();
		}
		
		public function load_data(){
			if(file_exists($this->filename)){
				$file_handle = fopen($this->filename,"r") or die("can't open ".$this->filename."\n");
				$str = fgets($file_handle);
				$this->user_data =  unserialize($str);
				fclose($file_handle);
			} else {
				$this->user_data = array();
			}
		}
		
		public function save_data(){
			$file_handle = fopen($this->filename, "w") or die("can't open ".$this->filename."\n");;
			fwrite($file_handle,  serialize($this->user_data));
			fclose($file_handle);
		}
		
		public function setGlobal($param,$value) {
			$QID = $this->queryID;
			if(isset($_POST['submitted'])) {
				$QID = $_POST['queryID'];
				if(is_bool($value)){
					define($param,isset($_POST[$param]));
					$this->user_data[$QID][$param]=(isset($_POST[$param])? 1:0);
				} else if(isset($_POST[$param])){
					define($param,$_POST[$param]);
					$this->user_data[$QID][$param]=$_POST[$param];
				}
			} else if(isset($this->user_data[$QID][$param])){
					define($param,$this->user_data[$QID][$param]);
			} else { // not submitted and no data in userData
				if(is_bool($value)){
					$this->user_data[$QID][$param]=(($value)?1:0);
					define($param,(($value)?1:0));
				} else {
					$this->user_data[$QID][$param]=$value;
					define($param,$value);
				}	
			}
		}
		
    }
?>