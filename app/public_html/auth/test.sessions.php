<?php
header('Content-Type: text/plain');
require_once('../../libraries/config.php');
require_once(LIB_DIR.'session.lib.php'); 

$user = 'mostlygeek'; 
$pass = 'phinkTweety'; 

if ($_GET['l'] == 'out') { 
    session_destroy(); 
    header('Location: '.$_SERVER['PHP_SELF']); 
    die();
}

if (!Session::getAccount($user)) {
    echo "Logging in\n";
    $tw = new Twitter();
    $twuser = $tw->setCredentials($user,$pass,true);
    Session::addAccount($twuser);
}

print_r(Session::GetFirstUser());

//print_r($_SESSION);
