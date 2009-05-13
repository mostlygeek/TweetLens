<?php

class Stack extends TwitterCache {
    
    const stackCacheLifetime = 86400; 
    
    protected $mc = null; 
    protected $db = null;
     
    public function __construct() {
        $this->mc = getMemcacheObject(); 
        $this->db = getDatabase();
    }
    
    protected function makeStackId($type) {
        $user = $this->getTwitterUser();
        if (!is_object($user)) {
            $this->throwException(__METHOD__," User is not an object");
        }
        $stackId = $user->id."-".$type; 
        return $stackId; 
    }

    protected function updateStack($stackId,$data) {
        $stack = $this->getStack($stackId);
        if (!is_array($stack)) 
            $stack = array(); // an empty one

        if (is_object($data)) // make it an array
            $data = array($data);
                        
        // merge in, de-dupe, sort and save. 
        $all = array_merge($data,$stack);
        $lookup = array(); 
        foreach ($all as $key => $object) {
            $lookup[$object->id] = $key; // dedupe. 
        }            
        krsort($lookup); // sort, reverse
        $new = array(); // build a new array
        foreach ($lookup as $key) {
            $new[] = $all[$key]; 
        }
        $this->setStack($stackId,$new); // save
    }
    
    protected function getStack($stackId) {
        return $this->mc->get($stackId);
    }
    
    protected function setStack($stackId,$stackData) {
        if ($this->mc->set($stackId,$stackData,null,self::stackCacheLifetime) === false) {
            $this->throwException(__METHOD__,'Memcache Set() Failed');
        }
    }

    protected function getStackPage($stackId,$page=1,$full=false) {
        $data = $this->getStack($stackId); 
        if ($data === false) {
            return array(); // empty page
        }
        $start = ($page-1)*20; 
        $end = ($page*20)-1;        

        if ($full && !isset($data[$end])) // only full pages
            return array(); // empty page

        return array_slice($data,$start,20); // 
    }

    protected function trimStack($stackId,$max=200) {
        $data = $this->getStack($stackId); 
        $data = array_slice($data,0,$max); 
        $this->setStack($stackId,$data); 
    }
    
    protected function emptyStack($stackId) {
        if ($this->mc->delete($stackId) === false) {
            $this->throwException(__METHOD__,'Memcache Delete() failed');
        }
    }

    /**** CUSTOM HELPER FUNCTIONS ****/
    protected function idSearch($id,$stack) {
        if (!is_array($stack)) return false; 
        $low = 0; $high = count($stack)-1; 
        while ($low <= $high) {
            $i = round(($low + $high)/2);
            $comp = ($id - $stack[$i]->id);
            if ($comp < 0) { $low = $i+1; continue; }
            if ($comp > 0) { $high = $i-1; continue; }
            return $i; 
        }
        return false; 
    }
    protected function _makeKey($type,$who,$what) {
        // memcache key for statuses and message read, show
        return "$type-$who-$what";
    }

    protected function _makeIdArray($objs) {
        if (is_array($objs)) {
            // load multiple values and cache
            $keys = array(); 
            foreach ($objs as $object) {
                $keys[] = $object->id; 
            }
        } else {
            // load a single value and cache
            $keys[] = $objs->id; 
        }
        return $keys;
    }
    protected function _makeLookup($objs) {
        $lookup = array();
        if (is_array($objs)) {
            foreach ($objs as $key => $object) {
                $lookup[$object->id] = $key;
            }
        }
        return $lookup;
    }
    
    // Item Flag Functions 
    protected function mergeFlags($targetType,$items,$cacheTime=86400) {
        $user = $this->getTwitterUser();
        $uid = $user->id;
        $flags = Flags::targetTypes();
        $targetTypeCode = Flags::getTargetCode($targetType);
        Flags::checkTarget($targetTypeCode);
        $flagBucket = Bucket::newBucket($uid,'flagBucket');
        foreach ($items as $item) {
            $key = sprintf('%s-%s',$item->id,$targetTypeCode);
            $flag = $flagBucket->get($key); 
            if (!is_object($flag)) {// attempt the db
                $flag = Flags::getTargetFlags($uid,$item->id,$targetTypeCode);
                $flag->_fromDB = 1;
                if (!is_object($flag)) {
                    // create a default set of flags.
                    $flag = new stdClass();
                    $flag->READ = '0'; 
                    $flag->SHOW = '1'; 
                    $flag->_default = 1;
                }
                $flagBucket->add($key,$flag,$cacheTime);                         
            } else {
                $flag->_cacheHit = 1;
            }
            $item->flags = $flag;
        }
    }
    public function unCacheFlag($targetType,$id) {
        $user = $this->getTwitterUser();
        $uid = $user->id;
        $targetTypeCode = Flags::getTargetCode($targetType);
        Flags::checkTarget($targetTypeCode);
        $flagBucket = Bucket::newBucket($uid,'flagBucket');
        $key = sprintf('%s-%s',$id,$targetTypeCode);
        $flagBucket->del($key);
    }
    
    
}