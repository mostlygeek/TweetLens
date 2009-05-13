var TIMELINE_AJAX_MGR = $.manageAjax({ 
    manageType:'queue'
    });

function Timeline(data) {
    if (arguments.length > 0)
        Timeline.superclass.init.call(this,data);
}

Timeline.superclass = Stack.prototype;
Timeline.prototype = new Stack;
Timeline.prototype.constructor = Timeline;
Timeline.prototype.fetchPage = function(page,callback) {
    // fetches a page from AJAX
    var stack = this; 
    TIMELINE_AJAX_MGR.add( 
    {
        type:       'GET',
        url:        "/webservice/timeline.php?page="+page,
        dataType:   'json',
        error: Stack.ajaxError,
        success: function (data,textStatus) {
            debug('(ajax) Page '+page+' loaded successfully from ajax:');
            stack.updateStack(data);
            $(document).trigger('TIMELINE_PAGE_ADDED',[page,data]);

            if (typeof(callback) == 'function')
                callback(page,data);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/timeline.php?page="+page);
        }
    });
}
Timeline.prototype.fetchLatest = function(callback) {
    var stack = this; 
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

    // find the last tweet that wasn't mine, to prevent
    // my last tweet from breaking the fetch. 
    for (i in data) {
        if (data[i].user.id != GLOBALS.currentAccount.id) {
            sinceid = data[i].id;
            break; 
        }
    }

    TIMELINE_AJAX_MGR.add( 
    {
        type:       'GET',
        url:        "/webservice/timeline.php?sinceid="+sinceid,
        dataType:   'json',
        error: Stack.ajaxError,
        success: function (delta,textStatus) {
            debug('(ajax) Latest timeline successfully loaded');

            if (typeof delta != 'object') {
                debug('ERROR: Timeline fetch latest invalid value'); 
                return; 
            }
            var c = 0;    
            if (delta.length > 0)
                c = stack.updateStack(delta);
            $(document).trigger('TIMELINE_FETCH_LATEST_RESULTS',[delta,c]);
            
            if (typeof(callback) == 'function')
                callback(delta);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/timeline.php?latest");
        }
    }); 
    
}