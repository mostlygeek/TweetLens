if (init.add) {

init.add('id.latestTweet.js INIT',function() {

    $(document).bind('STATUS_UPDATED',function(e,tweet) {
        var l = $('#latestTweet')
            .hide()
            .empty()
            .html(formatLastTweet(tweet)); 
        var c = $(document).data('CURRENT_STACK');
        if ( c == 'timline' || c=='replies')
            l.fadeIn('slow');
        GLOBALS.latestTweet = tweet; // update it. 
    }); 
    
    $('#latestTweet').bind('AJAX_SENDING_STATUS',function(e) {
        $(this).data('LAST_TWEET',$(this).html()); // might need this later...
        $(this).html('<img src="/css/images/loading-red.gif" alt="loading"> ... Sending to Twitter');
    });
    
    var showLatest = function() {
        var x = $('#latestTweet'); 
        if (x.data('v')) return; 
        x.show().data('v',true);
    } 
    var hideLatest = function() {
        var x = $('#latestTweet'); 
        if (!x.data('v')) return; 
        x.hide().data('v',false);
    }
    $('#formStatusUpdate').bind('INTENT_UPDATE',showLatest)
        .bind('INTENT_REPLY',showLatest)
        .bind('INTENT_RETWEET',showLatest)
        .bind('INTENT_DM',hideLatest)
        .bind('INTENT_SEARCH',hideLatest);

}); 

init.done('id.latestTweet.js DONE',function() {
    // show the latest tweet
    if (GLOBALS.latestTweet.text != undefined) {
        $('#latestTweet').html(formatLastTweet(GLOBALS.latestTweet));
    }
});
}

function formatLastTweet(tweet) {
    var text = tweet.text; 
    if (tweet.truncated == '')
        text = linkUsername(linkUrl(text)); 
    return 'Last Tweet: <span class="tweetText">'+text+'</span>'; 
}
