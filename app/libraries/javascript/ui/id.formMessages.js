if (init.add) {

init.add('id.formMessages.js INIT',function() {

    $(document).bind('FORM_UI_MESSAGE_CLEAR',function(e) {
        $('#formMessages').empty(); 
    });
    
    $(document).bind('FORM_UI_MESSAGE_POST',function(e,type,message,remove,timeout) {
        type = type || 'error';
        remove = remove || false; 
        timeout = timeout || 1500; 
        switch (type) {
            case 'error': 
                message = 'Error: ' + message; 
                break; 
            case 'notice': 
                message = 'Warning: ' + message; 
                break; 
            case 'success':
                message = 'Success: ' + message; 
                break; 
            default: 
                message = '????: ' + message; 
        }
        var msgObj = $('<p>');        
        msgObj.text(message)
            .addClass(type)
            .addClass('hide')
            .appendTo('#formMessages')
            .fadeIn();
        if (remove == true) {
            msgObj
                .animate({opacity:1.0},timeout)
                .fadeOut(function() {$(this).remove()});
        }
    });
}); 

}