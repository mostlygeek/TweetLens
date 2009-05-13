if (init.add) {

init.add ('id.searchId.js INIT',function() {

    $('#formStatusUpdate').bind('INTENT_SEARCH',function(e,search) {
            $('#searchId').val(search.id);
    });
}); 
}