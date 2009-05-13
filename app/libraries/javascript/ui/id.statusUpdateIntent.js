if (init) {
    init.add('statusUpdateIntent.init',function() {
        var statusIntent = $('#statusUpdateIntent'); 

        $('#formStatusUpdate').bind('INTENT_REPLY',function(e,item){
            if (item.user) // status
                statusIntent.text('Reply to: ' + item.user.screen_name);
            if (item.sender) // a direct message... 
                statusIntent.text('Direct Message to: ' + item.sender.screen_name);
        }).bind('INTENT_RETWEET',function(e){
            statusIntent.text('Retweeting...'); 
        }).bind('INTENT_UPDATE',function(e){
            statusIntent.text('What are you doing?'); 
        }).bind('INTENT_DM',function(e){
            statusIntent.text('Send a Direct Message'); 
        }).bind('INTENT_SEARCH',function(e){
            statusIntent.text('Search Twitter');
        });

    }); 
}