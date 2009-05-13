if (init.add) {

init.add('id.dmRecipient.js INIT',function() {
    $('#formStatusUpdate').bind('INTENT_DM',function(e,item) {
        $('#dmRecipient').fadeIn(); 
        if (item && typeof item.sender == 'object') {
            $('#recipient').val(item.sender.screen_name).keyup();
        }
    });

    var hideFunc = function(e) {
        $('#dmRecipient').fadeOut(); 
        $('#recipient').val('');
    };
    
    $('#formStatusUpdate')
        .bind('INTENT_SEARCH',hideFunc)
        .bind('INTENT_UPDATE',hideFunc)
        .bind('INTENT_REPLY',hideFunc) 
        .bind('INTENT_RETWEET',hideFunc);
});

}