<?php

class SomeThread extends Thread
{
    
    protected $uid;
    
    public function __construct($uid)
    {
        $this->uid = $uid;
    }
    
    public function run()
    {
        $uid = $this->uid;
        $user = posix_getpwnam($uid);
        posix_setuid($user['uid']);
        
        while (true) {
            sleep(1);
            error_log("We run as user: " . posix_getuid());
        }
    }
}

$someThread = new SomeThread('wagnert');
$someThread->start();