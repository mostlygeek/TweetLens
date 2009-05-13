<?php

/**
 * Twitter Exception class
 *
 * @author			Tijs Verkoyen <php-twitter@verkoyen.eu>
 */

class TwitterException extends Exception
{
    protected $twitterMessage = ''; 
    protected $twitterCode = 0;
    protected $requestUrl = ''; 
    
	public function __construct($code,$request,$message)
	{
	   $this->twitterMessage = $message; 
	   $this->twitterCode = $code; 
	   $this->requestUrl = $request; 
	   
	   // FROM: http://apiwiki.twitter.com/REST+API+Documentation#HTTPStatusCodes
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
	   $msg .= ": $message";
	   
		// call parent
		parent::__construct((string) $msg, $code);
	}
	
	function getTwitterMessage() {
	   return $this->twitterMessage; 
	}
	function getTwitterCode() {
	   return $this->twitterCode; 
	}
	function getRequestUrl() {
	   return $this->requestUrl; 
	}
}