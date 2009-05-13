if (init.add) {

init.add('pageNavigation-init',function() {

    $(document).bind('STACKDISPLAY_PAGE',function(e,page,numItems) {        
        (numItems > 0) ? 
            $('.pageNav').fadeIn(): 
            $('.pageNav').fadeOut();
        
        if (page == 1) {
            $('#pageNumber').html('<h3>Latest Tweets</h3>');
            $('li[rel=firstPage]').fadeOut('fast');
            $('li[rel=prevPage]').fadeOut('fast');
        } else {
            $('#pageNumber').html('<h3>Page: '+page+'</h3>');
            $('li[rel=firstPage]').fadeIn('fast');
            $('li[rel=prevPage]').fadeIn('fast');
            $('li[rel=nextPage]').fadeIn('fast');        
        }

        (numItems != 20) ?
            $('li[rel=nextPage]').fadeOut('fast') :
            $('li[rel=nextPage]').fadeIn('fast');       
    });


    var hidePageNav = function() {
        $('.pageNav').hide();
    }
    $(document).bind('SHOW_TIMELINE',hidePageNav)
    .bind('SHOW_REPLIES',hidePageNav)
    .bind('SHOW_MESSAGES',hidePageNav)
    .bind('SHOW_SENT_MESSAGES',hidePageNav)
    .bind('SHOW_FAVORITES',hidePageNav)
    .bind('SEARCH_SHOW',hidePageNav);
    
    // bind actions to the page navigation
    $('li[rel="firstPage"]>a').click(function() {
        $(document).trigger('GOTO_FIRST_PAGE'); 
    });
    $('li[rel="prevPage"]>a').click(function() {
        $(document).trigger('GOTO_PREV_PAGE'); 
    }); 
    $('li[rel="nextPage"]>a').click(function() {
        $(document).trigger('GOTO_NEXT_PAGE'); 
    });
     
}); 
    
}