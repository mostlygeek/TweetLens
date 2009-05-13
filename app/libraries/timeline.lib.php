<?php 

// Tools for centralizing timeline cache management 
function makeLookup($tweets) {

    $lookup = array();
    foreach ($tweets as $key => $tweet) {
        $lookup[$tweet->id] = $key; 
    }
    return $lookup;
}

function getTimeline($mc,$uid,$username,$password) {

    $tweets = $mc->get('friendstimeline-'.$uid);

    if (!$tweets) { // nothing in cache, fetch from twitter
        try {
            $tw = new TwitterCache($username,$password,$mc); 
            $tweets = $tw->getFriendsTimeline(null,null,200);            
            $mc->set('friendstimeline-'.$uid,$tweets,null,600);
        } catch (Exception $e) {
            throw $e; 
        }        
    }
    
    $lookup = makeLookup($tweets);
    
    // merge in the meta read/show data. 
    $shows = getTweetShow($mc,$uid,array_keys($lookup)); 
    $reads = getTweetRead($mc,$uid,array_keys($lookup));
    
    // reset it all. 
    for ($i=0; $i < count($tweets); $i++) {
        $tweets[$i]->read = 0;
        $tweets[$i]->show = 1; // default to show it.        
    }
    
    foreach ($shows as $twId) {
        $tweets[$lookup[$twId]]->show = 0; // hide 
    }
    foreach ($reads as $twId) {
        $tweets[$lookup[$twId]]->read = 1; 
    }
    return $tweets; 
}

function setTimeline($mc,$uid,$tweets) {
    $mc->set('friendstimeline-'.$uid,$tweets,null,600);    
}

// functions for managing [show] status on tweets
function setTweetShow($mc,$uid,$twId,$val) {
    if ($val) {
        $mc->delete("hide-$uid-$twId"); // don't over ride the default
    } else {
        // hide it. 
        $mc->set("hide-$uid-$twId",$twId,null,172800);
    }
}
function unsetTweetShow($mc,$uid,$twId) {
    $mc->delete("hide-$uid-$twId");
}
function getTweetShow($mc,$uid,$twId) {
    if (is_array($twId)) {
        foreach ($twId as $id) {
            $keys[] = "hide-$uid-$id"; // one key
        }
    } else {
        $keys[] = "hide-$uid-$twId"; // one key
    }
    $show = $mc->get($keys); 
    if (!is_array($show)) 
        return array();

    return $show;
}

// functions for managing [read] status on tweets
function setTweetRead($mc,$uid,$twId) {
    $mc->set("read-$uid-$twId",$twId,null,172800);
    
}
function unsetTweetRead($mc,$uid,$twId) {
    $mc->delete("read-$uid-$twId");
}
function getTweetRead($mc,$uid,$twId) {
    if (is_array($twId)) {
        foreach ($twId as $id) {
            $keys[] = "read-$uid-$id"; // one key
        }
    } else {
        $keys[] = "read-$uid-$twId"; // one key
    }    
    $read = $mc->get($keys); 
    if (!is_array($read))
        return array();
    
    return $read; 
}
