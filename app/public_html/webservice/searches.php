<?php
require_once('../../libraries/config.php'); 
if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

header('Content-type: text/plain'); 
try {
    $user = Session::GetFirstUser();
    $ownerId = $user->id;
    $db = getDatabase(); 

    $searchId = preg_replace('/[^0-9]/','',$_POST['searchId']); 
    $saveName = substr($_POST['saveName'],0,64); 
    $q = substr($_POST['query'],0,140);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['t'] == 'save') {
        if (strlen(trim($saveName)) == 0)
            throw new HTTPException(400,'Save name missing.'); 
            
        if ($searchId != '') {
            // do a replace
            $db->query_replace('Searches',array(
                'searchId'      => $searchId,
                'ownerId'       => $ownerId,
                'saveName'      => $saveName,
                'queryString'   => $q
            ));
        } else {
            $searchId = $db->query_insert('Searches',array(
                'ownerId'       => $ownerId,
                'saveName'      => $saveName,
                'queryString'   => $q
            ));
        }
        echo json_encode(makeSearchObject($searchId,$saveName,$q));
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['t'] == 'del') {
        $DQL = "DELETE FROM Searches WHERE searchId=$searchId and ownerId=$ownerId";
        $db->query($DQL);
        echo "ok";
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['t'] == 'get') {
        $data = $db->fetch_all_array('SELECT * FROM Searches WHERE ownerId='.$ownerId);
        $rtr = array(); 
        foreach ($data as $r) {
            $rtr[] = makeSearchObject($r['searchId'],$r['saveName'],$r['queryString']);
        }
        echo json_encode($rtr);
    }
    
} catch (Exception $e) {
    header('HTTP/1.0 502 Something broke. '); 
    echo json_encode($e->getMessage()); 
}


function makeSearchObject($id,$name,$query) {
    $x = new stdClass();
    $x->id = $id; 
    $x->saveName = $name; 
    $x->query = $query; 
    return $x; 
}