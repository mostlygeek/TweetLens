<?php
/* this provides an easy way to generate the HTML that links 
   to all the separate js files required to build the application .
   
   It also centralized the logic for the JS files used in Production / Dev. 
 */

function linkJS($opts) {
    if (!is_array($opts)) {
        $opts = array($opts); 
    }
    $output = ''; 
    foreach ($opts as $opt) {
        $output .= _linkJS($opt); 
    }
    return $output; 
}

function listJSFiles($what) {
    $LOCAL_PREFIX = JS_DIR; // where are the JS files are on the server
    $thirdPath = LIB_DIR.'../public_html/js/'; 
    $js['libs'] = _listJSFiles($LOCAL_PREFIX,'lib/');
    $js['main'] = _listJSFiles($LOCAL_PREFIX,'/');
    $js['ui'] = _listJSFiles($LOCAL_PREFIX,'ui/');
    
    switch ($what) {
        case 'all': 
            return array_merge(
                $js['libs'],
                $js['main'],
                $js['ui']);
        case 'libs': 
            return $js['libs']; 
        case 'main': 
            return $js['main']; 
        case 'ui': 
            return $js['ui'];
    }
    
}

function _listJSFiles($LOCAL_PREFIX,$path) {
    $files = glob($LOCAL_PREFIX.$path.'*.js'); 
    $returnArray = array();
    foreach ($files as $file) {
        $name = basename($file);
        $returnArray[] = $LOCAL_PREFIX.$path.$name; 
    }
    return $returnArray; 
}

function _linkJS($opt) {
    $output = '';
    $LOCAL_PREFIX = JS_DIR; // where are the JS files are on the server
    $WEB_PREFIX = '/js/'; // how to reference on the web

    switch ($opt) {            
        case 'dev-all': 
            $output .= _linkJS('3rdparty-jquery'); 
            $output .= _linkJS('3rdparty-jquery-contextmenus'); 
            $output .= _linkJS('3rdparty-shortcuts'); 
            $output .= _linkJS('3rdparty-jquery-ajaxmanager');
            $output .= _linkJS('3rdparty-jquery-qtip');
            $output .= _linkJS('dev-libs'); 
            $output .= _linkJS('dev-main'); // needs to be loaded first. 
            $output .= _linkJS('dev-ui'); // ui elements
            break; 

        // DEVELOPMENT specific 3rd party files
        case '3rdparty-jquery': 
            $output .= _scriptTag('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');             
            break;
        case '3rdparty-jquery-form': 
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/jquery.form.2.25.min.js');
            break;
        case '3rdparty-jquery-contextmenus': 
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/context/jquery.contextMenu.1.0.min.js');
            break; 
            
        case '3rdparty-shortcuts': 
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/shortcut.2.01b-custom.min.js');
            break;
        case '3rdparty-jquery-ajaxmanager': 
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/jquery.ajaxmanager.1.21.min.js');
            break;
        case '3rdparty-jquery-qtip': 
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/jquery.qtip-1.0.0-rc2.min.js'); break;
        case '3rdparty-jquery-cluetip': 
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/cluetip/lib/jquery.bgiframe.min.js');
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/cluetip/lib/jquery.hoverIntent.min.js');
            $output .= _scriptTag($WEB_PREFIX.'3rdparty/cluetip/jquery.cluetip.min.js');
            break;

        // DEVELOPMENT specific group files 
        case 'dev-main':             
            $path = '';
            $output .= _makeScriptTags($WEB_PREFIX.'TL/',$path,glob($LOCAL_PREFIX.$path.'*.js'));
            break;
        case 'dev-ui': 
            $path = 'ui/'; 
            $output .= _makeScriptTags($WEB_PREFIX.'TL/',$path,glob($LOCAL_PREFIX.$path.'*.js'));
            break;
        case 'dev-libs': 
            $path = 'lib/';

            // these have to loaded in this order
            $output .= _scriptTag($WEB_PREFIX.'TL/'.$path.'class.stack.js');
            $output .= _scriptTag($WEB_PREFIX.'TL/'.$path.'class.timeline.js');
            $output .= _scriptTag($WEB_PREFIX.'TL/'.$path.'class.replies.js');
            $output .= _scriptTag($WEB_PREFIX.'TL/'.$path.'class.messages.js');
            $output .= _scriptTag($WEB_PREFIX.'TL/'.$path.'class.sentmessages.js');
            $output .= _scriptTag($WEB_PREFIX.'TL/'.$path.'class.favorites.js');
            $output .= _scriptTag($WEB_PREFIX.'TL/'.$path.'class.twittersearch.js');
            
            // load the function libraries
            $output .= _makeScriptTags($WEB_PREFIX.'TL/',$path,glob($LOCAL_PREFIX.$path.'lib.*.js'));

            break; 
            
        /* PRODUCTION specific group files 
            - these requires the 404 handler, caching redirect
            - that generates the files named in the production libraries
         */ 
        case 'prod-all':
            $output .= _linkJS('3rdparty-jquery'); 
            // $output .= _linkJS('3rdparty-jquery-form'); 
            $output .= _linkJS('3rdparty-jquery-contextmenus'); 
            $output .= _linkJS('3rdparty-shortcuts'); 
            $output .= _linkJS('3rdparty-jquery-ajaxmanager');
            $output .= _linkJS('3rdparty-jquery-qtip');
            $output .= _scriptTag('/jscache/all.min.js?'.VERSION_KEY); 
            break;
    }
    return $output; 
}

function _makeScriptTags($WEB_PREFIX,$path,$files) {
    $output = ''; 
    foreach ($files as $file) {
        $name = basename($file);
        $output .= _scriptTag($WEB_PREFIX.$path.$name); 
    }
    return $output; 
}
function _scriptTag($file) {
    // output a script tag. 
    return '<script type="text/javascript" src="'.$file.'"></script>'."\n"; 
}
