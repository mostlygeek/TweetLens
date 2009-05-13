<?php

class Bucket {
    private $mc; // memcache object
    private $bucketId;
    private $cacheTime = 86400;
    
    public function __construct($bucketId,$cacheTime=null) {
        if ($bucketId == '')
            throw new Exception('Bucket ID is invalid');
        $this->bucketId = $bucketId; 
        
        $this->mc = getMemcacheObject(); 
        if(get_class($this->mc) != 'Memcache')
            throw new Exception("Unable to get Memcache Object");
        
        if ($cacheTime != null)
            $this->cacheTime = $cacheTime;
    }
    
    public function getBucketId() {
        return $this->bucketId; 
    }
    
    public function addMultiple($array) {
        if (!is_array($array)) 
            throw new Exception('array required');
            
        foreach ($array as $i => $item) {
            $this->add($i,$item);
        }
    }
    public function add($itemId,$data) {
        $key = $this->makeKey($itemId);
        // try to replace, and then add, and then fail to exception
        $this->mc->delete($key);
        if ($this->mc->add($key,$data,null,$this->cacheTime) === false)
            throw new Exception("Unable to add item [$key] to Bucket"); 
    }
    public function get($itemId) {
        $key = $this->makeKey($itemId);
        $data = $this->mc->get($key);
        if (is_array($key)) {
            $new = array();
            foreach ($data as $i => $item) {
                $n = str_replace($this->bucketId.'-','',$i);
                $new[$n] = $item;
            }
            return $new;
        }
        return $data;
    }
    public function del($itemId) {
        $key = $this->makeKey($itemId);
        return $this->mc->delete($key);
    }
    protected function makeKey($itemId) {
        if (is_array($itemId)) {
            foreach ($itemId as $id)
                $key[] = $this->bucketId.'-'.$id;
        } else {
            $key = $this->bucketId.'-'.$itemId;
        }
        return $key; 
    }
    
    // Helper function
    public static function newBucket($userId,$type,$cache=null) { 
        return new Bucket("bkt-$userId-$type",$cache);
    }
}