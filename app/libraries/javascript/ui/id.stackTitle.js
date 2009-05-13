if (init.add) {

init.add('id.stackTitle.js INIT',function() {
    var updateTitle = function(title,page) {
        var str = title;
        if (page != undefined && page > 0) str += ', Page '+page;
        $('#stackTitle').html(str);
    }

    $(document).bind('STACKDISPLAY_PAGE',function(e,page) {
        switch ($(document).data('CURRENT_STACK')) {
            case 'timeline' : updateTitle('Timeline',page); break; 
            case 'replies'  : updateTitle('Mentions',page); break; 
            case 'messages' : updateTitle('Direct Messages',page); break; 
            case 'sentMessages': updateTitle('Sent Messages',page); break;;
            case 'favorites': updateTitle('Favorites',page); break;
        }
    });
    
    // handle search results. 
    $(document).bind('SEARCH_SHOW',function(e,search) {
        if (search.response.page == undefined)
            $('#stackTitle').empty();
        else 
            updateTitle('Search Results',search.response.page);
    });
});

}