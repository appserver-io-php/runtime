<?php

declare(ticks = 1);

class TestWorker extends \Worker {
    
    public function __construct() {
    }
	
	public function run() {
		error_log(__METHOD__ . ':' . __LINE__);		
	}
	
	public function stop() {


	    error_log(__METHOD__ . ':' . __LINE__);

	    /*
	     * read from the socket, process the request and send data back to the client
	    */
	    socket_close($this->client);
	    
	    error_log(__METHOD__ . ':' . __LINE__);
	}
}

class TestRequest extends \Stackable {

    /**
     *  The number of bytes to send/receive.
     * @var integer
     */
    protected $lineLength = 2048;

    /**
     * New line character.
     * @var string
     */
    protected $newLine = "\n";
	
	protected $client;
	
	protected $receiver;
	
	public function __construct($receiver, $client) {
	    
	    $this->receiver = $receiver;
		$this->client = $client;

        // catch fatal error (rollback)
        register_shutdown_function(array($this, 'fatalErrorShutdown'));
	}
	
	public function run() {
		
		if ($this->worker) {
			
			$client = $this->client;
            
	        // initialize the buffer
	        $buffer = '';
	
	        // set the new line character
	        $newLine = $this->newLine;
	        
	        // read a chunk from the socket
	        while ($buffer .= socket_read($client, $this->lineLength, PHP_BINARY_READ)) {
	            // check if a new line character was found
	            if (substr($buffer, -1) === $newLine) {
	                // if yes, trim and return the data
	                return rtrim($buffer, $newLine);
	            }
	        }

	        try {
	            
	            // throw new Exception('someException');
	        
    			error_log(__METHOD__ . ':' . __LINE__);
    			
    			$response = array();
    			
    			$this->doSomething();
    			
    			socket_write($client, serialize($response) . "\n");
    	        
    			/* 
    			 * read from the socket, process the request and send data back to the client
    			 */
    			socket_close($client);
	            
	        } catch(\Exception $e) {
	            
	            error_log($e->__toString());
    			
    			$response = array();
    			
    			socket_write($client, serialize($e) . "\n");
    	        
    			/* 
    			 * read from the socket, process the request and send data back to the client
    			 */
    			socket_close($client);
	            
	        }
		}
	}

    /**
     * Method, that is executed, if script has been killed by:
     *
     * SIGINT: Ctrl+C
     * SIGTERM: kill
     *
     * @param int $signal
     */
    public function sigintShutdown($signal) {
        
        error_log(__METHOD__ . ':' . __LINE__);
        
        if ($signal === SIGINT || $signal === SIGTERM) {
            $this->shutdown();
        }
    }

    /**
     * Method that is executed, when a fatal error occurs.
     *
     * @return void
     */
    public function fatalErrorShutdown() {
        
        error_log(__METHOD__ . ':' . __LINE__);
        
        $lastError = error_get_last();
        if (!is_null($lastError) && $lastError['type'] === E_ERROR) {
            $this->shutdown();
        }
    }
}

class TestReceiver extends \Thread {
	
	protected $work;
	
	protected $workers;
	
	public function __construct() {
		$this->work = array();
		$this->workers = array();
	}
	
	public function run() {

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_bind($socket, '127.0.0.1', 8585);
		socket_listen($socket);
		socket_set_nonblock($socket);
		
		$workers = array();
		
		while (true) {
			
			try {
				
				if ($client = @socket_accept($socket)) {
		
            		$i = rand(0, 3);
            		
            		if (array_key_exists($i, $workers) === false) {
            			$workers[$i] = new TestWorker();
            			$workers[$i]->start();
            		}
            		
					$request = new TestRequest($this, $client);
					
					$workers[$i]->stack($this->work[] = $request);
				}
				
			} catch(\Exception $e) {
				error_log($e->__toString());
			}
			
			usleep(300);
		}
		
		socket_close($socket);
		
		$socket = array();

		$this->shutdown();
	}
}

class TestContainer extends \Thread {
	
	public function run() {
		$receiver = new TestReceiver();
		$receiver->start();
		$this->notify();
	}
}

class Server extends \Thread {
	
	public function run() {
	    
	    $threads = array();
	    
		for ($i = 0; $i < 1; $i++) {
			$threads[$i] = new TestContainer();
		    $threads[$i]->start(); 
		}
	}
}

$server = new Server();
$server->start();