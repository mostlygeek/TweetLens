<?php
function & getMemcacheObject() {
// Initialize a global memcache object. 
    static $memcache; 
    if (is_object($memcache))
        return $memcache; 
    $memcache = & new Memcache; 
    
    if (!$memcache->connect('127.0.0.1', 11211))
        throw new Exception('Could not connect to Memcache Server');
    return $memcache; 
}