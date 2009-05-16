<?php
/* this file contains all the settings for your installation. 
   tweet as necessary 
   
   Copy this file to settings.php as config.php requires it.
   */

// MySQL Information
$_config['db'] = array (
    'host'  => 'localhost',
    'user'  => 'username',
    'pass'  => 'password',
    'name'  => 'dbname'
); 
$_config['mc'] = array ( // memcache
    'host' => 'localhost',
    'port' => 11211
); 