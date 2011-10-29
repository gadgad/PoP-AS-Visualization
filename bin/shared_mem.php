<?php
class SharedMemory{
    private $nameToKey = array();
    private $key;
    private $id;
    
    function __construct($key = null){
        if($key === null){
            $tmp = tempnam('/tmp', 'PHP');
            $this->key = ftok($tmp, 'a');
            $this->id = shm_attach($this->key);
            $this->nameToKey[] = '';
            $this->nameToKey[] = '';
            $this->updateMemoryVarList();
            shm_put_var($this->id, 1, 1);
        }else{
            $this->key = $key;
            $this->id = sem_get($this->key);
            $this->refreshMemoryVarList();
            shm_put_var($this->id, 1, shm_get_var($this->id, 1) + 1);
        }
        if(!$this->id)
            die('Unable to create shared memory segment');
    }
    function __sleep(){
        shm_detach($this->id);
    }
    function __destruct(){
        if(shm_get_var($this->id, 1) == 1){
            // I am the last listener so kill shared memory space
            $this->remove();
        }else{
            shm_detach($this->id);
            shm_put_var($this->id, 1, shm_get_var($this->id, 1) - 1);
        }
    }
    function __wakeup(){
        $this->id = sem_get($this->key);
        shm_attach($this->id);
        $this->refreshMemoryVarList();
        shm_put_var($this->id, 1, shm_get_var($this->id, 1) + 1);
    }
    function getKey(){
        return $this->key;
    }
    function remove(){
        shm_remove($this->id);
    }
    function refreshMemoryVarList(){
        $this->nameToKey = shm_get_var($this->id, 0);
    }
    function updateMemoryVarList(){
        shm_put_var($this->id, 0, $this->nameToKey);
    }
    function __get($var){
        if(!in_array($var, $this->nameToKey)){
            $this->refreshMemoryVarList();
        }
        return shm_get_var($this->id, array_search($var, $this->nameToKey));
    }
    function __set($var, $val){
        if(!in_array($var, $this->nameToKey)){
            $this->refreshMemoryVarList();
            $this->nameToKey[] = $var;
            $this->updateMemoryVarList();
        }
        shm_put_var($this->id, array_search($var, $this->nameToKey), $val);
    }
}
?>