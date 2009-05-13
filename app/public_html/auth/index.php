<?php
require_once('../../libraries/config.php'); 
require_once(LIB_DIR.'js-control.lib.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>TweetLens: Log In</title>
<link rel="stylesheet" href="/css/bluetrip/css/screen.css" type="text/css" media="screen, projection">
<link rel="stylesheet" href="/css/bluetrip/css/print.css" type="text/css" media="print">
<link rel="stylesheet" href="style.css" type="text/css" media="screen, projection">
<!--[if IE]><link rel="stylesheet" href="/css/bluetrip/css/ie.css" type="text/css" media="screen, projection"><![endif]-->

<?=linkJS(array('3rdparty-jquery','3rdparty-jquery-form'));?>
<script type="text/javascript">
<!-- 
function beforeSubmit(formData,jqForm,options) {
    $('#errorMsg').hide();
    $('#successMsg').hide();
    $('#loginMsg').fadeIn();
}
function success(responseText, statusText) {
    if (responseText == "ok") {
        $('#errorMsg').hide(); 
        $('#loginMsg').hide();
        $('#successMsg').fadeIn('slow');
        window.location = "/home/";
    } else {
        $('#loginMsg').hide();
        $('#successMsg').hide();
        $('#errorMsg').fadeIn('normal');
    }
}
$( function() {
    // initialize the form
    var options = {
        beforeSubmit    : beforeSubmit,
        success         : success
    }; 
    $('#loginForm').ajaxForm(options);
});     
//-->
</script>
</head>
<body>
<div class="container">
    <div class="span-24 last" id="header">
        <h1>TweetLens : Log In</h1>
    </div>	

    <div class="span-12">

        <form id="loginForm" action="/auth/check.php" method="POST">
          	<fieldset>
                <legend>Log in</legend>
                <p><label for="username">Twitter Username</label><br>
                <input type="text" class="title" name="username" id="username" value="" maxlength="32"></p>
                
                <p><label for="password">Twitter Password</label><br>
                <input type="password" class="title" id="password" name="password" value="" maxlength="64">
                </p>
                <p><button class="button positive">Log In</button>
                <button class="button negative">Reset</button></p>
                <div class="notice hide" id="loginMsg">Logging in...</div>
                <div class="error hide" id="errorMsg">Log in failed. Please check your username / password.</div>
                <div class="success hide" id="successMsg">Log in successful. Redirecting to <a href="/home/">timeline</a>... </div>
      	</fieldset>
        </form>
        <div class="notice">
           Note: TweetLens does not permanently save your Twitter Username / Password. 
           After your session expires so will our working copy of your Twitter credentials. 
            <br><br>
           We take your privacy very seriously and have made the best efforts to keep your 
           username and password safe. 
           
           <br><br>
           Please see our <a href="#">Privacy Policy</a> for more 
           information. 
        </div>

    </div>
    <div class="span-12 last">
    </div>
    
</div> 
</body>
</html>