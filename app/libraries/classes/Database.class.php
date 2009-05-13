<?php
/*
Original Author: 
    # Author: ricocheting
    # Web: http://www.ricocheting.com/scripts/
    # Update: 2/2/2009
    # Version: 2.1
    # Copyright 2003 ricocheting.com

Hacked by me to throw exceptions 
*/

class Database {


var $server   = ""; //database server
var $user     = ""; //database login name
var $pass     = ""; //database login password
var $database = ""; //database name
var $pre      = ""; //table prefix


#######################
//internal info
var $record = array();

var $error = "";
var $errno = 0;

//table name affected by SQL query
var $field_table= "";

//number of rows affected by SQL query
var $affected_rows = 0;

var $link_id = 0;
var $query_id = 0;


#-#############################################
# desc: constructor
function Database($server, $user, $pass, $database, $pre=''){
    $this->server=$server;
    $this->user=$user;
    $this->pass=$pass;
    $this->database=$database;
    $this->pre=$pre;
}#-#constructor()


#-#############################################
# desc: connect and select database using vars above
# Param: $new_link can force connect() to open a new link, even if mysql_connect() was called before with the same parameters
function connect($new_link=false) {
    $this->link_id=@mysql_connect($this->server,$this->user,$this->pass,$new_link);

    if (!$this->link_id) {//open failed
        $this->oops("Could not connect to server: $this->server.");
        }

    if(!@mysql_select_db($this->database, $this->link_id)) {//no database
        $this->oops("Could not open database: $this->database.");
        }

}

function close() {
    if(!mysql_close($this->link_id)){
        $this->oops("Connection close failed.");
    }
}


function escape($string) {
    if(get_magic_quotes_gpc()) $string = stripslashes($string);
    return mysql_real_escape_string($string);
}


#-#############################################
# Desc: executes SQL query to an open connection
# Param: (MySQL query) to execute
# returns: (query_id) for fetching results etc
function query($sql) {
    
    if (!$this->link_id) {
        $this->connect();
    }
    
    // do query
    $this->query_id = @mysql_query($sql, $this->link_id);

    if (!$this->query_id) {
        $this->oops("MySQL Query fail: $sql");
    }
    
    $this->affected_rows = @mysql_affected_rows();

    return $this->query_id;
}#-#query()


#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (array) fetched record(s)
function fetch_array($query_id=-1) {
    // retrieve row
    if ($query_id!=-1) {
        $this->query_id=$query_id;
    }

    if (isset($this->query_id)) {
        $this->record = @mysql_fetch_assoc($this->query_id);
    }else{
        $this->oops("Invalid query_id: $this->query_id. Records could not be fetched.");
    }

    // unescape records
    if($this->record){
        $this->record=array_map("stripslashes", $this->record);
        //foreach($this->record as $key=>$val) {
        //    $this->record[$key]=stripslashes($val);
        //}
    }
    return $this->record;
}#-#fetch_array()


#-#############################################
# desc: returns all the results (not one row)
# param: (MySQL query) the query to run on server
# returns: assoc array of ALL fetched results
function fetch_all_array($sql) {
    $query_id = $this->query($sql);
    $out = array();

    while ($row = $this->fetch_array($query_id, $sql)){
        $out[] = $row;
    }

    $this->free_result($query_id);
    return $out;
}#-#fetch_all_array()


#-#############################################
# desc: frees the resultset
# param: query_id for mysql run. if none specified, last used
function free_result($query_id=-1) {
    if ($query_id!=-1) {
        $this->query_id=$query_id;
    }
    if(!@mysql_free_result($this->query_id)) {
        $this->oops("Result ID: $this->query_id could not be freed.");
    }
}#-#free_result()


#-#############################################
# desc: does a query, fetches the first row only, frees resultset
# param: (MySQL query) the query to run on server
# returns: array of fetched results
function query_first($query_string) {
    $query_id = $this->query($query_string);
    $out = $this->fetch_array($query_id);
    $this->free_result($query_id);
    return $out;
}#-#query_first()


#-#############################################
# desc: does an update query with an array
# param: table (no prefix), assoc array with data (doesn't need escaped), where condition
# returns: (query_id) for fetching results etc
function query_update($table, $data, $where='1') {
    $q="UPDATE `".$this->pre.$table."` SET ";

    foreach($data as $key=>$val) {
        if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
        elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
        else $q.= "`$key`='".$this->escape($val)."', ";
    }

    $q = rtrim($q, ', ') . ' WHERE '.$where.';';

    return $this->query($q);
}#-#query_update()


#-#############################################
# desc: does an insert query with an array
# param: table (no prefix), assoc array with data
# returns: id of inserted record, false if error
function query_insert($table, $data) {
    $q="INSERT INTO `".$this->pre.$table."` ";
    $v=''; $n='';

    foreach($data as $key=>$val) {
        $n.="`$key`, ";
        if(strtolower($val)=='null') $v.="NULL, ";
        elseif(strtolower($val)=='now()') $v.="NOW(), ";
        else $v.= "'".$this->escape($val)."', ";
    }

    $q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

    if($this->query($q)){
        //$this->free_result();
        return mysql_insert_id();
    }
    else return false;

}#-#query_insert()

function query_replace($table, $data) {
    $q="REPLACE INTO `".$this->pre.$table."` ";
    $v=''; $n='';

    foreach($data as $key=>$val) {
        $n.="`$key`, ";
        if(strtolower($val)=='null') $v.="NULL, ";
        elseif(strtolower($val)=='now()') $v.="NOW(), ";
        else $v.= "'".$this->escape($val)."', ";
    }

    $q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

    if($this->query($q)){
        //$this->free_result();
        return mysql_insert_id();
    }
    else return false;

}#-#query_replace()


#-#############################################
# desc: throw an error message
# param: [optional] any custom error to display
function oops($method,$msg='') {

    if($this->link_id>0) {
        $this->error=mysql_error($this->link_id);
        $this->errno=mysql_errno($this->link_id);
    } elseif (!is_resource($this->link_id)) {
        $this->error = "Link ID not set. Did you connect()?";
    } else {
        $this->error=mysql_error();
        $this->errno=mysql_errno();    
    }
    
    throw new Exception("$method() - ".$this->error,$this->errno); 
    
}#-#oops()


}//CLASS Database
