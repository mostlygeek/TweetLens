/* 
  This JS library controls all the events / actions on the statusUpdate TEXTAREA
 */

if (init) {

init.add("id.statusUpdate.js INIT", function() {
    // Define all the actions 
    var statusUpdate = $('#statusUpdate'); 
    statusUpdate
        .bind('UPDATE_CHAR_COUNT',updateCharacterCount)
        .keypress(updateCharacterCount)
        .keyup(updateCharacterCount)
        .keyup(updateFormIntent)
        .keyup(function(e) {
            if (e.keyCode == 27) { // esc
                $(this).blur(); 
                $(document).focus();
            }
        });

    //statusUpdate.bind('INTENT_UPDATE')
    $('#formStatusUpdate').bind('INTENT_DM',function(e,item){
        if (item && typeof item.sender == 'object') {
            statusUpdate.focus();
        }
        $('html,body').animate({scrollTop: 0}, 250);
    }).bind('INTENT_REPLY',function(e,item) {
        // for statuses
        if (item && typeof item.user == 'object') {
            var text = '@'+item.user.screen_name+' ';
            statusUpdate.val(text).focus().trigger('UPDATE_CHAR_COUNT');
        }
        $('#statusUpdate').data('lastTrigger','REPLY'+item.user.screen_name)
        $('html,body').animate({scrollTop: 0}, 250);
    }).bind('INTENT_RETWEET',function(e,item) {
        if (item && typeof item.user == 'object') {
            var u = item.user; 
            var text = 'RT @'+u.screen_name+' '+item.text
            statusUpdate.val(text).trigger('UPDATE_CHAR_COUNT').focus();
        }
        $('html,body').animate({scrollTop: 0}, 250);
    }).bind('INTENT_SEARCH',function(e,search) {
        statusUpdate.val(search.query);
    });
});

init.done('id.statusUpdate.js DONE',function() {
    /* sometimes Firefox fills in the form. want to make sure
       we trigger the right event 
     */  
    $('#statusUpdate').keyup();
}); 

function updateCharacterCount(e) { 
    $('#charCount').text( 140 - $('#statusUpdate').val().length);
    e.stopPropagation(); 
}

function updateFormIntent(e) {
/* this function throws out events to the rest of the form 
   to act on the intent of what the user is doing. 
   
   This only throws events if there is an actual change to be done. 
   Does this by keeping track of the last trigger sent. 
   
 */ 
    var status = $(this).val();
    var form = $('#formStatusUpdate');

    if (form.data('FORM_MODE') != 'STATUS') return; 
    
    if (status.substr(0,1) == '@') {
        var match = (/^@([A-Za-z0-9_]+) /).exec(status);
        if (match != null) {
            var recipient = match[1]; 
            if ($(this).data('lastTrigger') == 'REPLY'+recipient) 
                return; 
            $(this).data('lastTrigger','REPLY'+recipient); 
            $('#formStatusUpdate').trigger('INTENT_REPLY',
                [{user:{screen_name:recipient}}] // tweet form, for consistancy
            );
        }
    } else if (status.substr(0,3) == 'RT ') {
        if ($(this).data('lastTrigger') == 'RETWEET') return; 
        $(this).data('lastTrigger','RETWEET');
        $('#formStatusUpdate').trigger('INTENT_RETWEET');    
    
    } else if (status.substr(0,3).toLowerCase() == 'dm ') {
        var match = (/^dm ([A-Za-z0-9_]+)/i).exec(status);
        if (match != null) {
            var recipient = match[1]; 
            if ($(this).data('lastTrigger') == 'DM'+recipient) return; 

            $(this).data('lastTrigger','DM'+recipient); 
            $('#formStatusUpdate').trigger('INTENT_DM',
                [{sender:{screen_name:recipient}}] // tweet form, for consistancy
            );
        }        
    } else {
        if (status.length > 0 && $(this).data('lastTrigger') == 'UPDATE') return; 
        $(this).data('lastTrigger','UPDATE')
        $('#formStatusUpdate').trigger('INTENT_UPDATE');
    }

}

}