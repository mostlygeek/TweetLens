<?php
require_once('../../libraries/config.php'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

header('Content-Type: text/plain');
$recipient = $_POST['recipient_name']; 
$text = $_POST['text']; 

$recipient = trim($recipient);
if (preg_match('/[^a-z0-9_]/i',$recipient)) {
    header('HTTP/1.0 400 Bad Request: recipient name is invalid'); 
    die();    
}

$text = trim($text); 
$text = str_replace("\n",'',$text);
$text = str_replace("\r",'',$text);

if ($text == '') {
    header('HTTP/1.0 400 Bad Request: message is required'); 
    die();
}

try {
    $m = new MessageStack(Session::GetFirstUser());
    $resp = $m->sendDirectMessage($recipient,$text);
    $resp->show = 1; 
    $resp->read = 0; 
    echo json_encode($resp); 
} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getMessage()); 
    echo $e->getTwitterMessage();
} catch (Exception $e) {
    $msg = "Something Broke: ".$e->getMessage(); 
    header('HTTP/1.0 502 Bad Gateway'); 
    echo $msg;    
}