if (init) {

// create a global Ajax manager for API limits
var API_LIMIT_MGR = $.manageAjax({ 
    manageType:'queue',
    maxReq:1,
    blockSameRequest: true
    });

var timeoutId = 0; 

init.add('id.apilimit.js INIT',function(){
    
    $(document)
        .bind('TIMELINE_PAGE_ADDED',setAPILimitCheck)
        .bind('TIMELINE_FETCH_LATEST_RESULTS',setAPILimitCheck)
        .bind('REPLIES_PAGE_ADDED',setAPILimitCheck)
        .bind('REPLIES_FETCH_LATEST_RESULTS',setAPILimitCheck)
        .bind('MESSAGES_PAGE_ADDED',setAPILimitCheck)
        .bind('MESSAGES_FETCH_LATEST_RESULTS',setAPILimitCheck)
        .bind('SENT_MESSAGES_PAGE_ADDED',setAPILimitCheck)
        .bind('SENT_MESSAGES_FETCH_LATEST_RESULTS',setAPILimitCheck)
        .bind('FAVORITES_PAGE_ADDED',setAPILimitCheck);
    
    // immediately update the API limit
    $(document).bind('UPDATE_API_LIMIT',updateApiLimit); 
    
    function setAPILimitCheck(e) { 
        // delay it so the API limit is not pulled after every single request
        clearTimeout(timeoutId);
        timeoutId = setTimeout('$(document).trigger("UPDATE_API_LIMIT");',2000);
    }
    
    function updateApiLimit() {
        checkRateLimit = true; 
        API_LIMIT_MGR.abort(); // abort all previous requests
        API_LIMIT_MGR.add({
            type:       'GET',
            url:        "/webservice/ratelimit.php",
            dataType:   'text',
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                debug('(ajax) Failed to get rate limit: '+textStatus);
            },
            success: function (data,textStatus) {
                trackAnalytics("/webservice/ratelimit.php");
                $('#apilimit').text('API Limit: '+data+'/100');
            }
        }); 
    }
}); 

init.done('id.apilimit.js DONE',function() {
    $(document).trigger('UPDATE_API_LIMIT');
}); 
}