<?php
require_once('../../../libraries/config.php'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

if (!preg_match('/[0-9]{1,16}/',$_GET['id'])) {
    header('HTTP/1.0 400 Bad Request: id is invalid');
    die();
}

try {
    $user = Session::GetFirstUser();
    $tw = new TwitterCache(); 
    $tw->setTwitterUser($user);
    $status = $tw->getStatus($_GET['id']);
    $status->show = 1; 
    $status->read = 0; 
    echo json_encode($status);
} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getMessage()); 
    echo $e->getTwitterMessage();
} catch (Exception $e) {
    $msg = "Something Broke: ".$e->getMessage(); 
    header('HTTP/1.0 502 Bad Gateway'); 
    echo $msg;    
}