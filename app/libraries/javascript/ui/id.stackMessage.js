if (init.add) {

init.add('id.stackMessage.js INIT',function() {
    $(document).bind('STACK_MESSAGE',function(e,type,message,fadeAway) {
        if (typeof fadeAway != 'boolean')
            fadeAway = true; 
        var i = $('<p></p>');
        i.addClass(type)
            .text(message)
            .appendTo('#stackMessage')
            .show();
        if (fadeAway) 
            i.animate({opacity:1.0},2500)
             .fadeOut( function(){$(this).remove()});
    });
    
    $(document).bind('TIMELINE_FETCH_LATEST_RESULTS',function(e,delta,newCount) {
        if (newCount == 0) {
            $(this).trigger('STACK_MESSAGE',
                ['notice','Sorry, no new Tweets to show.']); 
        } else {
            var item = (newCount > 1) ? 'Tweets' : 'Tweet';
            $(this).trigger('STACK_MESSAGE',['success',
                newCount+' new '+item+' downloaded.']);
        }
    }); 
    $(document).bind('REPLIES_FETCH_LATEST_RESULTS',function(e,delta,newCount) {
        if (newCount == 0) {
            $(this).trigger('STACK_MESSAGE',
                ['notice','Sorry, no new Mentions to show.']); 
        } else {
            var item = (newCount > 1) ? 'Mentions' : 'Mention';
            $(this).trigger('STACK_MESSAGE',['success',
                newCount+' new '+item+' downloaded.']);
        }
    }); 
    $(document).bind('MESSAGES_FETCH_LATEST_RESULTS',function(e,delta,newCount) {
        if (newCount == 0) {
            $(this).trigger('STACK_MESSAGE',
                ['notice','Sorry, no new Messages to show.']); 
        } else {
            var item = (newCount > 1) ? 'Messages' : 'Message';
            $(this).trigger('STACK_MESSAGE',['success',
                newCount+' new '+item+' downloaded.']);
        }
    });
    $(document).bind('STACKDISPLAY_PAGE',function(e,page,numItems) {
        $('#stackMessage').empty();
        if (numItems == 0) {
            $(this).trigger('STACK_MESSAGE',
                ['notice','No items to display',false]);            
        }
    });
});

}