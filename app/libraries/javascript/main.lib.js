// so we don't blow up production code or missing firebug.
if (typeof(console) == 'undefined') console = {log:function(){}}; 
function debug() {
    if (PRODUCTION_MODE) return true;    
    console.log(arguments);
}
function init() {

    init.initAll(); 
    
    // show timeline by default. 
    $(document)
        .data('CURRENT_ACCOUNT','') // place holder
        .trigger('SHOW_TIMELINE');
    
    // Complete the document 
    init.initDone();    
}

/* These provide the ability for JS libraries 
   to add their initialization code to a stack to be started when the 
   DOM is ready */ 
init.add = function( name, fn ) {
    if (!init.initStack) {
        init.initStack = {}; 
    }
    if (typeof(fn) == "function")
        init.initStack[name] = fn; // add the function
    else 
        alert("init.add, function not provided");
}
init.done = function( name, fn ) { 
    // when all the inits are done. Run this. 
    if (!init.doneStack)
        init.doneStack = {}; 
        
    if (typeof(fn) == "function")
        init.doneStack[name] = fn; // add the function
    else 
        alert("init.done, function not provided");
}
init.initAll = function() {
    for (var name in init.initStack) {
        debug('Initializing JS Library: '+name);
        init.initStack[name](); 
    }
}
init.initDone = function() {
    for (var name in init.doneStack) {
        debug('Completion Function: '+name);
        init.doneStack[name](); 
    }
}