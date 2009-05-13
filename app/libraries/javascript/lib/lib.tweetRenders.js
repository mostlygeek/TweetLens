// Tweet Rendering Functions used by the Timeline and Replies classes
function renderItem(item) {
    // decides how to draw something.. .
    if (item.flags == undefined)
        item.flags = {};
    
    if (item.flags.SHOW == undefined) 
        item.flags.SHOW = '1'; 
        
    if (item.recipient != undefined) { // a message type item
        return (item.flags.SHOW == '1') ? 
            renderItem.messageShow(item) :
            renderItem.messageHide(item);
    }
    
    if (item.user != undefined) {
        return (item.flags.SHOW == '1') ?
            renderItem.statusShow(item) :
            renderItem.statusHide(item); 
    }
    
    if (item.from_user != undefined) {
        return (item.flags.SHOW == '1') ? 
            renderItem.resultShow(item) : 
            renderItem.resultHide(item);
    }
    return '??? ... can not figure out how to render this item';
}

renderItem.statusShow = function(t) {
    var u = t.user; // the user
    var text = t.text;
    
    // change links into real ones
    text = linkUrl(text);
    text = linkUsername(text);
    
    // link @username

    // stip 3 chars off the end and link it to the id, like on twitter.com
    if (t.truncated == 1) { 
        var newText = text.substring(0,t.text.length-4) 
            + ' <a href="#show-truncated" alt="view full tweet" class="show-truncated">...</a>'        
        text = newText; 
    }       
    var postTime = formatTime(t.created_at_unixtime);
    var inReply = '';
    var source = t.source.replace('<a','<a target="_blank"');
    
    if (t.in_reply_to_status_id != null)
        inReply = ' in reply to <a href="#in-reply" class="in-reply">'+t.in_reply_to_screen_name+'</a>';
    var html = '<img src="'+u.profile_image_url+'" width="48" height="48" alt="photo" class="profilepic">'
              +'<p class="stackText"><a href="#'+u.screen_name+'" class="twitterScreenName">'+u.screen_name+'</a> '+text
             +'<br><span class="itemInfo small quiet">'+postTime+' from '+source+inReply+'</span></p>';
    return html; 
};

renderItem.statusHide = function(t) {
    var html = ""; 
    var u = t.user; // the user
    
    var postTime = formatTime(t.created_at_unixtime);
    var text = t.text.substring(0,60)+'...';

    html = '<p class="stackText"><a href="#'+u.screen_name+'" class="twitterScreenName">'+u.screen_name+'</a> '+text
        +'<span class="itemInfo small quiet">'+postTime+' from '+t.source+'</span></p>';

    return html;
}; 

renderItem.messageShow = function(m) {
    var u = (m.sender.id == GLOBALS.currentAccount.id) ? 
        u = m.recipient : m.sender;
    
    var text = m.text;
    
    // change links into real ones
    text = linkUrl(text);
    text = linkUsername(text);
    
    // link @username

    var postTime = formatTime(m.created_at_unixtime);
    
    var html = '<img src="'+u.profile_image_url+'" width="48" height="48" alt="photo" class="profilepic">'
              + '<ul class="itemOptions">'
              + '<li><a href="#readClose">Close</a></li>'
              + '<li><a href="#reply"><span class="hotkey">R</span>eply</a></li></ul>'    
              +'<p class="stackText"><a href="#'+u.screen_name+'" class="twitterScreenName" target="_blank">'+u.screen_name+'</a> '+text
              +'<br><span class="itemInfo small quiet">'+postTime+'</span></p>';
    return html; 

}
renderItem.messageHide = function(m) {
    var html = ""; 
    var u = (m.sender.id == GLOBALS.currentAccount.id) ? 
        u = m.recipient : m.sender;
    
    var postTime = formatTime(m.created_at_unixtime);
    var text = m.text.substring(0,60)+'...';

    html = '<p class="stackText"><a href="#'+u.screen_name+'" class="twitterScreenName" target="_blank">'+u.screen_name+'</a> '+text
        +'<span class="itemInfo small quiet">'+postTime+'</span></p>';

    return html;

}
renderItem.resultShow = function(r) {
    var text = linkUsername(linkUrl(r.text)); 
    var postTime = formatTime(Date.parse(r.created_at)/1000);
    var source = r.source
        .replace(/&quot;/g,'"') 
        .replace(/&lt;/g,'<')
        .replace(/&gt;/g,'>')
        .replace('<a','<a target="_blank"');
    var html = '<img src="'+r.profile_image_url+'" width="48" height="48" alt="photo" class="profilepic">'
              +'<p class="stackText"><a href="#'+r.from_user+'" class="twitterScreenName">'+r.from_user+'</a> '+text
              +'<br><span class="itemInfo small quiet">'+postTime+' from '+source+'</span></p>';
    return html; 
    
}
renderItem.resultHide = function(i) {
    return 'hidden result';
}

function linkUsername(newText) {
    return newText.replace(/@([A-Za-z0-9_]{1,15})/gim,'@<a href="#$1" class="twitterScreenName">$1</a>');
}; 

function linkUrl(newText) {
    return newText.replace(/(\w+):\/\/[\S]+(\b|$)/gim,'<a href="$&" target="_blank" class="urlLink">$&</a>');
}