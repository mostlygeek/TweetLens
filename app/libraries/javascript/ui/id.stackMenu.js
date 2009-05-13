if (init.add) {

init.add('id.stackMenu.id-init',function(){
    // bind actions 
    /*
    SHOW_TIMELINE
    SHOW_REPLIES
    SHOW_MESSAGES
    SHOW_SENTMESSAGES
    TIMELINE_MANUAL_REFRESH
    REPLIES_MANUAL_REFRESH
    MESSAGES_MANUAL_REFRESH
    */
    $('a[href=#timeline]').click(function(e) {
        $(document).trigger('SHOW_TIMELINE');
    });
    $('a[href=#refresh-timeline]').click(function(e) {
        $(document).trigger('TIMELINE_MANUAL_REFRESH');
    });
    $('a[href=#replies]').click(function(e) {
        $(document).trigger('SHOW_REPLIES');
    });
    $('a[href=#refresh-replies]').click(function(e) {
        $(document).trigger('REPLIES_MANUAL_REFRESH');
    });
    $('a[href=#favorites]').click(function(e) {
        $(document).trigger('SHOW_FAVORITES');
    });
    $('a[href=#messages]').click(function(e) {
        $(document).trigger('SHOW_MESSAGES');
    });
    $('a[href=#refresh-messages]').click(function(e) {
        $(document).trigger('MESSAGES_MANUAL_REFRESH');
    });
    $('a[href=#sentmessages]').click(function(e) {
        var sentMessages = GLOBALS.sentMessages; 
        var stack = sentMessages.getStack(); 
        if (stack[0] == undefined) {
            sentMessages.fetchPage(1,function(page,data) {
                if (data.length > 0) {
                    // fetch latest first before showing sent messages
                    sentMessages.fetchLatest( function(delta) {
                        $(document).trigger('SHOW_SENT_MESSAGES');
                    });
                } else {
                    $(document).trigger('SHOW_SENT_MESSAGES');
                }
            });
        } else {
            sentMessages.fetchLatest(function(delta) {
                $(document).trigger('SHOW_SENT_MESSAGES');            
            });
        }
    });
    
    /************    
      SAVED SEARCHES
     ************/
    $('a[href="#search"]').click(function(e) {
        $(document).trigger('SEARCH_SHOW',new TwitterSearch());
        $('#statusUpdate').focus(); 
    });
    $('a[rel="savedSearch"]').live('click',function(e) {
        var search = $(this).data('searchObj'); 
        search.reset(); // clear away previous results.
        $(document).trigger('SEARCH_SHOW',[search]);

    });
    $('a[href="#del-search"]').live('click',function(e) {
        obj = $(this).data('searchObj');
        if (!confirm('Delete Saved Search: '+obj.saveName+'?')) return;
        obj.delete(function(search) {
            $(document).trigger('SEARCH_DELETED',[(search)]);
        });
    });
    
    $(document).bind('SEARCH_DELETED',function(e,search) {
        $('li.saved-search-'+search.id).remove(); // remove it form the DOM        
    });
    $(document).bind('SEARCH_UPDATED',function(e,search) {
        var domObj = $('li.saved-search-'+search.id); 
        if (domObj.length == 0) {
            // create a new one 
            $('<li></li>')
                .addClass('saved-search-'+search.id)
                .append(getSavedSearchLink(search))
                .append(', ')
                .append(getDeleteSearchLink(search))
                .appendTo('#savedSearches');
        } else { // update the display name of the current one. 
            domObj.empty()
            .append(getSavedSearchLink(search))
            .append(', ')
            .append(getDeleteSearchLink(search));
        }
        // rebuild the item in the list... 
        
        //$('li.saved-search-'+search.id).remove();
    }); 
    /*
    $(document).bind('SAVED_SEARCH_UPDATED',function(e,json) { // rebuild it.
        var searchObj = new TwitterSearch(json.saveName,json.query,json.id);
        var domEl = $('li.saved-search-'+searchObj.id); 
        if (domEl.length == 0) { // add a new one.
            $('<li></li>')
                .addClass('saved-search-'+searchObj.id)
                .append(getSavedSearchLink(searchObj))
                .append(', ')
                .append(getDeleteSearchLink(searchObj))
                .appendTo('#savedSearches');
        } else { // update a current one.
            domEl.empty()
            .append(getSavedSearchLink(searchObj))
            .append(', ')
            .append(getDeleteSearchLink(searchObj));
        }
    }); 
    */
    $(document).bind('TIMELINE_MANUAL_REFRESH',function(e) {
        GLOBALS.timeline.fetchLatest(); 
    });
    $(document).bind('REPLIES_MANUAL_REFRESH',function(e) {
        GLOBALS.replies.fetchLatest();
    });
    $(document).bind('MESSAGES_MANUAL_REFRESH',function(e) {
        GLOBALS.messages.fetchLatest();
    }); 
})

init.done('id.stackMenu.js DONE',function() {
    // Load and list the saved searches
    TwitterSearch.get(function(data) {
        for (var i in data) {
            var searchObj = new TwitterSearch(data[i].saveName,data[i].query,data[i].id);
            $('<li></li>')
                .addClass('saved-search-'+searchObj.id)
                .append(getSavedSearchLink(searchObj))
                .append(', ')
                .append(getDeleteSearchLink(searchObj))
                .appendTo('#savedSearches');
        }
    }); 
});

var getSavedSearchLink = function (search) {
    return $('<a></a>')
        .text(search.saveName)
        .attr('href','#saved-search-'+search.id)
        .attr('rel','savedSearch')
        .addClass('savedSearch')
        .addClass('savedSearch-'+search.id)
        .data('searchObj',search);
}
var getDeleteSearchLink = function(searchObj) {
    return $('<a>del</a>')
        .attr('href','#del-search')
        .data('searchObj',searchObj);
}

}