<?php
require_once('../../libraries/config.php'); 
if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

try {
    $bucket = Bucket::newBucket('global','BktOurls',86400);
    
    if (!$_POST['url'])
        throw new HTTPException(400,'url field is missing');        

    $url = $_POST['url'];

    if (preg_match('/http:\/\/w*\.{0,1}twitpic.com\/(.*)/',$url,$matches)) {
        $imageid = $matches[1];
        echo '<img src="http://twitpic.com/show/thumb/'.$imageid.'" height="150" width="150" alt="Loading from twitpic" class="tooltip">';        
    } elseif (preg_match('/^http:\/\/[^\/]{3,20}\/[a-z0-9-]+$/i',$url,$matches)) {
        
        // clean up the URL if required 
        $parts = parse_url($url);
        $url = $parts['scheme'].'://'.$parts['host'].$parts['path'];
        $output = $bucket->get($url);
        if (strlen($output) > 3) {
            echo '<span class="tooltipLink">'.$output.'</span>'; 
        } else { // attempt to get and fetch            
            $html = ''; 

            $ch = curl_init(); 
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_NOBODY,false); // a head request
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_HEADER, true);
            curl_setopt($ch,CURLOPT_TIMEOUT,3);
            
            for ($i = 0; $i < 3; $i++) {
                $response = curl_exec($ch);
                preg_match('/Location: (.*)/i',$response,$matches);
                if ($matches[1]) {
                    $output = (strlen($matches[1]) > 75) ? 
                        substr($matches[1],0,75).'...' : $matches[1];
                    $html = '<span class="tooltipLink">'.$output.'</span>'; 
                    $bucket->add($url,$output);
                    echo $html;
                    break; 
                } 
                sleep(2);
            }

            if ($html == '') {
                echo '<div class="error">no response from '.$url.'...</div>';
                $bucket->del($url);
            }
        }
    } else {
        echo $url; // token for don't display this
    }
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
