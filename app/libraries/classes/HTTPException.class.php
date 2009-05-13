<?php

class HTTPException extends Exception {
    var $httpCode = '200'; 
    
    function __construct($httpCode,$message='') {
        $this->httpCode = (string) $httpCode;
        parent::__construct((string) $message, $httpCode);
    }    
    
    function getHTTPReason() {
        $code = $this->httpCode;
        switch ($code) {
           case '400': 
               $msg = "$code Bad Request"; 
               break; 
           case '401': 
               $msg = "$code Not Authorized"; 
               break; 
           case '403': 
               $msg = "$code Forbidden"; 
               break; 
           case '404': 
               $msg = "$code Not Found"; 
               break; 
           case '500': 
               $msg = "$code Internal Server Error"; 
               break; 
           case '502': 
               $msg = "$code Bad Gateway"; 
               break; 
           case '503': 
               $msg = "$code Service Unavailable"; 
               break; 
           default: 
               $msg = "$code Unknown"; 
        }
        return $msg;
    }
}