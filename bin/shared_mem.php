<?php
class SharedMemory { 
        public $key;            //unique identifier for the shared memory block 
        public $shm;            //holds shared memory resource 
        public $mutex;            //holds the mutex 
        public $size;            //bytes to allocate 
         
        public function __construct($key=3354354334, $size=10000) {        //default key, can be overridden, same for size
            $this->key = $key; 
            $this->size = $size; 
            $this->Attach();    //create resources (shared memory + mutex) 
        } 
         
        //create resources 
        public function Attach() { 
            $this->shm = shm_attach($this->key, $this->size);    //allocate shared memory 
            $this->mutex = sem_get($this->key, 1);        //create mutex with same key 
        } 
         
        //write to shared memory 
        public function Set($var) { 
            sem_acquire($this->mutex);    //block until released 
            shm_put_var($this->shm, $this->key, $var);    //store var  
            sem_release($this->mutex);    //release mutex     
        } 
         
        //read from shared memory 
        public function Get() { 
            sem_acquire($this->mutex);    //block until released 
            $var = @shm_get_var($this->shm, $this->key);    //read var         
            sem_release($this->mutex);    //release mutex 
            return $var;         
        } 
}
?>