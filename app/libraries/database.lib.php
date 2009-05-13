<?php

function & getDatabase() {
// Initialize a global memcache object. 
    static $database; 
    
    if (is_object($database))
        return $database; 
        
    $database = & new Database('localhost','username','pass','dbname');
    $database->connect(); 
    return $database;
}
