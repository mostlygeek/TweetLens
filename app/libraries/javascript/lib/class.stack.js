function Stack(data) {
    this.stack = [];
    
    if (arguments.length > 0)
        this.init(data);
}

Stack.prototype.init = function(data) {
    this.setStack(data); 
}
Stack.prototype.getStack = function() {
    return this.stack; 
}
Stack.prototype.setStack = function(data) {
    this.stack = data; 
}

Stack.prototype.getPage = function(page) {
    // 20 item / page
    if (arguments.length < 1) 
        return [];        
    var start = (page-1)*20; 
    var end = (page*20);
    if (this.stack[start] == undefined) {
        return []; 
    }        
    return this.stack.slice(start,end);
}

Stack.prototype.updateStack = function(delta) {
    var newData = this.stack.concat(delta); // all the items
    var temp = new Object(); // for deduping
    var newCount = 0; // number of new items added
    
    for (var i in this.stack)
        temp[this.stack[i].id] = true; // a list of current ids 
    for (var i in delta) {
        if (delta[i].id == undefined) continue;
        if (temp[delta[i].id] != undefined) continue; 
        this.stack.push(delta[i]); // add it 
        newCount++;
    }
    
    // sort and trim to 200 items
    this.stack.sort(function(a,b){ return (b.id-a.id)}).slice(0,200); 
    return newCount; 
}

Stack.prototype.idSearch = function(id) {
  // Binary search for the id 
  // based on: http://www.dweebd.com/javascript/binary-search-an-array-in-javascript/
  var low = 0, high = this.stack.length - 1, i, comparison;
  while (low <= high) {
    i = parseInt((low + high) / 2, 10);
    comparison = (id - this.stack[i].id); 
    if (comparison < 0) { low = i + 1; continue; };
    if (comparison > 0) { high = i - 1; continue; };
    return i; // the item
  }
  return null;
};
Stack.prototype.getById = function(id) {
    var i = this.idSearch(id);
    if (i != null) 
        return this.stack[i];
    return null; 
}

// Shared functions used by children 
Stack.ajaxError = function(XHR, textStatus, errorThrown) {
    /* generic function for handling ajax errors */
    if (XHR.status == '401') {
        window.location = '/auth/logout.php'; 
        return; 
    }
    $(document).trigger('STACK_AJAX_ERROR',[XHR]);
}