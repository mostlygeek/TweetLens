<?php

class Session {
    
    static public function addAccount($user) {
        if (!is_object($user)) {
            throw new Exception('Supplied user is not an object'); 
        }
        $_SESSION['twitter.accounts'][$user->id] = $user;
        $_SESSION['twitter.accounts'][$user->screen_name] = $user;
    } 
    
    static public function updateUserStatus($status) {
        if (!isset($status->user->id))   
            throw new Exception('Supplied Status does not have a user id to use'); 
        
        $userid = $status->user->id; 
        $user = $_SESSION['twitter.accounts'][$userid];
        $user->status = $status; 
        $_SESSION['twitter.accounts'][$user->id] = $user;        
    }
    
    static public function GetAccount($uid) {
        return $_SESSION['twitter.accounts']["$uid"]; 
    }
    
    static public function GetFirstUser() {
        $temp = $_SESSION['twitter.accounts']; 
        return array_shift($temp);
    }
    
    static public function isLoggedIn() {
        return ( is_array($_SESSION['twitter.accounts']) && 
                 count($_SESSION['twitter.accounts']) > 1); 
    }
    
    
    
}