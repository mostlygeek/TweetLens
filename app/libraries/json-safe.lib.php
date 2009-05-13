<?php

// for cleaning up twitter object data before outputting to the user
// this is to prevent cross site scripting problems
// like the Mikeyy worm on Twitter. 

function safeJSON($object) {
    if (is_object($object)) {
        $new = clone $object; 
    } else {
        $new = $object; // could be an array 
    }
    foreach ($new as $member => $value) {
        // some custom mungling for the source of a twitter item
        // don't want to strip out the html, just want to clean it up :)
        if ($member == 'source' && is_scalar($value)) {
            $new->$member = str_replace('<a','<a target="_blank" class="sourceLink"',$value);
            continue;
        }
        // Seems like twitter fixed their JSON bugs. 
        // Blanking this out until we need something similar...         
        continue; // just skip the rest. 
        if (is_scalar($value)) { 
            $new->$member = htmlentities($value,ENT_NOQUOTES);
        } elseif (is_object($value)) {
            if (is_object($new)) 
                $new->$member = safeJSON($value); // mm recursion. 
            if (is_array($new)) 
                $new[$member] = safeJSON($value);
        } elseif (is_array($value)) {
            $new->$member = safeJSON($value);
        }
    }
    return $new;
}