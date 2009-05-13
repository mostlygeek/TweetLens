var FAVORITES_AJAX_MGR = $.manageAjax({ 
    manageType:'queue'
    });

function Favorites(data) {
    if (arguments.length > 0)
        Favorites.superclass.init.call(this,data);
}

Favorites.superclass = Stack.prototype;
Favorites.prototype = new Stack;
Favorites.prototype.constructor = Favorites;
Favorites.prototype.fetchPage = function(page,callback) {
    // fetches a page from AJAX
    var stack = this; 
    FAVORITES_AJAX_MGR.add( 
    {
        type:       'GET',
        url:        "/webservice/getFavorites.php?page="+page,
        dataType:   'json',
        error: Stack.ajaxError,
        success: function (data,textStatus) {
            debug('(ajax) Page '+page+' loaded successfully from ajax:');
            stack.updateStack(data);
            $(document).trigger('FAVORITES_PAGE_ADDED',[page,data]);

            if (typeof(callback) == 'function')
                callback(page,data);
        },
        complete: function(XMLHttpRequest, textStatus) {
            trackAnalytics("/webservice/getFavorites.php?page="+page);
        }
    });
}
