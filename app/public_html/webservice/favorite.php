<?php
require_once('../../libraries/config.php'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

if (!isset($_POST['twId']) || ($_POST['val'] != 'true' && $_POST['val'] != 'false')) {
    header('HTTP/1.0 400 Bad Request missing information'); 
    die();
}

    
$twId = $_POST['twId'];
$val = $_POST['val']; 

try {
    $user = Session::getFirstUser(); 
    $status = new StatusStack($user); 
    $item = $status->setStatusFavorite($twId,$val);
    echo json_encode($item);
} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getCode().' Twitter: '.$e->getMessage());
} catch (Exception $e) {
    header('HTTP/1.0 502 Something broke. '); 
    echo json_encode($e->getMessage()); 
}
