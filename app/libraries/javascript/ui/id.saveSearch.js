if (init.add) {

init.add('id.saveSearch.js INIT',function() {

    var showSearch = function() {
        var x = $('#saveSearch'); 
        if (x.data('v')) return; 
        x.show().data('v',true);
    } 
    var hideSearch = function() {
        var x = $('#saveSearch'); 
        if (!x.data('v')) return; 
        x.hide().data('v',false);
    }
    $('#formStatusUpdate').bind('INTENT_SEARCH',showSearch)
        .bind('INTENT_UPDATE',hideSearch)
        .bind('INTENT_REPLY',hideSearch)
        .bind('INTENT_RETWEET',hideSearch)
        .bind('INTENT_DM',hideSearch);
}); 

}