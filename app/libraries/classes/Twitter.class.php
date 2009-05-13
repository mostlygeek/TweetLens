<?php

class Twitter {

	const DEBUG = false;
	const TWITTER_API_URL = 'http://twitter.com';
	const TWITTER_API_PORT = 80;
	const SOURCE = "tweetlens"; 
	
	protected $username = null; 
	protected $password = null;
	private $user = null; // user object from twitter
	
	public $lastCallInfo; 
	
	public function __construct($username = null, $password = null)
	{
	   if ($username != null && $password != null) {
	       $this->setCredentials($username,$password);
	   }
	}
	
	public function setCredentials($username,$password, $verify=false) {
	   $this->username = $username; 
	   $this->password = $password;
	   if ($verify) 
	       return $this->verifyCredentials();
	}

	public function getTwitterUsername() {
	   return $this->username; 
	}
	public function getTwitterPassword() {
	   return $this->password; 
	}
	public function getTwitterUser() {
	   if ($this->user == null) {
	       throw new Exception(__METHOD__.', twitter user does not exist. Probably did not call verifyCredentials()');
	   }
	   return $this->user; 
	}

	public function setTwitterUser($user) { // sets an authenticated user
        if (!is_object($user)) {
            throw new Exception('Invalid Twitter User Object');
        }
        if (!isset($user->username) || !isset($user->password)) {
            // add something in or oAuth later on.
            throw new Exception('User does not have a username/password');
        }
        $this->user = $user; 
        $this->username = $user->username; 
        $this->password = $user->password; 

	}

	public function getPublicTimeline() 
	{
	   $resp = $this->_call('statuses/public_timeline.json');
	   $resp = json_decode($resp); 
	   $resp = $this->_addUnixTimes($resp);
	   return $resp; 
	}
        	
	public function getFriendsTimeline($since = null, $sinceId = null, $count = null, $page = null)
	{
        // validate parameters
        if($since !== null && (int) $since <= 0) throw new TwitterException('Invalid timestamp for since.');
        if($sinceId !== null && (int) $sinceId <= 0) throw new TwitterException('Invalid value for sinceId.');
        if($count !== null && (int) $count > 200) throw new TwitterException('Count can\'t be larger then 200.');
        
        // build url
        $fields = array();
        if($since !== null) $fields['since'] = date('r', (int) $since);
        if($sinceId !== null) $fields['since_id'] = (int) $sinceId;
        if($count !== null) $fields['count'] = (int) $count;
        if($page !== null) $fields['page'] = (int) $page;
        
        $resp = $this->_call('statuses/friends_timeline.json',$fields,true); 
        $resp = json_decode($resp);

        if (!is_array($resp)) {
            return array(); 
        }

        $resp = $this->_addUnixTimes($resp);
        return $resp; 
	}

	public function getUserTimeline($id = null, $since = null, $sinceId = null, $count = null, $page = null)
	{
        // validate parameters
        if($since !== null && (int) $since <= 0) throw new TwitterException('Invalid timestamp for since.');
        if($sinceId !== null && (int) $sinceId <= 0) throw new TwitterException('Invalid value for sinceId.');
        if($count !== null && (int) $count > 200) throw new TwitterException('Count can\'t be larger then 200.');
        
        // build parameters
        $fields = array();
        if ($id !== null) $fields['id'] = $id;
        if($since !== null) $fields['since'] = date('r', (int) $since);
        if($sinceId !== null) $fields['since_id'] = (int) $sinceId;
        if($count !== null) $fields['count'] = (int) $count;
        if($page !== null) $fields['page'] = (int) $page;
        
        // build url
        $url = 'statuses/user_timeline.json';
        if($id !== null) $url = 'statuses/user_timeline/'. urlencode($id) .'.json';
        $resp = $this->_call($url,$fields,true); 
        $resp = json_decode($resp);
        
        if (!is_array($resp)) {
            return array(); 
        }
        
        $resp = $this->_addUnixTimes($resp);
        return $resp; 	
	}

	public function getStatus($id)
	{
		$id = (string) $id;
		$url = 'statuses/show/'. urlencode($id) .'.json';
		$resp = json_decode($this->_call($url));
		$resp = $this->_addUnixTimes($resp);
		return $resp; 
	
	}

	public function getReplies($since = null, $sinceId = null, $page = null)
	{
		// validate parameters
		if($since !== null && (int) $since <= 0) throw new TwitterException('Invalid timestamp for since.');
		if($sinceId !== null && (int) $sinceId <= 0) throw new TwitterException('Invalid value for sinceId.');

		// build parameters
		$fields = array();
		if($since !== null) $fields['since'] = date('r', (int) $since);
		if($sinceId !== null) $fields['since_id'] = (int) $sinceId;
		if($page !== null) $fields['page'] = (int) $page;

		// do the call
		$resp = json_decode($this->_call('statuses/replies.json', $fields, true, false));
		
		if (!is_array($resp)) {
		  return array(); 
		}
		
		$resp = $this->_addUnixTimes($resp);
		return $resp; 
	}

	public function getFriends($id = null, $page = null)
	{
		$fields = array();
		if($page !== null) $fields['page'] = (int) $page;

		// build url
		$url = 'statuses/friends.json';
		if($id !== null) $url = 'statuses/friends/'. urlencode($id) .'.json';

		// do the call
		$resp = json_decode($this->_call($url, $fields, true, false));
		if (!is_array($resp)) {
            return array(); 
		}
		$resp = $this->_addUnixTimes($resp);
	    return $resp;
	
	}

	public function getFollowers($id = null, $page = null)
	{
		$fields = array();
		if($page !== null) $fields['page'] = (int) $page;

		// build url
		$url = 'statuses/followers.json';
		if($id !== null) $url = 'statuses/followers/'. urlencode($id) .'.json';

		// do the call
		$resp = json_decode($this->_call($url, $fields, true, false));
		if (!is_array($resp)) {
            return array(); 
		}
		$resp = $this->_addUnixTimes($resp);
	    return $resp;
	   
	}

	public function getFriend($id = null, $email = null)
	{
		// validate parameters
		if($id === null && $email === null) throw new TwitterException('id or email should be specified.');

		// build parameters
		$fields = array();
		if($email !== null) $fields['email'] = (string) $email;

		// build url
		$url = 'users/show/'. urlencode($id) .'.json';
		if($email !== null) $url = 'users/show.json';
		
		$resp = json_decode($this->_call($url,$fields,true));
		return $resp;
	
	}

	public function getDirectMessages($since = null, $sinceId = null, $page = null)
	{
		// validate parameters
		if($since !== null && (int) $since <= 0) throw new TwitterException('Invalid timestamp for since.');
		if($sinceId !== null && (int) $sinceId <= 0) throw new TwitterException('Invalid value for sinceId.');

		// build parameters
		$fields = array();
		if($since !== null) $fields['since'] = date('r', (int) $since);
		if($sinceId !== null) $fields['since_id'] = (int) $sinceId;
		if($page !== null) $fields['page'] = (int) $page;

		// do the call
		$resp = json_decode($this->_call('direct_messages.json', $fields, true));
		if (!is_array($resp)) {
            return array(); 
		}
		$resp = $this->_addUnixTimes($resp);
        return $resp;	
	}

	public function getSentDirectMessages($since = null, $sinceId = null, $page = null)
	{
		// validate parameters
		if($since !== null && (int) $since <= 0) throw new TwitterException('Invalid timestamp for since.');
		if($sinceId !== null && (int) $sinceId <= 0) throw new TwitterException('Invalid value for sinceId.');

		// build parameters
		$fields = array();
		if($since !== null) $fields['since'] = date('r', (int) $since);
		if($sinceId !== null) $fields['since_id'] = (int) $sinceId;
		if($page !== null) $fields['page'] = (int) $page;

		// do the call
		$resp = json_decode($this->_call('direct_messages/sent.json', $fields, true));
		if (!is_array($resp)) {
            return array(); 
		}
		$resp = $this->_addUnixTimes($resp);
		return $resp;
	}
	public function getRateLimitStatus()
	{
		$resp = json_decode($this->_call('account/rate_limit_status.json', NULL, true));
		return $resp;
	}

	public function getFavorites($id = null, $page = null)
	{
		// build parameters
		$fields = array();
		if($page !== null) $fields['page'] = (int) $page;

		$url = 'favorites.json';
		if($id !== null) $url = 'favorites/'. urlencode($id) .'.json';
		
		$resp = json_decode($this->_call($url,$fields,true)); 
		if (!is_array($resp)) {
            return array(); 
		}
        $resp = $this->_addUnixTimes($resp); 
        return $resp; 	   
	}
	

	public function updateStatus($status, $replyId = null)
	{
        $fields = array(); 
        $fields['status'] = $status; 
        if ($replyId !== null)
            $fields['in_reply_to_status_id'] = $replyId; 
        
        $resp = json_decode($this->_call('statuses/update.json',$fields,true,true));
        $resp = $this->_addUnixTimes($resp);
        return $resp; 
	}

	public function deleteStatus($id)
	{
	}

	public function sendDirectMessage($user, $text)
	{
		// redefine
		$fields = array(); 
		$fields['user'] = (string) $user; 
		$fields['text'] = (string) $text; 
		$resp = json_decode($this->_call('direct_messages/new.json',$fields,true,true)); 
        $resp = $this->_addUnixTimes($resp);
        return $resp; 		
	}

	public function deleteDirectMessage($id)
	{
	}

	public function createFriendship($id, $follow = true)
	{
	}

	public function deleteFriendship($id)
	{
	}

	public function existsFriendship($id, $friendId)
	{
	}

	/**
	 * Verifies your credentials
	 * Use this method to test if supplied user credentials are valid.
	 *
	 * @return	bool
	 */
	public function verifyCredentials()
	{
        // do the call
        $resp = json_decode($this->_call('account/verify_credentials.json',NULL,true));
        if (isset($resp->error)) {
            return false; 
        } else {
            $this->user = $resp;
            $this->user->username = $this->username; 
            $this->user->password = $this->password; 
            return $this->user; 
        }
	}

	public function endSession()
	{
	}

	public function updateDeliveryDevice($device)
	{
	}

	public function updateProfile($name = null, $email = null, $url = null, $location = null, $description = null)
	{
	}

	public function updateProfileColors($backgroundColor = null, $textColor = null, $linkColor = null, $sidebarBackgroundColor = null, $sidebarBorderColor = null)
	{
	}

	public function updateProfileImage($image)
	{
	}

	public function updateProfileBackgroundImage($image)
	{
	}

	public function createFavorite($id)
	{
        return json_decode($this->_call("favorites/create/$id.json",null,true,true));
	}

	public function deleteFavorite($id)
	{
        return json_decode($this->_call("favorites/destroy/$id.json",null,true,true));
	}

	public function follow($id)
	{
	}

	public function unfollow($id)
	{
	}

	public function createBlock($id)
	{
	}

	public function deleteBlock($id)
	{
	}

	public function test()
	{
		// make the call
		$resp = json_decode($this->_call('help/test.json'));
		return $resp;
	}
    
    private function _call($url, $fields=array(),$auth=false,$isPost=false) 
    {
        $url = self::TWITTER_API_URL.'/'.$url; 

        if (!empty($fields) && !$isPost) { // create a GET request w/ params
            $urlparts = parse_url($url);
            foreach ($fields as $key => $value) { // add the get vars.
                if (strlen($urlparts['query']) > 2 && substr($urlparts['query'],-1) != '&')
                    $urlparts['query'].='&'; 
                $urlparts['query'] .= urlencode($key).'='.urlencode($value); 
            }
            $url = $urlparts['scheme'].'://'; 
            if ($urlparts['user'])
                $url .= $urlparts['user'].':'.$urlparts['pass'].'@'; 
    
            $url .= $urlparts['host'].$urlparts['path'].'?'.$urlparts['query'];
            /*
            if ($urlparts['fragment'])
                $url .= '#'.$urlparts['fragment'];
            */
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        // fetch the user credentials
        if ($auth) {
            if ($this->username == null || $this->password == null) {
                throw new Exception(__METHOD__.' Twitter Credentials Missing');
            }
            curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        }
        if ($isPost) {
            $fields['source'] = self::SOURCE;
            curl_setopt($ch, CURLOPT_POST, true);
            
            // make a query string to prevent curl from trying to upload @reply 
			$var = ''; 
			foreach($fields as $key => $value) $var .= '&'. $key .'='. urlencode($value);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $var);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        }
        
        $response   = curl_exec($ch);
        $headers    = curl_getinfo($ch);
        $errorNum   = curl_errno($ch); 
        $errorMsg   = curl_error($ch);
        
        $this->lastCallInfo = $headers; 
        $this->errorNum = $errorNum; 
        $this->errorMsg = $errorMsg; 
        curl_close($ch);
        
        if ($headers['http_code'] >= 400) {
            $response = json_decode($response); 
            throw new TwitterException($headers['http_code'],
                $url,
                $response->error);
        }
        
        return $response; 
    }
    
    private function _addUnixTimes($data) {
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                if (isset($item->created_at)) { // any status info. 
                    $data[$key]->created_at_unixtime = (int) strtotime($item->created_at);
                }
                
                if (isset($item->status->created_at)) { // getFriends
                    $data[$key]->status->created_at_unixtime = (int) strtotime($item->status->created_at);
                }
            }
        } 
        
        if (is_object($data)) {
            if (isset($data->created_at)) {
                $data->created_at_unixtime = (int) strtotime($data->created_at);
            }
            if (isset($data->status->created_at)) { // getFriends
                $data->status->created_at_unixtime = (int) strtotime($data->status->created_at);
            }
        }
        
        return $data;
    }
    
}