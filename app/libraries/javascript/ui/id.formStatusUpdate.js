/***
  
  Status Update Form Functionality 
  
 ***/

// register our initialization function for when the DOM is ready
if (init.add) {

var FORM_AJAX = $.manageAjax({ 
    manageType:'queue'
    });

init.add('form.formStatusUpdate INIT', function() {
    /* Events to listen to .. 
      
       SHOW_TIMELINE, SHOW_REPLIES, SHOW_MESSAGES
       SHOW_SENTMESSAGES, POST_UPDATE,
       REPLY_TO_ITEM, RETWEET_ITEM
       
      Events the form children listen for on the form
      
       INTENT_UPDATE()
       INTENT_REPLY(status)
       INTENT_RETWEET(status)
       INTENT_DM(message)
      */
    var form = $('#formStatusUpdate');

    // Bind Event handlers for the form
    var setFormIntent = function(e) {
//        e.stopPropagation();
        if (form.data('FORM_TARGET') == 'STATUS') return;
        $('#submitButton').html('Update');
        form.data('FORM_TARGET','STATUS')
        debug('Form target changed to STATUS UPDATE');
        e.stopPropagation();
    }; 
    form.bind('INTENT_UPDATE',setFormIntent);
    form.bind('INTENT_RETWEET',setFormIntent);
    form.bind('INTENT_REPLY',setFormIntent);
    form.bind('INTENT_DM',function(e) {
        if (form.data('FORM_TARGET') == 'MESSAGE') return;
        $('#submitButton').html('Send');        
        form.data('FORM_TARGET','MESSAGE')
        debug('Form target changed to DIRECT MESSAGE');        
    });
    form.bind('INTENT_SEARCH',function(e) {
        if (form.data('FORM_TARGET') == 'SEARCH') return;
        $('#submitButton').html('Search');
        form.data('FORM_TARGET','SEARCH');
        debug('Form target changed to SEARCH');        
    });
    form.bind('submit',function(e) { return false }) // do nothing by default.
        .bind('submit',function(e) {
        // figure out what/where to send the data. 
        var me = this; 
        var ft = $(this).data('FORM_TARGET');
        debug("FORM TARGET: "+ft);
        switch (ft) {
            case 'STATUS': 
                debug('Performing a Status Update');
                handleStatusUpdate(); 
                break; 
            case 'SEARCH': 
                debug('Performing a search');
                handleSearch();
                break; 
            case 'MESSAGE': 
                debug('Performing a message');
                handleMessageSend();
                break; 

        }
        return false;
    }); 
    form.bind('RESET',function(e) {
        // clear everything. 
        e.stopPropagation();
        $('#formStatusUpdate textarea,input').val('').keyup();
        $(document).trigger('FORM_UI_MESSAGE_CLEAR'); 
    });

    var f_intent_update = function(e) {
        form.data('FORM_MODE','STATUS').trigger('RESET');
        form.trigger('INTENT_UPDATE');
    }
    var f_intent_dm = function(e) {
        form.data('FORM_MODE','MESSAGE').trigger('RESET');
        form.trigger('INTENT_DM');        
    }
    $('#updateSearch').click(function() {
        var search = form.data('SEARCH_OBJ'); 
        var q = $('#statusUpdate').val().trim();
        var n = $('#saveName').val().trim();
        
        if (n == '') {
            $(document).trigger('FORM_UI_MESSAGE_POST',['error','Save name required',true]);
            return false;            
        }
        if (q == '') {
            $(document).trigger('FORM_UI_MESSAGE_POST',['error','Query required',true]);
            return false;            
        }
        search.setQuery(q);
        search.setSaveName(n);
        search.save(function(search) {
            debug('saved query: '+search.saveName);
            $(document).trigger('SEARCH_UPDATED',[search]);
        });
        return false; 
    }); 
    $('#resetForm').click(function() {
        form.trigger('RESET');
        return false;
    });
    

    $(document)
        .bind('POST_UPDATE',f_intent_update)
        .bind('SHOW_TIMELINE',f_intent_update)
        .bind('SHOW_REPLIES',f_intent_update)
        .bind('SHOW_MESSAGES',f_intent_dm)
        .bind('SHOW_SENT_MESSAGES',f_intent_dm)
        .bind('POST_UPDATE',function(e) {
            $('#statusUpdate').focus();
        });
    
    $(document).bind('RETWEET_ITEM',function(e,item) {
        form.trigger('INTENT_RETWEET',[item]);
    });

    $(document).bind('REPLY_TO_ITEM',function(e,item) {
        if (item.recipient) form.trigger('INTENT_DM',[item]);
        if (item.user) form.trigger('INTENT_REPLY',[item]);
        
    });

    // Handle when when a status is updated
    $(document).bind('STATUS_UPDATED',function(e,tweet) {
        GLOBALS.timeline.updateStack([tweet]); 
        if ($(this).data('CURRENT_STACK') == 'timeline') {
            if ($('#stackDisplay').data('CURRENT_PAGE') == 1) 
                $('#stackDisplay').trigger('RENDER_PAGE',1);
        } 
    }); 

    // Handle when a new message is posted
    $(document).bind('DIRECTMESSAGE_SENT',function(e,message) {
        GLOBALS.sentMessages.updateStack([message]); 
        if ($(this).data('CURRENT_STACK') == 'sentMessages') {
            $('#stackDisplay').trigger('RENDER_PAGE',1);
        } 
    });
    
    // Handle Searching
    $(document).bind('SEARCH_SHOW',function(e,search) {
        form.data('FORM_MODE','SEARCH')
            .data('SEARCH_OBJ',search)
            .trigger('INTENT_SEARCH',[search]);
    });
    
    // Handle Status updates as events ... 
    $(document).bind('SUBMIT_UPDATE',function(e,postData,callback) {
        FORM_AJAX.add(
        {
            type:       'POST',
            url:        '/webservice/update.php',
            data:       postData,
            dataType:   'json',
            error:      form_ERROR,
            success:    function(response,statusText) {
                $(document).trigger('STATUS_UPDATED',[response]); 
                if (typeof callback == 'function')
                    callback(response);
            }    
        });    

    }); 
});

init.done('id.formStatusUpdate.js DONE',function() {
    $('#formStatusUpdate').trigger('RESET');
});


/*
 * form handling functions for status updates 
 *
 */ 
function form_ERROR(XMLHttpRequest, textStatus, errorThrown) {
    var form = $('#formStatusUpdate'); 
    $(document).trigger('FORM_UI_MESSAGE_POST',['error',XMLHttpRequest.responseText]); 
    $('#form-working').fadeOut();
}
function handleStatusUpdate() {
    // validate the data
    var form = $('#formStatusUpdate');
    var text = $('#statusUpdate').val().trim().stripNewlines(); 
    
    $(document).trigger('FORM_UI_MESSAGE_CLEAR');    
    if (text.length == 0) {
        $(document).trigger('FORM_UI_MESSAGE_POST',['error',"Can't post that you're doing nothing.",true]); 
        return false; 
    }
    
    var data = { 'text' : text, 
                 'rid': $('#replyToId').val() 
               }
    
    $(document).trigger('SUBMIT_UPDATE',[data,
    function(response) {
        $(document).trigger('FORM_UI_MESSAGE_POST',['success',"Your status has been updated",true])
        $('#formStatusUpdate').trigger('RESET');    
    }]);
}

function handleMessageSend() {
    var form = $('#formStatusUpdate');
    var text = $('#statusUpdate').val().trim().stripNewlines(); 
    var recipient = $('#recipient').val().trim().stripNewlines(); 

    if (recipient.length == 0) {
        $(document).trigger('FORM_UI_MESSAGE_POST',['error',"A recipient is required."]); 
        return false; 
    }
    
    if (recipient.search(/[^a-z0-9_]/i) > -1) {
        $(document).trigger('FORM_UI_MESSAGE_POST',['error',"The recipient's name is not valid."]); 
        return false;         
    }
    if (text.length == 0) {
        $(document).trigger('FORM_UI_MESSAGE_POST',['error',"Can't send the recipient a blank message."]); 
        return false;
    }
    
    // strip out the DM type syntax .. 
    text = text.replace(/^dm ([A-Za-z0-9_]+) /i,'');
    FORM_AJAX.add(
    {
        type:       'POST',
        url:        '/webservice/sendmessage.php',
        data:       { 'text' : text, 'recipient_name':recipient },
        dataType:   'json',
        error:      form_ERROR,
        success:    function(response,statusText) {
            $(document)
                .trigger('DIRECTMESSAGE_SENT',[response])
                .trigger('FORM_UI_MESSAGE_POST',['success','Your message has been sent',true]);
            $('#formStatusUpdate').trigger('RESET');
        }
    });
}
function handleSearch() {
    var q = $('#statusUpdate').val().trim();
    if (q.length == 0) {
        $(document).trigger('FORM_UI_MESSAGE_POST',['error','Nothing to search',true]);
        return false;
    }
    // clone the search object, except for the query
    var sO = $('#formStatusUpdate').data('SEARCH_OBJ');
    var search  = new TwitterSearch(sO.saveName,q,sO.id);
    search.page(1,function(search) {
        $(document).trigger('SEARCH_SHOW',[search]);
    });
}

}