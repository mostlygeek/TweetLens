<?php
function & getMemcacheObject() {
// Initialize a global memcache object. 
    static $memcache;
    global $_config; 
    if (is_object($memcache))
        return $memcache; 
    $memcache = & new Memcache; 
    
    if (!$memcache->connect($_config['mc']['host'],$_config['mc']['port']))
        throw new Exception('Could not connect to Memcache Server');
    return $memcache; 
}