<?php

class MessageStack extends Stack {

    const flagCacheTime = 86400;
    const readCacheTime = 86400; 
    const showCacheTime = 86400;
    const maxStackSize  = 200; 

    public function __construct($user = null) {
        if ($user != null) {
            $this->setTwitterUser($user);
        }
        parent::__construct();
    }
    
    /** Functions for getting direct / sent messages. 
        the functionality is essentially the same except for a few
        different calls 
     **/
     
    private function _getPage($type,$page,$fromCacheOnly=false) {
        if ($page < 1 || $page > 10)
            throw new Exception("Page $page is an invalid value");

        $stackId = $this->makeStackId($type);
        $messages = $this->getStackPage($stackId,$page,false);
        if (!$fromCacheOnly && (!is_array($messages) || count($messages) == 0 )) {
            // Fetch from Twitter.
            $messages = ($type == 'messages') ? 
                $this->getDirectMessages(null,null,$page) : 
                $this->getSentDirectMessages(null,null,$page); 

            $this->updateStack($stackId,$messages);
        }
        $this->mergeFlags($messages);
        return $messages;

    }
    private function _getLatest($type,$sinceid) {
        if ($sinceid == null) {
            $messages = $this->_getStack($type);
            $sinceid = $messages[0]->id; 
        }
        
        if ($sinceid == null) 
            throw new Exception('sinceid value is missing');
            
        $delta = ($type == 'messages') ? 
            $this->getDirectMessages(null,$sinceid) : 
            $this->getSentDirectMessages(null,$sinceid); 

        $stackId = $this->makeStackId($type);
        $this->updateStack($stackId,$delta);
        $this->trimStack($stackId,self::maxStackSize);
        $this->mergeFlags($delta);        
        return $delta;      
    }
    
    private function _getStack($type) {
        $messages = $this->getStack($this->makeStackId($type));
        if (!is_array($messages))
            return array();         
        $this->mergeFlags($messages);        
        return $messages;        
    }

    public function getMessagesPage($page) {
        return $this->_getPage('messages',$page);
    }
    public function getMessagesLatest($sinceid = null) {
        return $this->_getLatest('messages',$sinceid);
    }
    public function getMessagesStack() {
        return $this->_getStack('messages');
    }
    public function getSentMessagesPage($page) {
        return $this->_getPage('sentmessages',$page);
    }
    public function getSentMessagesLatest($sinceid = null) {
        return $this->_getLatest('sentmessages',$sinceid);
    }
    public function getSentMessagesStack() {
        return $this->_getStack('sentmessages');
    }
    
    /***
     *  Flags for Status 
     ***/
    protected function mergeFlags($messages) {
        parent::mergeFlags('MESSAGE',$messages,self::flagCacheTime);
    }
    public function unCacheFlag($id) {
        parent::unCacheFlag('MESSAGE',$id);
    }
}