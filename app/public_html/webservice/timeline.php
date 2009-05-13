<?php
require_once('../../libraries/config.php'); 
header('Content-type: text/plain'); 

if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

try {
    $stack = new StatusStack(Session::GetFirstUser()); 
    
    if (isset($_GET['sinceid'])) {
        $sinceid = $_GET['sinceid'];
        if (preg_match('[^0-9]',$sinceid)) {
            throw new Exception('HTTP/1.0 400 sinceid value is not valid'); 
        }
        $stack->disableCache();
        $timeline = $stack->getTimelineLatest($sinceid);
    } elseif (isset($_GET['page'])) {
        $page = $_GET['page']; 
        if (preg_match('[^0-9]',$page)) {
            throw new Exception('HTTP/1.0 400 page value is not valid'); 
        }
        $timeline = $stack->getTimelinePage($page);
    } else {
        $timeline = $stack->getTimelinePage(1);
    }
    
    echo json_encode($timeline);

} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getCode().' Twitter: '.$e->getMessage());
} catch (Exception $e) {
    header('HTTP/1.0 502 Something broke. '); 
    echo json_encode($e->getMessage()); 
}
