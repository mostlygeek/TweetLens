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
$type = $_POST['type']; 

if ($type != 'status' && $type != 'message') {
    header('HTTP/1.0 400 Bad Request: invalid type value provided or missing'); 
    die();    
}

try {
    $user = Session::getFirstUser(); 
    if ($type == 'status') {
        $status = new StatusStack($user); 
        $status->setStatusRead($twId);
    }
    if ($type == 'message') {
        $message = new MessageStack($user); 
        $message->setMessageRead($twId);
    }
    echo "ok";
} catch (Exception $e) {
    header('HTTP/1.0 501 Bad Request: invalid read value provided');
    print_r($e); 
    die(); 
}