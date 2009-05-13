<?php
require_once('../../libraries/config.php'); 
if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

header('Content-type: text/plain'); 

try {
    $stack = new StatusStack(Session::GetFirstUser()); 
    
    if (isset($_GET['sinceid'])) {
        $sinceid = $_GET['sinceid'];
        if (preg_match('[^0-9]',$sinceid)) {
            throw new Exception('HTTP/1.0 400 sinceid value is not valid'); 
        }
        $stack->disableCache();
        $replies = $stack->getRepliesLatest($sinceid);
    } elseif (isset($_GET['page'])) {
        $page = $_GET['page']; 
        if (preg_match('[^0-9]',$page)) {
            throw new Exception('HTTP/1.0 400 page value is not valid'); 
        }
        $replies = $stack->getRepliesPage($page);
    } else {
        $replies = $stack->getRepliesPage(1);
    }
    
    echo json_encode($replies);

} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getCode().' Twitter: '.$e->getMessage());
} catch (Exception $e) {
    header('HTTP/1.0 502 Something broke. '); 
    echo json_encode($e->getMessage()); 
}
