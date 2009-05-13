var REPLIES_AJAX_MANAGER = $.manageAjax({ 
    manageType:'queue',
    blockSameRequest: false
    });

function Replies(data) {
    if (arguments.length > 0)
        Replies.superclass.init.call(this,data);
}

Replies.superclass = Stack.prototype;
Replies.prototype = new Stack;
Replies.prototype.constructor = Replies;
Replies.prototype.fetchPage = function(page,callback) {
    // fetches a page from AJAX
    var stack = this; 
    REPLIES_AJAX_MANAGER.add( 
    {
        type:       'GET',
        url:        "/webservice/replies.php?page="+page,
        dataType:   'json',
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            debug('(ajax) Failed to Load Page: '+page+': '+textStatus);
        },
        success: function (data,textStatus) {
            debug('(ajax) Page '+page+' loaded successfully from ajax:');
            stack.updateStack(data);
            $(document).trigger('REPLIES_PAGE_ADDED',[page,data]);
            if (typeof(callback) == 'function')
                callback(page,data);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/replies.php?page="+page);
        }
    });
}
Replies.prototype.fetchLatest = function(callback) {
    var stack = this; 
    var sinceid = 1;

    var data = stack.getStack(); 
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
    REPLIES_AJAX_MANAGER.add( 
    {
        type:       'GET',
        url:        "/webservice/replies.php?sinceid="+sinceid,
        dataType:   'json',
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            debug('(ajax) Fetch Latest Replies failed: '+textStatus);
        },
        success: function (delta,textStatus) {
            if (typeof delta != 'object') {
                debug('ERROR: Replies fetch latest invalid value'); 
                return; 
            }   
            var c = 0; 
            if (delta.length > 0)
                c = stack.updateStack(delta);
                
            $(document).trigger('REPLIES_FETCH_LATEST_RESULTS',[delta,c]);
            
            if (typeof(callback) == 'function')
                callback(delta);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/replies.php?sinceid="+sinceid);
        }
    }); 
}