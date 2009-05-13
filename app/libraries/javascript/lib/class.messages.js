var MESSAGES_AJAX_MANAGER = $.manageAjax({ 
    manageType:'queue'
    });

function Messages(data) {
    if (arguments.length > 0)
        Messages.superclass.init.call(this,data);
}
Messages.superclass = Stack.prototype; 
Messages.prototype = new Stack; 
Messages.prototype.constructor = Messages; 
Messages.prototype.fetchPage = function(page,callback) {
    // fetches a page from AJAX
    var stack = this; 
    MESSAGES_AJAX_MANAGER.add( 
    {
        type:       'GET',
        url:        "/webservice/messages.php?page="+page,
        dataType:   'json',
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            debug('(ajax) Failed to Load Page: '+page+': '+textStatus);
        },
        success: function (data,textStatus) {
            stack.updateStack(data);
            $(document).trigger('MESSAGES_PAGE_ADDED',[page,data]);
            if (typeof(callback) == 'function')
                callback(page,data);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/messages.php?page="+page);
        }
    });
}
Messages.prototype.fetchLatest = function(callback) {
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
    MESSAGES_AJAX_MANAGER.add( 
    {
        type:       'GET',
        url:        "/webservice/messages.php?sinceid="+sinceid,
        dataType:   'json',
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            debug('(ajax) Fetch Latest Messages failed: '+textStatus);
        },
        success: function (delta,textStatus) {
            if (typeof delta != 'object') {
                return; 
            }   
            var c = 0; 
            if (delta.length > 0)
                c = stack.updateStack(delta);
                
            $(document).trigger('MESSAGES_FETCH_LATEST_RESULTS',[delta,c]);
            
            if (typeof(callback) == 'function')
                callback(delta);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/replies.php?sinceid="+sinceid);
        }
    }); 
    

}