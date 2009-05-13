if (init) {
    
init.add('init-stackDisplay',function() {
    var sd = $('#stackDisplay');         
    var doc = $(document);
    
    // this sets the data stack to render
    sd.bind('SET_STACK',function(e,stack) {
        $(this).data('STACK',stack); // reference it. 
    });
    
    // renders a page out of the current data stack
    sd.bind('RENDER_PAGE',function(e,page,noFetch) {
        var stack = $(this).data('STACK'); 
        var pageStack = stack.getPage(page);
        var currentStack = $(document).data('CURRENT_STACK'); 
        var minItems = 0; 
        noFetch = noFetch || false; 
        
        if (!noFetch && (pageStack.length < 20)) {
            debug('page '+page+' empty.  Fetching Page: '+page);
            stack.fetchPage(page,function(page,data) { // the call back
                $('#stackDisplay').trigger('RENDER_PAGE',[page,true]);
            });
            return;
        }

        $(this).empty();            
        for (var i in pageStack) {
            var stackItem = pageStack[i]; 
            var domItem =  $('<div class="stackItem"></div>') // create a new stackItem element
                .data('data',stackItem)
                .appendTo(this)
                .trigger('REFRESH');

            if (i == pageStack.length-1)
                domItem.addClass('lastItem');
        }
        $(this).data('CURRENT_PAGE',page);
        $(document).trigger('STACKDISPLAY_PAGE',[page,pageStack.length]); // notify I am showing page ... 
    });  // RENDER_PAGE
    
    // Navigation watches... 
    doc.bind('GOTO_FIRST_PAGE',function(e,page) {
        if ($(this).data('CURRENT_STACK') == 'search') {
            $('#stackDisplay').data('SEARCH_OBJECT').page(1,function(search) {
                $(document).trigger('SEARCH_SHOW',[search]);
            });
        } else {
            $('#stackDisplay').trigger('RENDER_PAGE',[1]);
        }        
    }); 
    doc.bind('GOTO_PREV_PAGE',function(e,page) {
        if ($(this).data('CURRENT_STACK') == 'search') {
            var search = $('#stackDisplay').data('SEARCH_OBJECT');
            if (search.response.previous_page == undefined) return; 
            search.page((parseInt(search.response.page)-1),function(search){
                    $(document).trigger('SEARCH_SHOW',[search]);
                });
        } else {
            var sd = $('#stackDisplay');
            var prevPage = sd.data('CURRENT_PAGE') - 1; 
            if (prevPage > 0) sd.trigger('RENDER_PAGE',[prevPage]);
        }
    }); 
    doc.bind('GOTO_NEXT_PAGE',function(e) {
        if ($(this).data('CURRENT_STACK') == 'search') {
            var search = $('#stackDisplay').data('SEARCH_OBJECT');
            if (search.response.next_page == undefined) return; 
            search.page((parseInt(search.response.page)+1),function(search){
                    $(document).trigger('SEARCH_SHOW',[search]);
                });
        } else {
            var sd = $('#stackDisplay');
            var stack = sd.data('STACK'); 
            var items = stack.getPage(sd.data('CURRENT_PAGE'));
            var nextPage = sd.data('CURRENT_PAGE') +1;
            if (nextPage <= 10 && items.length == 20) 
                sd.trigger('RENDER_PAGE',[nextPage]);
        }        
    }); 
    
    // Change Stack to display 
    doc.bind('SHOW_TIMELINE',function(e) {
        $(this).data('CURRENT_STACK','timeline');
        $('#stackDisplay')
            .trigger('SET_STACK',[GLOBALS.timeline])
            .trigger('RENDER_PAGE',[1]);        
    }); 
    doc.bind('SHOW_REPLIES',function(e) {
        $(this).data('CURRENT_STACK','replies');
        $('#stackDisplay')
            .trigger('SET_STACK',[GLOBALS.replies])
            .trigger('RENDER_PAGE',[1]);        
    }); 
    doc.bind('SHOW_MESSAGES',function(e) {
        $(this).data('CURRENT_STACK','messages');
        $('#stackDisplay')
            .trigger('SET_STACK',[GLOBALS.messages])
            .trigger('RENDER_PAGE',[1]);        
    }); 
    doc.bind('SHOW_SENT_MESSAGES',function(e) {
        $(this).data('CURRENT_STACK','sentMessages');
        $('#stackDisplay')
            .trigger('SET_STACK',[GLOBALS.sentMessages])
            .trigger('RENDER_PAGE',[1]);        
    });
    doc.bind('SHOW_FAVORITES',function(e) {
        $(this).data('CURRENT_STACK','favorites'); 
        $('#stackDisplay')
            .trigger('SET_STACK',[GLOBALS.favorites])
            .trigger('RENDER_PAGE',[1]);
    });
    doc.bind('SEARCH_SHOW',function(e,search) {
        $(this).data('CURRENT_STACK','search'); 
        var sd = $('#stackDisplay').empty().data('SEARCH_OBJECT',search);
        
        // Run the search if it hasn't been run already.
        if (search.searchRun != true && search.query.length != 0) {
            sd.html('<p>Fetching search results... <img src="/css/images/loading-red.gif" alt="loading"></p>');
            search.page(1,function(search) {
                $(document).trigger('SEARCH_SHOW',[search]);
            });
            return; 
        }
        
        if (search.results.length == 0) {
            doc.trigger('STACK_MESSAGE',['notice','No Search Results to Display',false]);
            doc.trigger('STACKDISPLAY_PAGE',[0,0]); // trigger display of nothing.
            return; 
        }

        var sd = $('#stackDisplay').empty();
        for (i in search.results) {
            var item = $('<div class="stackItem"></div>')
                .addClass('stackItem-'+search.results[i].id)
                .addClass('searchResultItem')
                .html(renderItem(search.results[i]))
                .data('data',search.results[i])
                .appendTo(sd);
            doc.trigger('STACK_ITEM_REFRESHED',[search.results[i],item]);
            
            if (i == search.results.length-1) 
                item.addClass('lastItem');
        }
        doc.trigger('STACKDISPLAY_PAGE',[search.response.page,search.results.length]);        
    }); 

    doc.bind('SEARCH_DELETED',function(e,search){
        var curSearch = $('#stackDisplay').data('SEARCH_OBJECT');
        if (curSearch == undefined) return; 
        if (curSearch.id == search.id) {
            var x = new TwitterSearch();
            $('#stackDisplay').empty().data('SEARCH_OBJECT',x); 
            if (doc.data('CURRENT_STACK') == 'search') {
                doc.trigger('STACK_MESSAGE',['notice','Search Deleted',false])
                .trigger('SEARCH_SHOW',[x]);
            }
        }
    }); 
        
    // Showing changes when stacks change
    doc.bind('TIMELINE_FETCH_LATEST_RESULTS',function(e,delta) {
        if ($(this).data('CURRENT_STACK') != 'timeline' || delta.length == 0)
            return;
        $('#stackDisplay').trigger('RENDER_PAGE',[1]);
    });
    doc.bind('REPLIES_FETCH_LATEST_RESULTS',function(e,delta) {
        if ($(this).data('CURRENT_STACK') != 'replies' || delta.length == 0)
            return;
        $('#stackDisplay').trigger('RENDER_PAGE',[1]);
    });
    doc.bind('MESSAGES_FETCH_LATEST_RESULTS',function(e,delta) {
        if ($(this).data('CURRENT_STACK') != 'messages' || delta.length == 0)
            return;
        $('#stackDisplay').trigger('RENDER_PAGE',[1]);
    });

}); 

}