<?php
require_once('../../libraries/config.php'); 
try {
    if (!Session::isLoggedIn())
        throw new HTTPException(401);         
        
    if (!isset($_POST['tid']))
        throw new HTTPException(400,'target id required'); 
        
    if (preg_match('/[^0-9]/',$_POST['tid']))
        throw new HTTPException(400,'target id value invalid'); 
        
    if (!isset($_POST['type']))
        throw new HTTPException(400,'target type required'); 
        
    if (Flags::getTargetCode($_POST['type']) == null)
        throw new HTTPException(400,'target type invalid'); 
        
    if (Flags::getFlagCode($_POST['ftype']) == null)
        throw new HTTPException(400,'flag type invalid'); 

    if (!isset($_POST['fval'])) {
        $flagValue = ''; 
    } else {
        $flagValue = substr($_POST['fval'],0,64);
    }
    
    $user = Session::GetFirstUser(); 
    $uid = $user->id; 
    Flags::setFlag($uid,
        $_POST['tid'], // target id
        Flags::getTargetCode($_POST['type']), // target type
        Flags::getFlagCode($_POST['ftype']), // flag type
        $flagValue);
    
    // remove from cache. next time a refresh is called it will refresh
    if ($_POST['type'] == 'STATUS') 
        $x = new StatusStack($user); 
    if ($_POST['type'] == 'MESSAGE')
        $x = new MessageStack($user);
        
    $x->uncacheFlag($_POST['tid']);
    
    echo "ok";
} catch (HTTPException $e) {
    header('HTTP/1.0 '.$e->getHTTPReason());
    echo $e->getMessage();
} catch (Exception $e) {
    header('HTTP/1.0 502 Bad Gateway'); 
    echo "Something Broke: ".$e->getMessage(); 
    print_r($e);
}