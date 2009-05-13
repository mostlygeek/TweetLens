<?php
header('Content-Type: text/plain');
require_once('../../libraries/config.php');

$username = trim($_POST['username']); 
$password = trim($_POST['password']); 

try {
    $tw = new Twitter();
    $user = $tw->setCredentials($username,$password,true);
    
    if ($user !== false) {
        Session::addAccount($user);
        echo "ok";
    } else {
        echo "failed";
    }
} catch (Exception $e) {
    print_r($e); 
    die(); 
}