// some custom string handling functions used throughout the application
var trim = function (str) {
    return str.trim; 
}
var stripNewlines = function(str) {
    return str.stripNewLines; 
}

String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g,""); 
}
String.prototype.stripNewlines = function() {
    return this.replace(/[\n\r]/g,"");
}
String.prototype.squashSpaces = function() {
    return this.replace(/\s+/g," "); 
}