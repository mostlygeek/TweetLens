<?php
require_once('../../libraries/config.php'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

if (!isset($_POST['twId'])) {
    header('HTTP/1.0 400 Bad Request: twId required'); 
    die();
}
    
$twId = $_POST['twId'];
$show = $_POST['show'];
$type = $_POST['type']; 

if ($type != 'status' && $type != 'message') {
    header('HTTP/1.0 400 Bad Request: invalid type value provided or missing'); 
    die();    
}

if ($show != 1 && $show != 0) {
    header('HTTP/1.0 400 Bad Request: invalid show value provided'); 
    die();
}

try {
    $user = Session::getFirstUser(); 
    if ($type == 'status') {
        $status = new StatusStack($user); 
        $status->setStatusShow($twId,$show);
    }
    
    if ($type == 'message') {
        $message = new MessageStack($user); 
        $message->setMessageShow($twId,$show);
    }
    echo "ok";
} catch (Exception $e) {
    header('HTTP/1.0 501 Bad Request: invalid show value provided');
    print_r($e); 
    die(); 
}