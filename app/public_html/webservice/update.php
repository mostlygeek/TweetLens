<?php 
require_once('../../libraries/config.php'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

header('Content-Type: text/plain');

if (isset($_POST['rid'])) {
    $rid = preg_replace('/[^0-9]/','',$_POST['rid']); 
}

$status = $_POST['text'];

// clean up status 
$status = trim($status); 
$status = str_replace("\n",'',$status);
$status = str_replace("\r",'',$status);

if ($status == '') {
    header('HTTP/1.0 400 Bad Request: status is required'); 
    die();
}

try {
    $tw = new StatusStack(Session::GetFirstUser());
    $status = $tw->updateStatus($status,$rid);
    Session::updateUserStatus($status); // update the user's status
    $status->show = 1; 
    $status->read = 0;
    $status = json_encode($status);
    echo $status; 
} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getMessage()); 
    echo $e->getTwitterMessage();
} catch (Exception $e) {
    header('HTTP/1.0 502 Bad Gateway'); 
    echo "Something Broke: ".$e->getMessage(); 
}