<?php
require_once('../../libraries/config.php'); 
require_once(LIB_DIR.'json-safe.lib.php');
header('Content-type: text/plain'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

try {
    $user = Session::getFirstUser(); 
    $stack = new TwitterCache();
    $stack->setTwitterUser($user);
    if (isset($_GET['page'])) {
        $page = $_GET['page']; 
        if (preg_match('[^0-9]',$page)) {
            throw new Exception('HTTP/1.0 400 page value is not valid'); 
        }
        $favorites = $stack->getFavorites($user->id,$page);
    } else {
        $favorites = $stack->getFavorites($user->id,1);
    }
    echo json_encode($favorites);
} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getCode().' Twitter: '.$e->getMessage());
} catch (Exception $e) {
    header('HTTP/1.0 502 Something broke. '); 
    echo json_encode($e->getMessage()); 
}
