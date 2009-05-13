if (init.add) {

init.add('ui.twitpic.js INIT',function() {
    /* add twitpic images to the right */
    $(document).bind('STACK_ITEM_REFRESHED',function(e,item,domEl) {
        $(domEl).children('p.stackText').each(twitpicPara);
    });
    var twitpicPara = function() {
        var t = $(this).html();
        var m = (/twitpic.com\/([a-z0-9]*)<\/a>/i).exec(t); 
        if (m != null) {
            var id = m[1]; 
            var img = $('<img src="http://twitpic.com/show/mini/'+id
                +'" class="twitpic" alt="twitpic">').qtip({
                show        : { solo : true, when : { event : 'mouseover' }},
                hide        : { when : { event: 'mouseout'}},
                style       : { name: 'cream', tip:'leftMiddle', width : {min:180, max: 'auto'}, 
                                "text-align" : 'center'
                                },
                position    : { corner: {target: 'rightMiddle',tooltip:'leftMiddle'}},
                content     : { text  : '<img src="http://twitpic.com/show/thumb/'+id+'" height="150" width="150" alt="loading...">'}
            });
            var link = $('<a target="_blank" href="http://twitpic.com/'+id+'"></a>')
                .html(img);
            $(this).prepend(link);                
        }
    }
});


}