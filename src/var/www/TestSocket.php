<?php

declare(ticks = 1) {
    
    class Client extends \Worker {
        
        public function __construct() {
            error_log(__METHOD__ . ':' . __LINE__);
        }
        
        public function run() {
            error_log(__METHOD__ . ':' . __LINE__);
        }
    }
    
    class Request extends \Stackable {
        
        protected $client;
        
        public function __construct($client) {
            
            $this->client = $client;
        }
        
        public function run() {
            
    		$client = $this->client;
            
            if ($client) {
            
    			$header = 0;
    			
    			while (($chars = socket_read($client, 1024, PHP_NORMAL_READ))) {
    				$head[$header] = trim($chars);
    				if ($header>0) {
    					if (!$head[$header] && !$head[$header-1])
    						break;
    				}
    				$header++;
    			}
    			
    			foreach ($head as $header) {
    				if ($header) {
    					$headers[] = $header;	
    				}
    			}
    
    			$response = array(	
    				"head" => array(
    					"HTTP/1.0 200 OK",
    					"Content-Type: text/html"
    				), 
    				"body" => array()
    			);
    
    			socket_getpeername($client, $address, $port);
    
    			$response["body"][]="<html>";
    			$response["body"][]="<head>";
    			$response["body"][]="<title>Multithread Sockets PHP ({$address}:{$port})</title>";
    			$response["body"][]="</head>";
    			$response["body"][]="<body>";
    			$response["body"][]="<pre>";
    			foreach($headers as $header)
    				$response["body"][]="{$header}";
    			$response["body"][]="</pre>";
    			$response["body"][]="</body>";
    			$response["body"][]="</html>";
    			$response["body"] = implode("\r\n", $response["body"]);
    			$response["head"][] = sprintf("Content-Length: %d", strlen($response["body"]));
    			$response["head"] = implode("\r\n", $response["head"]);
    
    			socket_write($client, $response["head"]);
    			socket_write($client, "\r\n\r\n");
    			socket_write($client, $response["body"]);
    
    			socket_close($client);
    		}
        }
    }
    
    class TestContainer {
        
        protected $workerNumbers = 1;
        
        protected $workers;
        
        protected $server;
        
        public function __construct($server) {
            
            $this->server = $server;
            
            $this->workers = array();
        }
        
        public function start() {
            
            try {
                
                gc_enable();
            
                if (($socket = @socket_create_listen(8585)) === false) {
                    throw new \Exception(socket_last_error());
                }
                
                if (@socket_set_nonblock($socket) === false) {
                    throw new \Exception(socket_last_error($socket));
                }
                
                while (true) {
                    
                    if ($client = @socket_accept($socket)) {
                        
                        $this->getRandomWorker()->stack($requests[] = new Request($client));
                    }
                    
                    usleep(500);
                }
                        
                error_log("Stop infinite loop");
                
                if (is_resource($socket)) {
                    socket_close($socket);
                }
                
            } catch (\Exception $e) {
                
                socket_close($socket);
                
                error_log($e->__toString());
            }
        }
        
        public function getRandomWorker() {


            $i = rand(0, $this->workerNumbers - 1);
            
            if (array_key_exists($i, $this->workers) === false) {
                
                $this->workers[$i] = new Client();
                $this->workers[$i]->start();
                
                error_log("Successfully created new worker " . $this->workers[$i]->getThreadId());
                
            } else {
                
                if ($this->workers[$i]->isWorking()) {
                    
                    $this->workerNumbers++;
                    
                    error_log("Raise worker number to {$this->workerNumbers}");
                    
                    return $this->getRandomWorker();
                }
            }
            
            return $this->workers[$i];
        }
    }
    
    class TestServer extends \Thread {
        
        protected $container;
        
        public function __construct() {
            $this->container = new TestContainer($this);
        }
        
        public function run() {
            $this->container->start();
        }
    }
    
    
    $server = new TestServer();
    $server->start();
    
}