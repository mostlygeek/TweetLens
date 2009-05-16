<?php

function & getDatabase() {
// Initialize a global memcache object. 
    static $database;
    global $_config; 
    
    if (is_object($database))
        return $database; 
        
    $database = & new Database($_config['db']['host'],
                               $_config['db']['user'],
                               $_config['db']['pass'],
                               $_config['db']['name']);
    $database->connect(); 
    return $database;
}
