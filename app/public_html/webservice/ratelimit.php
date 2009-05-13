<?php
require_once('../../libraries/config.php'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

header('Content-Type: text/plain');
try {
    $tw = new Twitter();
    $tw->setTwitterUser(Session::GetFirstUser());
    $remain = $tw->getRateLimitStatus(); 
    echo $remain->remaining_hits;
} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getCode().' Twitter: '.$e->getMessage());
} catch (Exception $e) {
    header('HTTP/1.0 502 Something broke. '); 
    echo json_encode($e->getMessage()); 
}
