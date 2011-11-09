<?php
/*
 * colorManager Class
 * provided methods for loading & storing 
 * (user-specific & global) ASN color associations
 * as persistent objects on the server-side . 
 */
	require_once("bin/color.php");
	require_once("bin/userData.php");
	
    class colorManager
	{
		public $GLOBAL_COLOR_LIST;
		public $USER_QID_COLOR_LIST;
		public $USER_GLOBAL_COLOR_LIST;
		
		private $COLOR_LIST;
		
		private $global_data_file;
		private $user_data_file;
		private $session;
		private $user_data;
		private $queryID;
		private $username;
		private $as_list;
		
		public function __construct($user,$queryID)
		{
			$this->queryID = $queryID;
			$this->username = $user;
			
			$this->GLOBAL_COLOR_LIST = array();
			$this->USER_QID_COLOR_LIST = array();
			$this->USER_GLOBAL_COLOR_LIST = array();
			$this->COLOR_LIST = array();
			$this->as_list = NULL;
			
			$this->global_data_file = 'data/ASN_color.data';
			$this->user_data_file = 'data/'.$user.'.data';
			
			$this->session = new userData($this->username,$this->queryID);
			$this->user_data =& $this->session->user_data;
			
			$this->load_global_color_list();
			$this->load_user_color_list();
			$this->build_color_list();
		}
		
		public function getASList(){
			if($this->as_list == NULL){
				$queries = simplexml_load_file('xml/query.xml');							
				$result = $queries->xpath('/DATA/QUERY[queryID="'.$this->queryID.'"]');
				if(empty($result)){
					return NULL;
				}
				$result = $result[0];
				$as = (string)$result->allAS;
				$as = trim($as,"'");
				$this->as_list = explode("','",$as);
				$this->as_list = array_map('intval',$this->as_list);
			}
			return $this->as_list;
		}
		
		public function getColorList(){
			return $this->COLOR_LIST;
		}
		
		private function build_color_list(){
			if(isset($this->GLOBAL_COLOR_LIST['asn'])){
				foreach($this->GLOBAL_COLOR_LIST['asn'] as $asn=>$color){
					$this->COLOR_LIST['asn'][$asn] = $color;
					$this->COLOR_LIST['color'][$color->web_format()] = $asn;
				}
			}
			if(isset($this->USER_QID_COLOR_LIST['asn'])){
				foreach($this->USER_QID_COLOR_LIST['asn'] as $asn=>$color){
					$this->COLOR_LIST['asn'][$asn] = $color;
					$this->COLOR_LIST['color'][$color->web_format()] = $asn;
				}
			}
			if(isset($this->USER_GLOBAL_COLOR_LIST['asn'])){
				foreach($this->USER_GLOBAL_COLOR_LIST['asn'] as $asn=>$color){
					$this->COLOR_LIST['asn'][$asn] = $color;
					$this->COLOR_LIST['color'][$color->web_format()] = $asn;
				}
			}
		}
		
		private function load_user_color_list(){
			$user_data = $this->user_data;
			$queryID = $this->queryID;
			if(isset($user_data[$queryID]) && isset($user_data[$queryID]['asn-color'])){
				foreach($user_data[$queryID]['asn-color'] as $asn=>$color){
					$this->USER_QID_COLOR_LIST['asn'][$asn] = $color;
					$this->USER_QID_COLOR_LIST['color'][$color->web_format()] = $asn;
				}
			}
			if(isset($user_data['global']) && isset($user_data['global']['asn-color'])){
				foreach($user_data['global']['asn-color'] as $asn=>$color){
					$this->USER_GLOBAL_COLOR_LIST['asn'][$asn] = $color;
					$this->USER_GLOBAL_COLOR_LIST['color'][$color->web_format()] = $asn;
				}
			}	
		}
		
		public function save_user_color_list(){
			$user_data =& $this->user_data;
			if(isset($this->USER_GLOBAL_COLOR_LIST['asn']) && !empty($this->USER_GLOBAL_COLOR_LIST['asn'])){
				foreach($this->USER_GLOBAL_COLOR_LIST['asn'] as $asn=>$color){
					$user_data['global']['asn-color'][$asn] = $color;
				}
			}
			if(isset($this->USER_QID_COLOR_LIST['asn']) &&  !empty($this->USER_QID_COLOR_LIST['asn'])){
				foreach($this->USER_QID_COLOR_LIST['asn'] as $asn=>$color){
					$user_data[$this->queryID]['asn-color'][$asn] = $color;
				}
			}
			$this->session->save_data();	
		}
		
		private function load_global_color_list(){
			$filename = $this->global_data_file;
			if(file_exists($filename)){
				$file_handle = fopen($filename,"r") or die("can't open ".$filename."\n");
				$str = fgets($file_handle);
				$this->GLOBAL_COLOR_LIST =  unserialize($str);
				fclose($file_handle);
			} else {
				$this->GLOBAL_COLOR_LIST = array();
			}
		}
		
		public function save_global_color_list(){
			$filename = $this->global_data_file;
			$file_handle = fopen($filename, "w") or die("can't open ".$filename."\n");;
			fwrite($file_handle,  serialize($this->GLOBAL_COLOR_LIST));
			fclose($file_handle);
		}
	}
?>