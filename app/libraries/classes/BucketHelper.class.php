<?php

class BucketHelper {

    public static function fragStatusArray($uid,$array) {
        // breaks apart an array of statuses
        if (!is_array($array))
            throw new Exception('Expecting an array');
        $sid = array(); 
        foreach ($array as $status) {
            $sid[] = BucketHelper::fragStatus($uid,$status);
        }
        return $sid; 
    }
    public static function fragStatus($uid,$status) {
        $temp = clone $status; // so we don't destroy the original
        $s = Bucket::newBucket($uid,'status');
        $u = Bucket::newBucket($uid,'user');
        $userid = $temp->user->id; 
        $u->add($userid,$temp->user);

        $temp->user = $userid; // replace it with the id
        $s->add($temp->id,$temp);
        return $temp->id;
    }
    public static function defragStatus($uid,$statusId) {
        $s = Bucket::newBucket($uid,'status');
        $u = Bucket::newBucket($uid,'user');
        $status = $s->get($statusId); // get the status item
        $status->user = $u->get($status->user);  // put it back in 
        return $status; 
    }
    public static function defragStatusArray($uid,$array) {
        if (!is_array($array))
            throw new Exception('Expecting an array');
        $items = array(); 
        foreach ($array as $i => $statusId) {
            $items[$i] = BucketHelper::defragStatus($uid,$statusId);
        }
        return $items;
    }
}