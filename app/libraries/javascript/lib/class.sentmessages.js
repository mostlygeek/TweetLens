var SENT_MESSAGES_AJAX_MANAGER = $.manageAjax({ 
    manageType:'queue'
    });

function SentMessages(data) {
    if (arguments.length > 0)
        SentMessages.superclass.init.call(this,data);
}
SentMessages.superclass = Stack.prototype; 
SentMessages.prototype = new Stack; 
SentMessages.prototype.constructor = SentMessages; 
SentMessages.prototype.fetchPage = function(page,callback) {
    // fetches a page from AJAX
    var stack = this; 
    SENT_MESSAGES_AJAX_MANAGER.add( 
    {
        type:       'GET',
        url:        "/webservice/sentmessages.php?page="+page,
        dataType:   'json',
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            debug('(ajax) Failed to Load Page: '+page+': '+textStatus);
        },
        success: function (data,textStatus) {
            debug('(ajax) Page '+page+' loaded successfully from ajax:');
            stack.updateStack(data);
            $(document).trigger('SENT_MESSAGES_PAGE_ADDED',[page,data]);
            if (typeof(callback) == 'function')
                callback(page,data);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/sentmessages.php?page="+page);
            $(document).trigger('UPDATE_API_LIMIT');
        }
    });
}
SentMessages.prototype.fetchLatest = function(callback) {
    var stack = this; 
    var data = stack.getStack(); 
    var sinceid = 1; 
    if (data.length == 0) {
        // try to fetch page 1 instead 
        this.fetchPage(1,function(page,data) {
            if (data.length > 0) {
                // got a page, maybe from cache, try to get recent
                stack.fetchLatest(); 
            }
        });
        return; 
    }

    sinceid = data[0].id;    
    
    SENT_MESSAGES_AJAX_MANAGER.add( 
    {
        type:       'GET',
        url:        "/webservice/sentmessages.php?sinceid="+sinceid,
        dataType:   'json',
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            debug('(ajax) Fetch Latest Sent Messages failed: '+textStatus);
        },
        success: function (delta,textStatus) {
            if (typeof delta != 'object') {
                debug('ERROR: Sent Messages fetch latest invalid value'); 
                return; 
            }   
            var c = 0; 
            if (delta.length > 0)
                stack.updateStack(delta);
                
            $(document).trigger('SENT_MESSAGES_FETCH_LATEST_RESULTS',[delta,c]);
            
            if (typeof(callback) == 'function')
                callback(delta);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/sentmessages.php?sinceid="+sinceid);
            $(document).trigger('UPDATE_API_LIMIT');
        }
    }); 
    

}