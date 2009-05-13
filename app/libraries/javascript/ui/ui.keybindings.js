if (init) {
    init.add('ui-keyboard-bindings',function() {
        var doc = $(document); 
        var defaultOptions = {type:'keypress',disable_in_input:true};
        shortcut.add('c',function(){ // none
                $('.stackItemSelected').trigger('RATE_NONE');
            },defaultOptions);
        shortcut.add('f',function(){ 
                $('.stackItemSelected').trigger('FAVORITE');
            } ,defaultOptions);    
        shortcut.add('j',function(){ 
                doc.trigger('NEXT_ITEM');
            } ,defaultOptions);
        shortcut.add('k',function(){ 
                doc.trigger('PREV_ITEM');
            } ,defaultOptions);
        shortcut.add('n',function(){ 
                doc.trigger('GOTO_NEXT_PAGE');
            } ,defaultOptions);
        shortcut.add('o',function(){ 
                doc.trigger('TOGGLE_SHOW');
            } ,defaultOptions);        
        shortcut.add('p',function(){ 
                doc.trigger('GOTO_PREV_PAGE');
            } ,defaultOptions);
        shortcut.add('r',function(){ 
                $('.stackItemSelected').trigger('INLINE_REPLY');
            } ,defaultOptions);
        shortcut.add('s',function(){
            $('a[href="#search"]').click();
            },defaultOptions);
        shortcut.add('t',function() {
                $('.stackItemSelected').trigger('RETWEET');
            }, defaultOptions);
        shortcut.add('u',function() {
            $('a[href=#timeline]').click();
            } ,defaultOptions);
        shortcut.add('z',function(){ // like
                $('.stackItemSelected').trigger('RATE_LIKE');
            },defaultOptions);

        shortcut.add(';',function(){ 
                $('.stackItemSelected').trigger('READ');
            } ,defaultOptions);
        shortcut.add('space',function(){
                $('.stackItemSelected').trigger('HIDE');
                doc.trigger('NEXT_ITEM');
            },defaultOptions);

        // older tweets
        shortcut.add('left',function(){ 
                doc.trigger('GOTO_PREV_PAGE');
            } ,defaultOptions);    
        // newer tweets
        shortcut.add('right',function(){ 
                doc.trigger('GOTO_NEXT_PAGE');
            } ,defaultOptions);
    }); 
}