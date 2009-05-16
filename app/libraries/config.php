<?php
require_once('../settings.php'); // pull in the installation specific settings

/* These are apache ENV variables for controlling the application 
   mode */
if (!isset($_SERVER['TWEETLENS_APP_MODE']))
    die('TWEETLENS_APP_MODE not defined'); 

if (!isset($_SERVER['TWEETLENS_VERSION_ID'])) {
    die('TWEETLENS_VERSION_ID not defined'); 
} else {
    define('VERSION_KEY',$_SERVER['TWEETLENS_VERSION_ID']);  // some arbitrary value for the version
}

if ($_SERVER['TWEETLENS_APP_MODE'] == 'DEV') {
    define('IN_PRODUCTION',false);   // flag for if we are in production or not.    
} elseif ($_SERVER['TWEETLENS_APP_MODE'] == 'PROD') {
    define('IN_PRODUCTION',true);   // flag for if we are in production or not.    
} else {
    die('Unrecognized App Mode');
}

define('LIB_DIR',dirname(__FILE__).'/'); // all libraries with config.php
define('JS_DIR',dirname(__FILE__).'/javascript/'); // where all the javascript files are

function __autoload($class) {
    if (file_exists(LIB_DIR.'classes/'.$class.'.class.php')) {
        require_once(LIB_DIR.'classes/'.$class.'.class.php');    
    } else {
        $class = strtolower($class);
        require_once(LIB_DIR.'classes/'.$class.'.class.php');
    }
}

// include all the standard libraries
require_once('memcache.lib.php'); 
require_once('database.lib.php');
require_once('session.lib.php');