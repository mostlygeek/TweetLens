<?php

// use memcached for saving sessions 
ini_set('session.save_handler','memcache'); 
ini_set('session.save_path','127.0.0.1:11211'); 

session_start(); 

function isLoggedIn() {
    return is_object($_SESSION['tw_user']); 
}

function setCredentials($user, $username, $password ) {
    $_SESSION['tw_uid'] = $user->id; 
    $_SESSION['tw_user'] = $user; 
    $_SESSION['tw_username'] = $username; 
    $_SESSION['tw_password'] = $password; 
}

function sessGetUser() {
    return $_SESSION['tw_user']; 
}
function sessGetTwitterUsername() {
    return $_SESSION['tw_username']; 
}

function sessGetTwitterPassword() {
    return $_SESSION['tw_password']; 
}
