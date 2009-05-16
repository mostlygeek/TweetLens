if (init.add) {

init.add('class.inlineReply.js INIT',function() {

    $(document).bind('ATTACH_INLINE_REPLY',function(e,domEl) {
        var i = $(domEl).data('data');
        if (i == undefined || i.id == undefined) return;
        
        // figure out what kind of item this is.
        var u = (i.from_user != undefined) ? i.from_user : i.user.screen_name;
        var t = '@'+u;

        var m = 139-t.length;
        if ($('#inlineReply').length > 0) return false; // one already exists
        var html = '<div id="inlineReply"><input type="hidden" id="replyId" value="'+i.id+'">'
            + t+' <input type="text" maxlength="'+m+'" class="span-10" id="replyText"> '
            + '<button class="inline positive" id="inlineSubmit">Reply</button> '
            + '<button class="inline negative" id="inlineCancel">Cancel</button></div>';
        $(domEl).after(html);
        $('#replyText').focus();
    });
    
    $(document).bind('REMOVE_INLINE_REPLIES',function(e,force) {
        if ($('#replyText').length == 0) return; 
        var tl = $('#replyText').val().trim().length;
        if (force || tl == 0 || (tl > 0 && confirm('Cancel reply'))) {
            $('#inlineReply').remove();
        }
    });
    $('#inlineCancel').live('click',function(e) {
        e.stopImmediatePropagation();
        $(document).trigger('REMOVE_INLINE_REPLIES');
    });     
    $('#replyText').live('keyup',function(e) {
        if (e.keyCode == 27) // esc
            $(document).trigger('REMOVE_INLINE_REPLIES');
    });
    $('#inlineSubmit').live('click',function(e) {
        $('#inlineCancel').remove();
        $(this).html('Sending...');
        var i = $('.stackItemSelected').data('data');

        var u = (i.from_user != undefined) ? i.from_user : i.user.screen_name;
        var t = '@'+u;
        
        var rt = $('#replyText').val().trim();
        if (rt.length == 0) {
            alert('Nothing to post');
            return; 
        }
        t +=' '+rt; // form the text. 
        var r = $('#replyId').val();
        var data = { text: t, rid: r };
        $(document).trigger('SUBMIT_UPDATE',[data,function(response) {
            $(document).trigger('REMOVE_INLINE_REPLIES',[true]);
            $('<div class="inline success"></div>')
                .html('<p class="inline">Success! Reply sent to '+u+'</p>')
                .insertAfter('.stackItemSelected')
                .fadeIn()
                .animate({opacity: 1.0},2000)
                .fadeOut('fast',function() { $(this).remove() });
        }]);
    });
}); 

}