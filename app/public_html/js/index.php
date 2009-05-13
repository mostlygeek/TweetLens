<?php
/* returns a concatenated version of all the js files */ 
require_once('../../../libraries/config.php');
header('Content-Type: text/javascript');


$skipCache = ($_GET['sk']) ? true : false; 
$cacheKey = 'js-cache-'.VERSION_KEY; 

$skipMin = ($_GET['sm']) ? true : false; 
if (!$skipMin) {
    include('jsmin-1.1.1.php'); 
    $cacheKey .= '-skip-min';
}

$mc = getMemcacheObject(); 

if (!$skipCache && $output = $mc->get($cacheKey)) {
    echo "/** FROM CACHE **/\n\n"; 
    echo $output; 
    die();
}



switch ($_GET['a']) {
    default: // get everything
        $files = glob('*.js'); 
}


if (is_array($files)) {
    $output = ''; 
    foreach ($files as $file) { 
        $output .= "/*** FILE: $file ***/\n"; // add a comment
        $source = file_get_contents($file); 
        if (!$skipMin) {
            // minimize it 
            $source = JSMIN::minify($source);
        }
        $output .= $source; 
        $output .= "\n\n";
    }
    $mc->set($cacheKey,$output,null,86400);
    echo $output; 
}

