if(init.add) {

var STACKITEM_AJAX_MGR = $.manageAjax({ 
    manageType:'queue',
    maxReq:2,
    blockSameRequest: true
    });

init.add('class.stackItem.js INIT',function(){
    
    var si = $('.stackItem');
    si.live('REFRESH',function(e) {
        var item = $(this).data('data');
        $(this)
            .addClass('stackItem-'+item.id)
            .empty()
            .html(renderItem(item))
            .toggleClass('vote-like',item.flags.VOTE=='1')
            .toggleClass('vote-lame',item.flags.VOTE=='-1')
	        .toggleClass('stackItemFavorited',(item.favorited == true || item.favorited == 1));        
        if (item.flags.SHOW == '1' && typeof item.replyTarget == 'object') {
            $('<div class="stackItem replyTarget"></div>')
                .data('data',item.replyTarget)
                .appendTo(this)
                .trigger('REFRESH');
        }
        $(document).trigger('STACK_ITEM_REFRESHED',[item,this]);
    });

    si.live('SELECT_ITEM',function(e){
        e.stopImmediatePropagation();
        // remove it if we actually switch items
        if (!$(this).hasClass('stackItemSelected')) 
            $(document).trigger('REMOVE_INLINE_REPLIES');
        $('.stackItem')
            .removeClass('stackItemSelected')
            .removeClass('stackItemMouseOver');
        $(this).addClass('stackItemSelected');
    }); 
    si.live('mouseover',function(e){
        e.stopImmediatePropagation();
        $(this).addClass('stackItemMouseOver');
    });
    si.live('mouseout',function(e){
        e.stopImmediatePropagation();
        $(this).removeClass('stackItemMouseOver');
    });
    si.live('click',function(e) {
        e.stopImmediatePropagation();
        $(this).trigger('SELECT_ITEM'); 
    }); 
    si.live('HIDE',function(e){
        e.stopImmediatePropagation();
        $(document).trigger('REMOVE_INLINE_REPLIES');
        var t = $(this).data('data');
        if (t == undefined || t.flags.SHOW == '0') return; 
        t.flags.SHOW = '0'; 
        $(this).data('data',t);
        $(this).trigger('REFRESH');
        setItemFlag(t,'SHOW',t.flags.SHOW);
    });
    si.live('SHOW',function(e){
        e.stopImmediatePropagation();
        var t = $(this).data('data'); 
        if (t == undefined || t.flags.SHOW == '1') return; 
        t.flags.SHOW = '1'; 
        $(this).trigger('REFRESH');
        setItemFlag(t,'SHOW',t.flags.SHOW);
    });

    var rateItem = function(item,rating) {
        item.flags.VOTE=rating;
        setItemFlag(item,'VOTE',item.flags.VOTE);
    }
    si.live('RATE_LIKE',function(e) {
        e.stopImmediatePropagation();
        rateItem($(this).data('data'),'1');
        $(this).trigger('REFRESH');
    }); 
    si.live('RATE_LAME',function(e) {
        e.stopImmediatePropagation();
        rateItem($(this).data('data'),'-1');
        $(this).trigger('REFRESH');
    }); 
    si.live('RATE_NONE',function(e) {
        e.stopImmediatePropagation();
        rateItem($(this).data('data'),'0');
        $(this).trigger('REFRESH');
    }); 
    
    si.live('READ',function(e) {
        e.stopImmediatePropagation();
        var t = $(this).data('data');
        if (t == undefined || t.flags.READ == '1') return; 
        t.flags.READ = '1'; 
        setItemFlag(t,'READ','1');
    });
    
    var toggleShow = function(e) {
        e.stopImmediatePropagation();
        var t = $(this).data('data');
        if (t == undefined) return; 
        if (t.flags.SHOW == '1')
            $(this).trigger('HIDE'); 
        else 
            $(this).trigger('SHOW');
    }
    si.live('dblclick',toggleShow)
        .live('TOGGLE_SHOW',toggleShow);
    
    // Bind Reply, Retweet, Favorite actions
    si.live('REPLY',function(e) {
        // so something here evetually.. .
        e.stopImmediatePropagation();
        $(document).trigger('REPLY_TO_ITEM',[$(this).data('data')]);
    });
    
    si.live('RETWEET',function(e){
        e.stopImmediatePropagation();
        $(document).trigger('RETWEET_ITEM',[$(this).data('data')]);        
    }); 
    si.live('FAVORITE',function(e){
        e.stopImmediatePropagation();
        var domE = $(this); 
        var item = domE.data('data');
        var newVal = ! item.favorited;
        
        // do an ajax call to update it on the server side.
        STACKITEM_AJAX_MGR.add( 
        {
            type:       'POST',
            url:        "/webservice/favorite.php",
            data:       { twId:item.id, val: newVal },
            dataType:   'json',
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                debug('(ajax) Failed to change favorite: '+textStatus);
            },
            success: function (jsonData,textStatus) {
                // favorite the same status in timeline
                if (jsonData == undefined || jsonData.id == undefined) {
                    return; 
                }
                var tItem = GLOBALS.timeline.getById(item.id);
                if (tItem != null) tItem.favorited = newVal;

                // favorite the same status in Replies
                var rItem = GLOBALS.replies.getById(item.id);
                if (rItem != null) rItem.favorited = newVal;
                
                // favorite single items in reply tree stack
                var treeItem = GLOBALS.replyTree.getById(item.id); 
                if (treeItem != null) treeItem.favorited = newVal;
                
                // manage GLOBALS.favorites
                if (newVal == false) { // delete the item from the array
                    var newArray = GLOBALS.favorites.getStack().filter(function(x) {
                        return (x.id != jsonData.id); 
                    });
                    GLOBALS.favorites.setStack(newArray);
                    
                    if ($(document).data('CURRENT_STACK') == 'favorites') {
                        $('.stackItem-'+jsonData.id).fadeOut(function() { 
                            $(this).remove(); 
                        });
                    }
                } else {
                    GLOBALS.favorites.updateStack([jsonData]);
                }
                
                /* refresh all renders of the current stack 
                   might be in reply tree and in stackDisplay
                 */
                $('.stackItem-'+jsonData.id).trigger('REFRESH');
                trackAnalytics("/webservice/show.php?type=message");
                $(document).trigger('FAVORITES_CHANGED');
            }
        });
    });
    
    /***********************************************************************
     * SHOWING REPLY TREE 
     ***********************************************************************/
    $('.in-reply').live('click',function(e) {
        e.stopImmediatePropagation();
        $(this).trigger('SHOW_REPLY'); // trigger it 
        return false;
    });
    si.live('SHOW_REPLY',function(e) {
        e.stopImmediatePropagation();
        var parent = $(this); 
        var item = parent.data('data');

        if (typeof item.replyTarget == 'object') { 
            item.replyTarget.flags.SHOW = '1'; 
            parent.trigger('REFRESH'); 
            return; 
        }

        var rid = item.in_reply_to_status_id; 
        if (rid != null) {
            STACKITEM_AJAX_MGR.add( 
            {
                type:       'GET',
                url:        "/webservice/status/show.php",
                data:       { id : rid},
                dataType:   'json',
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    debug('(ajax) failed to fetch status... '+textStatus);
                },
                success: function (itemData,textStatus) {
                    // add this item to the replyTree singletons, link it
                    GLOBALS.replyTree.updateStack([itemData]);
                    item.replyTarget = itemData;
                    parent.trigger('REFRESH');
                    trackAnalytics("/webservice/status/show.php");
                }
            });
        }
    });
    /**** INLINE REPLIES ****/
    si.live('INLINE_REPLY',function(e) {
        e.stopPropagation();
        var i = $(this).data('data'); 
        if (i == undefined || i.flags == undefined || i.flags.SHOW != '1') return;
        $(document).trigger('ATTACH_INLINE_REPLY',[this]); 
    });
            
    // Track clicks on any links in the .stackText 
    $('a.screenNameLink').live('click',function(e) {
        // do something here to pop up a window showing the user's latest
        // tweets
        // $(document).trigger('SHOW_USER'); ... 
    });
    
    $('a.urlLink').live('click',function(e) {
        $(this).trigger('LINK_CLICKED'); // send this up the stack. 
    }); 
    
    // Bind keyboard navigation listeners
    var d = $(document);
    d.bind('TOGGLE_SHOW',function(e) {
        var item = $('.stackItemSelected');
        item.trigger('TOGGLE_SHOW'); 
        $(this).trigger('SCROLL_TO_ITEM',[item]);
    });
    d.bind('NEXT_ITEM',function(e) {
        var item = $('.stackItemSelected');
        item.next().trigger('SELECT_ITEM');
        $(this).trigger('SCROLL_TO_ITEM',[item]);
    }); 
    d.bind('PREV_ITEM',function(e) {
        var item = $('.stackItemSelected');
        item.prev().trigger('SELECT_ITEM')
        $(this).trigger('SCROLL_TO_ITEM',[item]);
    });
    d.bind('READ_CLOSE',function(e){
        var item = $('.stackItemSelected');
        item.trigger('HIDE').trigger('READ');
        $(this).trigger('NEXT_ITEM');
        $(this).trigger('SCROLL_TO_ITEM',[item]);
    });
    d.bind('SCROLL_TO_ITEM',function(e,item) {
        
        var pos = item.offset(); 
        var posTop = Math.round(pos.top);
        var winTop = Math.round($(window).scrollTop());
        var height = Math.round($(window).height());
        var bottom = ( winTop + height); 
    
        if (posTop < winTop+50) { // scroll up
            $('html,body').animate({scrollTop: (posTop-(height/2))}, 250);
            // debug("Scrolling up");
        }
        if (posTop > (bottom-100)) { // scroll down
            $('html,body').animate({scrollTop: (posTop)}, 250);
            //debug('Scrolling Down');
        }
    });
    d.bind('STACKDISPLAY_PAGE',function(e) {
        var item = $('.stackItem:first'); 
        item.trigger('SELECT_ITEM');
        $(this).trigger('SCROLL_TO_ITEM',[item]);
    }); 
});
}

// Common AJAX Functions
function setItemFlag(item,flagType,flagValue) {
    
    var itemType = 'X'; 
    
    if (item.sender) itemType = 'MESSAGE'; // a message item 
    if (item.user) itemType = 'STATUS';  // a status item
    if (flagValue == undefined) flagValue = '';
    
    STACKITEM_AJAX_MGR.add( 
    {
        type:       'POST', 
        url:        "/webservice/flag.php?"+itemType+'&'+flagType,
        data:       { tid:item.id,type :itemType,ftype:flagType,fval:flagValue },
        dataType:   'text',
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            debug('(ajax) Failed to mark show: '+textStatus);
        },
        success: function (data,textStatus) {
            trackAnalytics("/webservice/flag.php?type="+itemType);
        }
    });
}

