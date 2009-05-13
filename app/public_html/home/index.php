<?php
require_once('../../libraries/config.php');
require_once(LIB_DIR.'js-control.lib.php');
require_once(LIB_DIR.'json-safe.lib.php');

if (!Session::isLoggedIn()) {
    header('Location: /auth/');
}

// create a globals JSON array to use 
$GLOBALS = array(
    'timeline'      => array(), 
    'replies'       => array(), 
    'directmsgs'    => array(),
    'sdirectmsgs'   => array(),
    'followers'     => array(), 
    'friends'       => array()
); 

try {
    $user = Session::getFirstUser(); 
    $status = new StatusStack($user);
    // just attempt to pull something out of the cache 
    $timeline = safeJSON($status->getTimelinePage(1,20,true));
} catch (Exception $e) {
    // do something fancier here... 
    print_r($e); 
    die(); 
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />

    <title>TweetLens : <?=$user->screen_name?></title>
    <link rel="stylesheet" href="/css/bluetrip/css/screen.css" type="text/css" media="screen, projection">
    <link rel="stylesheet" href="/css/bluetrip/css/print.css" type="text/css" media="print">
    <!--[if IE]>
        <link rel="stylesheet" href="/css/bluetrip/css/ie.css" type="text/css" media="screen, projection">
    <![endif]-->
    <link rel="stylesheet" href="/css/global.css" type="text/css" media="screen, projection">  
    <link rel="stylesheet" href="style.css" type="text/css" media="screen, projection">  
</head>
<body>
<div class="container ">

<?php if(!IN_PRODUCTION): ?>
    <div class="span-24 last"><p class="error">In development mode</p></div>
<?php endif;?>

    <div id="header" class="span-24 last">
    <h1>TweetLens: <?=$user->screen_name?></h1>
    </div>

<div id="main-content" class="span-17 suffix-1">
        <form id="formStatusUpdate" method="GET">            
            <input type="hidden" name="rid" id="replyToId" value="" readonly>
            <div class="span-9">
                <span class="statusHeader" id="statusUpdateIntent">What are you doing?</span>
            </div>
            <div class="span-8 last right">
                <span class="statusHeader" id="charCount"></span>
            </div>
            <div class="span-17 last" id="dmRecipient">
                Recipient: <input type="text" name="recipient_screenname" id="recipient">
            </div>
            <div class="span-17 last">
                <textarea id="statusUpdate" name="text" tabindex="1"></textarea>
            </div>
            <div class="span-17 last" id="formMessages">
            </div>
            <div class="span-13">
                <div id="saveSearch">
                    <div class="span-10"><p>Name: <input class="span-8" type="text" name="saveName" id="saveName" maxlength="64" size="32" value=""></p></div>
                    <div class="span-3 last"><button id="updateSearch" class="button positive">Save Search</button> </div>
                    <input type="hidden" name="searchId" id="searchId">
                </div>
                <p id="latestTweet"></p>
                &nbsp;
            </div>
            <div class="span-2">
                <button tabindex="3" class="button negative" id="resetForm">Reset</button>
            </div>
            <div class="span-2 last">            
                <button tabindex="2" class="button positive" id="submitButton">Update</button>
            </div>
        </form>

    <div class="span-17 last">    
        <h2 id="stackTitle">Loading items... <img src="/css/images/loading-red.gif" alt="loading"></h2>
    </div>
    <div id="stackMessage" class="span-17 last"></div>

    <div class="stack-menu">
    <div class="span-9">
        <ul class="stackOptions">
            <li><a href="#mark-all-as-read" class="menuLink">Mark all as read</a></li>
            <li><a href="#show-all" class="menuLink">Show All</a></li>
            <li><a href="#hide-all" class="menuLink">Collapse All</a></li>
        </ul>
    </div>
    <div class="span-8 last">
        <ul class="pageNav">
            <li rel="firstPage"><a href="#fpage">Latest</a></li>
            <li rel="prevPage"><a href="#newerTweets">&#171; Newer</a></li>
            <li rel="nextPage"><a href="#olderTweets">Older &#187;</a></li>
        </ul>
    </div>
    </div>

    <div id="stackDisplay" class="span-17 last"></div>

    <div class="stack-menu">
    <div class="span-9">
        <ul class="stackOptions">
            <li><a href="#mark-all-as-read" class="menuLink">Mark all as read</a></li>
            <li><a href="#show-all" class="menuLink">Show All</a></li>
            <li><a href="#hide-all" class="menuLink">Collapse All</a></li>
        </ul>
    </div>
    <div class="span-8 last">
        <ul class="pageNav">
            <li style="display:none" rel="firstPage"><a href="#fpage">Latest</a></li>
            <li style="display:none" rel="prevPage"><a href="#newerTweets">&#171; Newer</a></li>
            <li rel="nextPage"><a href="#olderTweets">Older &#187;</a></li>
        </ul>
    </div>
    </div>

</div>

<div id="right-column" class="span-6 last">

    <div id="mainMenu">
        <ul id="stackMenu">
	       <li><a href="#timeline">Timeline</a> (<a href="#refresh-timeline">refresh</a>)</li>
	       <li><a href="#replies">Mentions</a> (<a href="#refresh-replies">refresh</a>)</li>
	       <li><a href="#favorites">Favorites</a></li>
	       <li>Messages: 
	           <ul>
	               <li><a href="#messages">Received</a> (<a href="#refresh-messages">refresh</a>)</li>
	               <li><a href="#sentmessages">Sent</a></li>
	           </ul></li>
	       <li><a href="#search">New Search</a>
	           <ul id="savedSearches">
	           </ul>
	       </li>
           <li><a href="/auth/logout.php">Logout</a></li>	       
        </ul>

        <h2>Hot Keys</h2>
        <ul id="hotkey-list">
            <li><span class="hotkey">j</span> -  move down</li>
            <li><span class="hotkey">k</span> -  move up</li>
            <li><span class="hotkey">r</span> - reply</li>
            <li><span class="hotkey">t</span> - retweet</li>
            <li><span class="hotkey">o</span> - toggle show/hide</li>
            <li><span class="hotkey">u</span> - update status</li>
            <li><span class="hotkey">f</span> - favorite</li>
            <li><span class="hotkey">z</span> - rate like</li>
            <li><span class="hotkey">c</span> - clear rating</li>
            <li><span class="hotkey">s</span> - new search</li>
            <li><span class="hotkey">[space]</span> -  toggle open/close</li>
            <li><span class="hotkey">[right], n</span> -  next page</li>
            <li><span class="hotkey">[left], p</span> -  previous page</li>
        </ul>
        <h2>Support</h2> 
        <ul>
            <li><a href="http://code.google.com/p/tweetlens/issues/list" target="_blank">Bug/Feature Tracker</a></li>
        </ul>        
    </div>
</div>

<div class="span-24 last footer">
    <div class="span-12">
        <p>TweetLens &copy; Benson Wong 2009, Alpha2 Release | follow <a href="http://twitter.com/tweetlens" target="_blank">@tweetlens</a> and <a href="http://twitter.com/mostlygeek" target="_blank">@mostlygeek</a> for updates. </p>
    </div>
    <div class="span-12 right last">
        <span id="apilimit">API Limit (unitialized)</span>    
    </div>
</div>

</div> <!-- class=container -->


<!-- data output divs  -->

<div id="ajaxWatch"></div>
<div id="debugView">
<h3>Debug Output</h3>
   <ol id="debug"></ol>
</div>

<script type="text/javascript">
var PRODUCTION_MODE = <?=(IN_PRODUCTION) ? 'true' : 'false'?>;
</script>
<?php 
// load the libraries
if (IN_PRODUCTION) {
    echo linkJS('prod-all');
} else {
    echo linkJS('dev-all'); 
}
?>

<script type="text/javascript">
    // bind all the data. 
    var GLOBALS = {
        currentAccount: <?=json_encode($user)?>,
        latestTweet   : <?=json_encode($user->status)?>, // only for first load display
        timeline      : new Timeline(<?=json_encode($timeline)?>),
        replies       : new Replies,
        messages      : new Messages,
        favorites     : new Favorites,
        replyTree     : new Stack, // single items for reply tree stack
        sentMessages  : new SentMessages
    };    
</script>
<script type="text/javascript">
    $(init); // initialize everything.
</script>

<?php if (IN_PRODUCTION): ?>

<style type='text/css'>@import url('http://s3.amazonaws.com/getsatisfaction.com/feedback/feedback.css');</style>
<script src='http://s3.amazonaws.com/getsatisfaction.com/feedback/feedback.js' type='text/javascript'></script>
<script type="text/javascript" charset="utf-8">
  var tab_options = {}
  tab_options.placement = "right";  // left, right, bottom, hidden
  tab_options.color = "#222"; // hex (#FF0000) or color (red)
  GSFN.feedback('http://getsatisfaction.com/tweetlens/feedback/topics/new?display=overlay&style=idea', tab_options);
</script>
<script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www."); document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    try {
        var pageTracker = _gat._getTracker("UA-584664-2");
        pageTracker._trackPageview("/home/");
    } catch(err) {
    }        
</script>

<?php endif; ?>

</body>
</html>