<?php

class Test extends Thread
{

    protected $socket;

    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    public function run()
    {
        
        $client = socket_accept($this->socket);
        socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 5, "usec" => 0));

        $counter = 1;
        $connectionOpen = true;
        $startTime = time();
        
        $timeout = 5;
        $maxRequests = 5;
        
        do {
            
            $buffer = '';
            
            while ($buffer .= socket_read($client, 1024)) {
                if (false !== strpos($buffer, "\r\n\r\n")) {
                    break;
                }
            }

            if ($buffer === '') {
                
                socket_close($client);
                $connectionOpen = false;
                
                continue;
            }
            
            $availableRequests = $maxRequests - $counter++;
            echo '$availableRequests = ' . $availableRequests . PHP_EOL;

            $ttl = ($startTime + $timeout) - time();
    
            ob_start();
            require 'phpinfo.php';
            $contentLength = strlen($message = ob_get_clean());
            
            // prepare response headers
            $headers = array(
                "HTTP/1.1 200 OK",
                "Content-Type: text/html",
                "Content-Length: $contentLength"
            );

            // check if this will be the last requests handled by this thread
            if ($availableRequests > 0 && $ttl > 0) {
                $headers[] = "Connection: keep-alive";
                $headers[] = "Keep-Alive: max=$availableRequests, timeout=$timeout, thread={$this->getThreadId()}";
            } else {
                $headers[] = "Connection: close";
            }
            
            // prepare the response head/body
            $response = array(
                "head" => implode("\r\n", $headers) . "\r\n",
                "body" => $message
            );
            
            // write the result back to the socket
            socket_write($client, implode("\r\n", $response));
            
            // check if this is the last request
            if ($availableRequests <= 0 || $ttl <= 0) {
                
                // if yes, close the socket and end the do/while
                socket_close($client);
                $connectionOpen = false;
            }
            
        } while ($connectionOpen);
    }
}

// check if port was given via arg values
if (!isset($argv[1])) {
    // set 9015 by default
    $argv[1] = 9080;
}

$workers = array();
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, '0.0.0.0', $argv[1]);
socket_listen($socket);

if ($socket) {
    
    $worker = 0;
    
    while (++ $worker < 5) {
        $workers[$worker] = new Test($socket);
        $workers[$worker]->start();
    }
    
    printf("%d threads waiting on port %d" . PHP_EOL, count($workers), $argv[1]);
    
    foreach ($workers as $worker) {
        $worker->join();
    }
}