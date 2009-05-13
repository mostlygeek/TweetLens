function TwitterSearch(n,q,id) {
    
    this.setQuery(q);
    this.setSaveName(n);
    this.id = id || ''; 

    // data from the last search result
    this.searchRun = false; 
    this.response = {}; 
    this.results = {};
}
TwitterSearch.prototype.setSaveName = function(n) {
    this.saveName = (n == undefined) ? '' : n.trim();
    return false;
}
TwitterSearch.prototype.setQuery = function(q) {
    this.query = (q == undefined) ? '' : q.trim();
    return false; 
    //this.query = (q == undefined) ? '' : q.trim();
}

TwitterSearch.prototype.reset = function() {
    this.searchRun = false; 
    this.response = {};
    this.results = {}; 
}

TwitterSearch.prototype.page = function(page,callback) { // get specific page of search results
    // fetch from ajax, parse and render the results 
    page = parseInt(page) || 1;
    var query = encodeURIComponent(this.query);
    var surl = 'http://search.twitter.com/search.json?rpp=20&page='+page+'&q='+query;
    debug('Search: '+surl);
    var me = this;
    $.ajax({
        dataType: 'jsonp',
        type: 'GET',
        url : surl,
        success: function (json,textStatus) {
            // set the data
            me.results = json.results;
            me.response = json;
            me.searchRun = true;
            if (typeof callback == 'function')
                callback(me);
        },
        error : function (XMLHttpRequest, textStatus, errorThrown) {
            debug('Twitter search error');
        }
    });
}
TwitterSearch.prototype.delete = function(callback) {
    var me = this; 
    $.ajax( {
        dataType: 'text',
        type    : 'POST',
        data    : {'searchId': this.id, 't':'del'},
        url     : '/webservice/searches.php',
        success : function(text,textStatus) {
            if (typeof callback == 'function') 
                callback(me);
        },
        error : function (XMLHttpRequest, textStatus, errorThrown) {
            debug('Unable to delete saved search');
        }
    });
};

TwitterSearch.prototype.save = function(callback) {
    var me = this; 
    $.ajax( {
        dataType: 'json',
        type    : 'POST',
        data    : {'t':'save',
                   'searchId': this.id, 
                   'saveName': this.saveName, 
                   'query'   : this.query
                   },
        url     : '/webservice/searches.php',
        success : function(json,textStatus) {
            me.id = json.id; 
            me.setSaveName(json.saveName); 
            me.setQuery(json.query);
            if (typeof callback == 'function') 
                callback(me);
        },
        error : function (XMLHttpRequest, textStatus, errorThrown) {
            debug('Unable to Save Search');
        }
    });
}

TwitterSearch.get = function(callback) {
    debug('Fetching Saved Searches');
    $.ajax( {
        dataType: 'json',
        type    : 'GET',
        url     : '/webservice/searches.php?t=get',
        success : function(json,textStatus) {
            if (typeof callback == 'function')
                callback(json);
        },
        error : function (XMLHttpRequest, textStatus, errorThrown) {
            debug('Unable to fetch saved searches');
        }

    });

}