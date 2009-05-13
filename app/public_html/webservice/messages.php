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
            throw new HTTPException(400,'Sinceid value is not valid'); 
        }
        $stack->disableCache();
        $messages = $stack->GetMessagesLatest($sinceid);
    } elseif (isset($_GET['page'])) {
        $page = $_GET['page']; 
        if (preg_match('[^0-9]',$page)) {
            throw new HTTPException(400,'Page value is not valid'); 
        }
        $messages = $stack->GetMessagesPage($page);
    } else {
        $messages = $stack->GetMessagePage(1);
    }
    
    echo json_encode($messages);

} catch (TwitterException $e) {
    header('HTTP/1.0 '.$e->getMessage()); 
    echo $e->getTwitterMessage();
} catch (HTTPException $e) {
    header('HTTP/1.0 '.$e->getHTTPReason());
    echo $e->getMessage();
} catch (Exception $e) {
    header('HTTP/1.0 502 Bad Gateway'); 
    echo "Something Broke: ".$e->getMessage(); 
}