<?php
require_once('../../libraries/config.php'); 
if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

header('Content-type: text/plain'); 

try {
    $stack = new MessageStack(Session::GetFirstUser()); 
    
    if (isset($_GET['sinceid'])) {
        $sinceid = $_GET['sinceid']; 
        if (preg_match('[^0-9]',$sinceid)) {
            throw new Exception('HTTP/1.0 400 sinceid value is not valid'); 
        }
        $messages = $stack->GetSentMessagesLatest($sinceid);
    } elseif (isset($_GET['page'])) {
        $page = $_GET['page']; 
        if (preg_match('[^0-9]',$page)) {
            throw new Exception('HTTP/1.0 400 page value is not valid'); 
        }
        $messages = $stack->GetSentMessagesPage($page);
    } else {
        $messages = $stack->GetSentMessagePage(1);
    }
    
    echo json_encode($messages);   

} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getMessage()); 
    echo $e->getTwitterMessage();
} catch (Exception $e) {
    header('HTTP/1.0 502 Bad Gateway'); 
    echo "Something Broke: ".$e->getMessage(); 
}