if (init.add) {

init.add('ui.tooltips.js INIT',function() {    

    $(document).bind('STACK_ITEM_REFRESHED',function(e,item,el) {
        $('.qtip').remove(); // any ones sticking around. 
        var stackId = '.stackItem-'+item.id;
        $('a.twitterScreenName').each(function() {
            if ($(this).data('tooltipped') == true) return;
            $(this).data('tooltipped',true);
            var screen_name = $(this).attr('href').substr(1); //screen name
            $(this).qtip({
                show        : 'click',
                hide        : 'unfocus',
                style       : { width: 400, 
                                padding: 5},
                position    : { corner: {target: 'bottomLeft',tooltip:'topLeft'}},
                content     : { url: '/webservice/userinfo.php',
                                data: { i : screen_name},
                                method: 'get',
                                title : { 
                                    text: 'Viewing: '+screen_name, 
                                    button: 'Close'
                                }
                              }
            });
        });
        
        // default one
        $('a.urlLink:not([href*=twitpic.com])').each(function() {
            if ($(this).data('tooltipped') == true) return; 
            $(this).data('tooltipped',true);
            $(this).qtip( {
                    show        : { solo : true, when : { event : 'mouseover' }},
                    hide        : { when : { event: 'mouseout'}},
                    style       : { name: 'dark', width : {min:160, max: 'auto'}, "text-align" : 'center'},
                    position    : { corner: {target: 'bottomLeft',tooltip:'topLeft'}},
                    content     : { url: '/webservice/expandurl.php',
                                    data: { url : $(this).attr('href')},
                                    method: 'post',
                                    text  : '<img src="/css/images/loading-red.gif" width="43" height="11">'
                                  }
            });
        });
        // custom styling for twitpic
        $('a.urlLink[href*=twitpic.com]').each(function() {
            if ($(this).data('tooltipped') == true) return; 
            $(this).data('tooltipped',true);
            
            $(this).qtip( {
                    show        : { solo : true, when : { event : 'mouseover' }},
                    hide        : { when : { event: 'mouseout'}},
                    style       : { name: 'cream', tip:'leftMiddle', width : {min:180, max: 'auto'}, 
                                    "text-align" : 'center'
                                    },
                    position    : { corner: {target: 'rightMiddle',tooltip:'leftMiddle'}},
                    content     : { url: '/webservice/expandurl.php',
                                    data: { url : $(this).attr('href')},
                                    method: 'post',
                                    text  : '<img src="/css/images/loading-red.gif" width="43" height="11">'
                                  }
            });
        });
    });
});

}
/*
var qTipHandlers = {}
qTipHandlers.checkUrl = function() {
    // var url = this.options.content.data.url;
    // check the URL before passing, might need this in the future.
}
qTipHandlers.getUrl = function() {
     var url = this.options.content.url;
     var data = this.options.content.data;
     var method = this.options.content.method
     this.loadContent(url, data, method);    
}
qTipHandlers.checkShow = function(e) {
    var api = $(e.target).qtip('api'); 
    if (api.elements.content.text() == '') {
        var url = this.options.content.url;
        var data = this.options.content.data;
        var method = this.options.content.method
        this.loadContent(url, data, method);
    }
//    return (api.elements.content.text() != '|||');
}
/* References ... since this library isn't doc'd that well yet; 

http://craigsworks.com/projects/qtip/forum/topic/114/dynamic-content-loading/

*/