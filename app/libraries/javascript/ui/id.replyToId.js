if (init.add)
    init.add('id.replyToId.js INIT',function() {
        var resetReplyId = function(e) {
            $('#replyToId').val(''); 
        }
        
        $('#formStatusUpdate').bind('INTENT_RETWEET',resetReplyId)
            .bind('INTENT_DM',resetReplyId)
            .bind('INTENT_UPDATE',resetReplyId)
            .bind('INTENT_REPLY',function(e,item) {
                if (item.id == undefined) return;             
                $('#replyToId').val(item.id);
            }); 
    }); 