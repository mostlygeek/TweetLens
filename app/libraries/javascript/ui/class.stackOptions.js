if (init.add) {

init.add('id.stackOptions.js INIT',function() {
    $('a[href=#mark-all-as-read]').click(function(e) {
        $('#stackDisplay > .stackItem').trigger('READ').trigger('HIDE');
    });
    $('a[href=#show-all]').click(function(e) {
        $('#stackDisplay > .stackItem').trigger('SHOW');
    });
    $('a[href=#hide-all]').click(function(e) {
        $('#stackDisplay > .stackItem').trigger('HIDE');
    });

    /* returns the twitter id values of current display 
       stack. For batching operations in the future 
     */      
    var getStackIds = function() {
        var sI = $('.stackItem');
        var ids = []; 
        for (var i=0; i < sI.length; i++) {
            ids.push(sI.eq(i).data('data').id);
        }
        // */
    }
    $(document).bind('STACKDISPLAY_PAGE',function(e,page,numItems) {
        if (numItems > 0) {
            $('.stackOptions').fadeIn();
        } else {
            $('.stackOptions').fadeOut();
        }
    });
    var showStackOpts = function() {
        $('.stackOptions li').show();
    }
    var hideStackOpts = function() {
        $('.stackOptions li').hide();
    }
    $(document).bind('SHOW_TIMELINE',showStackOpts)
    .bind('SHOW_REPLIES',showStackOpts)
    .bind('SHOW_MESSAGES',showStackOpts)
    .bind('SHOW_SENT_MESSAGES',showStackOpts)
    .bind('SHOW_FAVORITES',showStackOpts)
    .bind('SEARCH_SHOW',hideStackOpts);
});

}