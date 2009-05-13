if (init.add) {
    init.add("id.ajaxWatch.js",function() {
        var ajaxWatch = $('#ajaxWatch'); 
        ajaxWatch.bind('ajaxSend', function() {
            $(this)
                .html('Requesting... <img src="/css/images/loading-red.gif">')
                .show();
            });
            
        ajaxWatch.bind('ajaxStop',function() {
            $(this).hide();
        });
        
        ajaxWatch.bind('ajaxComplete',function() {
            $(this).hide();
        });
    })
}