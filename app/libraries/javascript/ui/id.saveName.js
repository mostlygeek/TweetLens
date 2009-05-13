if (init.add) {

init.add('id.saveName.js',function() {

    $('#formStatusUpdate').bind('INTENT_SEARCH',function(e,search) {
        $('#saveName').val(search.saveName);
    }); 
}); 

}