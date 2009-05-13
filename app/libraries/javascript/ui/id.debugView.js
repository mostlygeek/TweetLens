if(init.add) {
    init.add('id.debugView.js-INIT',function() {
        $(document).bind('TOGGLE_DEBUG',function(e){
            $('#debugView').slideToggle(100);
        });
    });
}