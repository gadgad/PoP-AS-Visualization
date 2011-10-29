<?php

define(kSHARED_FOLDER, "tmp/");
define(kSHARED_MAX_ATTEMPS, 10);
define(kSESSION_SHARED, "shared_");

class Shared {
    var $id = 0;
    var $filename = '';
    var $filepointer;
    
    var $data = array();
    var $date = 0;
    
    public function __construct($id) {
        $this->id = $id;
        
        $this->filename = kSHARED_FOLDER.$this->id;
        
        if(empty($this->filename))
        {
            print "no filename";
            return false;        
        }
        
        $this->date = $_SESSION[kSESSION_SHARED.$id];
            
    }
    
    function clear() {
        if ($this->id == null)
        {
            return false;
        }
            
        $counter = 0;
        ignore_user_abort(true);
        if(($this->filepointer = @fopen($this->filename, "w")) == false) {        
            ignore_user_abort(false);
            return false;
        }

        while(true) {
            if ($counter >= kSHARED_MAX_ATTEMPS) {
                fclose($this->filepointer);
                ignore_user_abort(false);
                return false;
            }
            
            if(flock($this->filepointer, LOCK_EX) == false) {
                $counter++;
                usleep(rand(1, 25000));
            }
            else
                break;
        }

        if(flock($this->filepointer, LOCK_UN) == false) {        
            ignore_user_abort(false);
            return false;
        }
        
        unset($this->data);
        $this->data = array();

        fclose($this->filepointer);
        $this->date = $_SESSION[kSESSION_SHARED.$id] = filemtime($this->filename);
        ignore_user_abort(false);
        
        return true;
    }
    
    function setObjectForKey($value, $key) {
        if ($this->id == null)
            return false;
            
        $counter = 0;
        ignore_user_abort(true);
        if(($this->filepointer = @fopen($this->filename, "a+")) == false) {        
            ignore_user_abort(false);
            print "can not open file<br>";
            return false;
        }

        while(true) {
            if ($counter >= kSHARED_MAX_ATTEMPS) {
                fclose($this->filepointer);
                print("1 aborted...");
                ignore_user_abort(false);
                return false;
            }
            
            $block;
            if(flock($this->filepointer, LOCK_EX, $block) == false) {
                $counter++;
                print("1 waiting...");
                usleep(rand(1, 25000));
            }
            else
                break;
        }
        
        $data = file_get_contents($this->filename);
        $array = array();
        if (!empty($data))
            $array = unserialize($data);

        $array[$key] = $value;
        $data = serialize($array);
        ftruncate($this->filepointer, 0);
        fseek($this->filepointer, 0, SEEK_SET);
        fwrite($this->filepointer, $data);
        
        $this->data = $array;
        
        if(flock($this->filepointer, LOCK_UN) == false) {        
            ignore_user_abort(false);
            return false;
        }

        fclose($this->filepointer);
        $this->date = $_SESSION[kSESSION_SHARED.$id] = filemtime($this->filename);
        ignore_user_abort(false);
        
        return true;
    }
    
    function getObjectForKey($key)    {
        if ($this->id == null)
            return null;
            
        $counter = 0;
        ignore_user_abort(true);
    
        if(($this->filepointer = @fopen($this->filename, "a+")) == false) {        
            ignore_user_abort(false);
            print("can not open<br>");
            return null;
        }

        if ($this->date == filemtime($this->filename)) {            
            fclose($this->filepointer);
            return $this->data[$key];
        }
        
        while(true) {
            if ($counter >= kSHARED_MAX_ATTEMPS) {
                fclose($this->filepointer);
                ignore_user_abort(false);
                print("2 aborted<br>");
                return null;
            }
            
            if(flock($this->filepointer,  LOCK_SH ) == false) {
                $counter++;
                print("2 waiting...<br>");
                usleep(rand(1, 25000));
            }
            else
                break;
        }
        
        fseek($this->filepointer, 0);
        $data = file_get_contents($this->filename);
        $array = array();
        if (!empty($data))
            $array = unserialize($data);
            
        $data = $array[$key];
        $this->data = $array;
        
        if(flock($this->filepointer, LOCK_UN) == false) {        
            ignore_user_abort(false);
            return $data;
        }

        fclose($this->filepointer);
        $this->date = $_SESSION[kSESSION_SHARED.$id] = filemtime($this->filename);
        ignore_user_abort(false);
        
        return $data;
    }
}
?>