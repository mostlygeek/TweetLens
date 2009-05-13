<?php

class Flags {
    public static function flagTypes() {
        return array(
            'READ'  => 1,
            'SHOW'  => 2,
            'VOTE'  => 3
        );
    }
    public static function targetTypes() {
        return array(
            'STATUS'    => 1,
            'MESSAGE'   => 2,
            'USER'      => 3,
            'TWITPIC'   => 4
        ); 
    }
    
    public static function flagCodes() { return array_flip(Flags::flagTypes()); }
    public static function targetCodes() { return array_flip(Flags::targetTypes()); }
    public static function getTargetName($code) { // code based search
        Flags::checkTarget($code);
        $codes = Flags::targetCodes(); 
        return $codes[$code];
    }
    public static function getTargetCode($name) { // name based search
        $flags = Flags::targetTypes();
        Flags::checkTarget($flags[$name]); 
        return $flags[$name]; 
    }
    public static function getFlagName($code) {
        Flags::checkFlag($code);
        $codes = Flags::flagCodes();
        return $codes[$code];
    }
    public static function getFlagCode($name) {
        $flags = Flags::flagTypes();
        Flags::checkFlag($flags[$name]); 
        return $flags[$name]; 
    }
    
    // Database Functions    
    public static function removeFlag($ownerId,$targetId,$targetType,$flagType) {
        Flags::checkTarget($targetType);
        Flags::checkFlag($flagType);
        $db = getDatabase(); 
        $db->query("DELETE FROM Flags WHERE ownerId=$ownerId AND targetId=$targetId AND targetType=$targetType and flagType=$flatType");        
    }
    
    public static function setFlag($ownerId,$targetId,$targetType,$flagType,$flagValue='') {    
        Flags::checkTarget($targetType);
        Flags::checkFlag($flagType);
        if (strlen($flagValue) > 64)
            throw new Exception("Flag value can not exceed 64 characters");
        
        $db = getDatabase();
        $db->query_replace('Flags',array(
            'ownerId'    => $ownerId,
            'targetId'   => $targetId,
            'targetType' => $targetType,
            'flagType'   => $flagType,
            'flagValue'  => $flagValue
        ));
    }
    
    // Flag Data Get functions
    public static function getTargetFlags($ownerId,$targetId,$targetType,$object=true) {
        Flags::checkTarget($targetType);
        $db = getDatabase();
        if (is_array($targetId)) {
            $ids = implode(',',$targetId);
            $data = $db->fetch_all_array("SELECT * FROM Flags 
                WHERE ownerId=$ownerId AND targetId in ($ids) and targetType=$targetType");
        } else {
            $data = $db->fetch_all_array("SELECT * FROM Flags 
                WHERE ownerId=$ownerId AND targetId=$targetId and targetType=$targetType");
        }
        /* make more useful: $array['id'] = array ( $flag type => $value) 
         */
        $merged = array();
        foreach ($data as $r) {
            $name = Flags::getFlagName($r['flagType']);
            $merged[$r['targetId']][$name] = $r['flagValue'];
        }
        if ($object) // return object instead
            foreach($merged as $id => $flags) 
                $merged[$id] = Flags::makeObject($flags);
        
        if (count($merged) == 0) return false; 
        return (is_array($targetId)) ? $merged : array_pop($merged);
    }
    public static function makeObject($flags) { // convert it to an object
        $x = new stdClass; 
        foreach ($flags as $name => $value) 
            $x->$name = $value; 
        return $x; 
    }
    // Validation Functions
    public static function checkFlag($flagType) {
        $fTypes = Flags::flagCodes();
        if (!isset($fTypes[$flagType]))
            throw new Exception("Invalid Flag Type: ".$flagType);
    }
    public static function checkTarget($targetType) {
        $tTypes = Flags::targetCodes(); 
        if (!isset($tTypes[$targetType]))
            throw new Exception("Invalid Target Type: ".$targetType);
    }
        
}; 