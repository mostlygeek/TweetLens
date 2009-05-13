<?php 
require_once('../../libraries/config.php'); 
require_once(LIB_DIR.'json-safe.lib.php');
if (!Session::isLoggedIn()) {
    header('HTTP/1.0 401 Not Authorized'); 
    die();
}

// load the user's profile.. 
try {
    $me = Session::GetFirstUser();
    $tw = new TwitterCache(); 
    $tw->setTwitterUser($me); 
    $uTimeline = $tw->getUserTimeline($_GET['i'],null,null,5); // 5 items
    $uTimeline = safeJSON($uTimeline);
    $user = $uTimeline[0]->user;
} catch (Exception $e) {
    die('Unable to fetch user info');
}
?>
<div class="tooltipUser">
    <img src="<?=$user->profile_image_url?>"><br>
    <?php if ($user->id != $me->id): ?>
    <?php if ($user->following == 1) : ?>
        <a href="#unfollow" onClick="$(document).trigger('UNFOLLOW',[<?=$user->id?>])">Unfollow</a>
    <?php else: ?>
        <a href="#follow" onClick="$(document).trigger('FOLLOW',[<?=$user->id?>])">Follow</a>
    <?php endif; ?>
    <?php endif; ?>
    
    <p>Following: <?=$user->friends_count?>, Followers: <?=$user->followers_count?></p>

    <p>Name: <?=$user->name?></p>
    <?php if($user->location): ?>
    <p>Location: <?=$user->location?></p>
    <?php endif ?>

    <?php if($user->url): ?>    
    <p>Web: <a href="<?=$user->url?>" target="_blank" class=""><?=$user->url?></a></p>
    <?php endif;?>
    
    <?php if ($user->description): ?>
    <p>Bio: <?=$user->description?></p>
    <?php endif; ?> 
    <hr>
    
    <h3>Latest Tweets</h3>
    <ol class="tweetList">
<?php foreach ($uTimeline as $tweet): ?>
        <li><?=$tweet->text?> - <?=date('h:i, d/m/y',$tweet->created_at_unixtime)?></li>
<?php endforeach; ?>
    </ol>
</div>