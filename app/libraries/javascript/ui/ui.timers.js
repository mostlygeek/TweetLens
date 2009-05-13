if (init.add) {

init.add('ui.timers.js INIT',function() {
    
    // add timers in here 
    
}); 

init.done('ui.timers.js DONE',function() {
    var diff = (new Date().getTime()/1000)
        - GLOBALS.timeline.stack[0].created_at_unixtime;
    if (diff > 600) GLOBALS.timeline.fetchLatest();
});

}