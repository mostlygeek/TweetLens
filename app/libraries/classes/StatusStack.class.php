<?php 

class StatusStack extends Stack {

    const flagCacheTime = 86400;
    const timelineStackKey = 'timeline'; 
    const repliesStackKey  = 'replies'; 
    
    const maxTimelineStackSize  = 200; 
    const maxRepliesStackSize  = 200;
    
    public function __construct($user) {
        $this->setTwitterUser($user);
        parent::__construct();
    }

    public function getTimelinePage($page,$count=20,$fromCacheOnly=false) {
        if ($page < 1 || $page > 10) {
            throw new Exception("Page $page is an invalid value");
        }
        $stackId = $this->makeStackId(self::timelineStackKey);
        $tweets = $this->getStackPage($stackId,$page,false);
        
        if (!$fromCacheOnly && (!is_array($tweets) || count($tweets) == 0 )) {
            // Fetch from Twitter. 
            if (is_array($this->getStack($stackId))) { 
                // something already in cache, get additional page
                $tweets = $this->getFriendsTimeline(null,null,20,$page);
                $this->updateStack($stackId,$tweets);
            } else { // init cache. fetch 200 items (10 pages), and save it all. 
                $tweets = $this->getFriendsTimeline(null,null,200);
                $this->setStack($stackId,$tweets);
                $tweets = $this->getStackPage($stackId,$page);
            }
        }
        $this->mergeFlags($tweets);
        return $tweets;
    }
    
    public function getTimelineLatest($sinceid = null){
        if ($sinceid == null) {
            $tweets = $this->getTimelineStack(); 
            $sinceid = (count($tweets) > 0) ? $tweets[0]->id : 1; 
        }
        if ($sinceid == null) 
            throw new Exception('sinceid value is missing');

        $delta = $this->getFriendsTimeline(null,$sinceid,200);
        $stackId = $this->makeStackId(self::timelineStackKey);
        $this->updateStack($stackId,$delta);
        $this->trimStack($stackId,self::maxTimelineStackSize);

        $this->mergeFlags($delta);
        return $delta;  
    }
    
    public function getTimelineStack() {
        $tweets = $this->getStack($this->makeStackId(self::timelineStackKey));
        $this->mergeFlags($tweets);
        return $tweets;
    }
    
    public function getRepliesPage($page) {
        if ($page < 1 || $page > 10) {
            throw new Exception("Page $page is an invalid value");
        }
        $stackId = $this->makeStackId(self::repliesStackKey);
        $tweets = $this->getStackPage($stackId,$page);
        if (count($tweets) == 0) {
            // Fetch from Twitter. 
            $tweets = $this->getReplies(null,null,$page);
            $this->updateStack($stackId,$tweets);
        }
        $this->mergeFlags($tweets);
        return $tweets;
    }
    
    public function getRepliesLatest($sinceid = null) {
        if ($sinceid == null) {
            $tweets = $this->getRepliesStack(); 
            $sinceid = (count($tweets) > 0) ? $tweets[0]->id : 1; 
        }
        if ($sinceid == null) 
            throw new Exception('sinceid value is missing');
        
        $delta = $this->getReplies(null,$sinceid);
        $stackId = $this->makeStackId(self::repliesStackKey);
        $this->updateStack($stackId,$delta);
        $this->trimStack($stackId,self::maxRepliesStackSize);
        $this->mergeFlags($delta);
        return $delta;  
    }
    
    public function getRepliesStack() { 
        $tweets = $this->getStack($this->makeStackId(self::repliesStackKey));
        if (!is_array($tweets)) {
            return array(); 
        }
        $this->mergeFlags($delta);
        return $tweets;
    }
    
    /***
     *  Flags for Status 
     ***/
    protected function mergeFlags($tweets) {
        parent::mergeFlags('STATUS',$tweets,self::flagCacheTime);
    }
    public function unCacheFlag($id) {
        parent::unCacheFlag('STATUS',$id);
    }
        
    public function emptyTimeline() {
        $stackId = $this->makeStackId(self::timelineStackKey);
        parent::emptyStack($stackId);
    }
    public function emptyReplies() {
        $stackId = $this->makeStackId(self::repliesStackKey);
        parent::emptyStack($stackId);
    }
    
    /*** FAVORITING FUNCTIONS ***/
    public function setStatusFavorite($id,$val) {
        if ($val == 'true') {
            $item = $this->createFavorite($id);
            $val = 1; 
        } elseif ($val == 'false') {
            $item = $this->deleteFavorite($id);
            $val = 0; 
        }

        // update the timeline stack
        $tId = $this->makeStackId(self::timelineStackKey);
        $timeline = $this->getStack($tId); 
        $i = $this->idSearch($id,$timeline);
        if ($i !== false) {
            $timeline[$i]->favorited = $val;
            $this->setStack($tId,$timeline);
        }

        // update the replies stack 
        $rId = $this->makeStackId(self::repliesStackKey);
        $replies = $this->getStack($rId); 
        $i = $this->idSearch($id,$replies);
        if ($i !== false) {
            $replies[$i]->favorited = $val;
            $this->setStack($rId,$replies);
        }
        
        return $item; 
    }
    
    /*** Twitter Over-ride functions ***/
    public function updateStatus($status, $replyId = null) {
        $resp = parent::updateStatus($status,$replyId);
        // add it into the current stack
        $stackId = $this->makeStackId(self::timelineStackKey);
        $this->updateStack($stackId,$resp);
        return $resp; 
    }
}