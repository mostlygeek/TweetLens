<?php 
require_once('../libraries/config.php'); 
// handle 
switch ($_SERVER['REDIRECT_URL']) {
    case '/jscache/all.min.js':     
        include_once(LIB_DIR.'js-control.lib.php');
        include_once(LIB_DIR.'jsmin-1.1.1.php');

        header('HTTP/1.0 200'); 
        header('Content-Type: text/javascript');
        $LOCAL_JS_PREFIX = JS_DIR;
        $files = listJSFiles('all'); 
        $output = ''; 
        foreach ($files as $filename) {
            $basename = basename($filename);
            $output .= "/*** FILE: $basename ***/\n\n"; 
            $code = file_get_contents($filename);
            $output .= JSMin::minify($code);
            $output .= "\n\n"; 
        }
        
        //*write the file to the cache directory
        $fp = fopen('jscache/all.min.js','w'); 
        if ($fp) {
            fwrite($fp,$output);
            fclose($fp);
        } //*/
        echo $output ;
        break; 
    default: 
        // need a better message here. 
        die('Sorry. File Not Found'); 
        
}