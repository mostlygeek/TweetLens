function formatTime(tweetTime) {
    var now  = new Date().getTime()/1000; 
    var diff = Math.round(now - tweetTime); // in seconds
    var timeStr; 
    
    if ( (diff/3600) > 48) {
        timeStr = "About "+parseInt(diff/3600/24)+" days ago"; 
    } else if ( (diff/3600) > 24) {
        timeStr = "more than 1 day ago"; 
    } else if ( (diff/3600) > 12) {
        timeStr = "about 12 hours ago"; 
    } else if ( (diff/3600) > 6) {
        timeStr = "about 6 hours ago"; 
    } else if ( (diff/3600) >= 1) {
        timeStr = "about 1 hour ago"; 
    } else if ((diff/60) > 1) {
        timeStr = Math.round(diff/60) + ' minutes ago';
    } else if (diff > 0) {
        timeStr = diff + ' seconds ago';
    } else {
        timeStr = 'a second ago';
    }
    
    return timeStr; 
}
