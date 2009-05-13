<?php
/*
 Notes about Design: 
   - provides Twitter REST API call caching 
   - it is a simple layer on top. 
 */ 

class TwitterCache extends Twitter 
{
    // Cache Age Defaults in seconds
    protected $mc = NULL;
    public $useCache = true;
    
    public $cacheHit = false; // the last function was a cache hit
    public $lastMethod;
    public $lastCacheKey;
    public $lastCacheExpire;
    
    // Cache Expiration times (in seconds)
    private $cacheExpires = array (
        'getFeatured'          => 20, 
        'getPublicTimeline'    => 60, 
        'getUserTimeline'      => 60, 
        'getFriendsTimeline'   => 30,  
        'showStatus'           => 84600, 
        'getReplies'           => 60, 
        'getFriends'           => 60,
        'getFollowers'         => 120,
        'showUser'             => 60,
        'getMessages'          => 60,
        'getSentMessages'      => 60,
        'getArchive'           => 60,
        'getFavorites'         => 60,
        'getRateLimitStatus'   => 5,
        '!default!'            => 60
    ); 

    
    function __construct($username = null, $password = null) {
        if (!is_null($username) && !is_null($password)) {
            $this->setCredentials($username,$password,true); 
        }
        $this->mc = getMemcacheObject(); 
        if(get_class($this->mc) != 'Memcache') {
            throw new Exception(__METHOD__." Unable to get Memcache Object");
        }
    }
    
	/** MEMCACHE METHODS **/
	public function enableCache() {
	   $this->useCache = true; 
	}
	public function disableCache() {
	   $this->useCache = false; 
	}
	function mcGet($key) {
        $this->cacheHit = false; 
        $this->lastCacheKey = ''; 
        
        if (!$this->useCache)
            return false; 

        $val = $this->mc->get($key);
        
        if ($val !== false) {
            $this->cacheHit = true; 
            $this->lastCacheKey = $key;
        }
        
        return $val; 
	}

    function mcSet($key,$val,$expire) {
        $this->cacheHit = false; 
        if (!$this->useCache) // cache is disabled, don't cache anything
            return false;
	    $this->lastCacheExpire = $expire; 
        $this->mc->delete($key); // to avoid any collisions
        $this->mc->set($key,$val,false,$expire);
    }
    
    private function _cacheParent($privateCache,$method,$args=NULL,$key=null) {
        $user = $this->getTwitterUser();
        if ($key != null) {
            $cacheKey = $key;
        } else {
            if ($privateCache) {
                $cacheKey = $user->id.'-'.$method;
                if (is_array($args)) {
                    $cacheKey .= implode('|',$args);
                }
                $cacheKey = md5($cacheKey); // should shorten things a bit. 
            } else {
                $cacheKey = $method; 
            }
        }
        $result = $this->mcGet($cacheKey);
        if ($result === false) {
            $result = call_user_func_array(array('parent',$method),$args);
            if ( ! ($expire = $this->cacheExpires[$method]) ) {
                $expire = $this->cacheExpires['!default!']; 
            }
            $this->mcSet($cacheKey,$result,$expire);
        }
        $this->lastMethod = $method; 
        return $result;
    }
    
    /* 
     FUNCTIONS TO OVERRIDE AND ADD CACHING FUNCTIONALITY TO THE 
     PARENT FUNCTIONS 
     */

    public function getFriendsTimeline() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
    }
    public function getPublicTimeline() {
        return $this->_cacheParent(false,__FUNCTION__);
    }
	public function getUserTimeline() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    
    public function getFavorites($id,$page) {
        $this->disableCache(); // no caching for this. 
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    
	public function createFavorite($id) {
	   $this->_updateStatusCacheFavorite($id,1);
	   return parent::createFavorite($id);
	}
	public function deleteFavorite($id) {
	   // update the single statuses we fetched in our cache 
	   $this->_updateStatusCacheFavorite($id,0);
	   return parent::deleteFavorite($id);
	}
	private function _updateStatusCacheFavorite($id,$val) {
 	    // update the singleton function in the cache... 
        $expire = $this->cacheExpires['showStatus'];
        $user = $this->getTwitterUser(); 
        $key = $user->id.'-status-'.$id; 
        $status = $this->mcGet($key); 
        if ($status !== false) {
            $status->favorited = $val;
            $this->mcSet($key,$status,$expire);
        }
	}
    public function getStatus($id) {
        /* gets and caches a singleton status */ 
        $user = $this->getTwitterUser(); 
        $key = $user->id.'-status-'.$id; 
        $args[] = $id;
        return $this->_cacheParent(true,__FUNCTION__,$args,$key);
	}
    public function getReplies() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    public function getFriends() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    public function getFollowers() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    public function getFriend() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    public function getDirectMessages() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    public function getSentDirectMessages() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
    public function getRateLimitStatus() {
        $args = func_get_args();
        return $this->_cacheParent(true,__FUNCTION__,$args);
	}
}
