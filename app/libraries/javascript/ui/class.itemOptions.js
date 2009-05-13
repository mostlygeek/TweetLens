if(init.add) {
init.add('class.itemOptions.js',function() {
    $('.itemOptions a').live('click',function(e) {
        var action = $(this).attr('href'); 
        switch (action) {
//            case '#reply'       : $(this).trigger('REPLY'); break;
            case '#reply'       : $(this).trigger('INLINE_REPLY'); break;
            case '#retweet'     : $(this).trigger('RETWEET'); break; 
            case '#favorite'    : 
            case '#unfavorite'  : $(this).trigger('FAVORITE'); break; 
            case '#close'       : $(this).trigger('HIDE'); break; 
            case '#like'        : $(this).trigger('RATE_LIKE'); break; 
            case '#unlike'      : $(this).trigger('RATE_NONE'); break;
        }
        e.stopImmediatePropagation();
    }).live('dblclick',function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        return false;
    }); 
    
    // Decide what to add options to ... 
    $('.stackItem').live('SELECT_ITEM',function(e) {
        $('.stackItem .itemOptions').remove();
        // generate the options 
        var options = makeOptions($(this).data('data')); 
        if (options != false) 
            $(this).append(options); 
    });
    $(document).bind('STACK_ITEM_REFRESHED',function(e,item,domElement) {
        if (!$(domElement).hasClass('stackItemSelected')) return; 
        $('.stackItem .itemOptions').remove();
        // generate the options 
        var options = makeOptions($(domElement).data('data')); 
        if (options != false) 
            $(domElement).append(options);
    }); 

    function makeOptions(item) {
        if (item == undefined) return false; 

        // generate the options
        var options = []; 
        // a visible status object
        if (item.user != undefined && item.flags.SHOW == '1') { 
            options.push('reply','retweet');
            options.push((item.favorited) ? 'unfavorite' : 'favorite');
            options.push((item.flags.VOTE == "1") ? 'unlike' : 'like'); 
            options.push('close');
            options.push({link:
                '<a href="http://twitter.com/'
                +item.user.screen_name+'/status/'
                +item.id+'" target="_blank">View on Twitter</a>'
                });
        }
        
        // a search item 
        if (item.from_user != undefined) {
            options.push('reply');
            debug('search item options');
        }
        
        var html = '<ul class="itemOptions">'; 
        for (var i in options) {
            var opt = options[i];
            html += (typeof opt == 'object') ? 
                '<li>'+opt.link+'</li>' : 
                '<li><a href="#'+opt+'">'+opt+'</a></li>';
        }
        html += '</ul>'; 
        return html; 
    }
});
}
/*
<ul class="itemOptions">
    <li><a href="#reply">reply</a></li>
    <li><a href="#retweet">retweet</a></li>
    <li><a href="#favorite">favorite</a></li>
    <li><a href="#close">close</a></li>
    <li><a href="#like">Like</a></li>
</ul>
*/