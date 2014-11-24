<?php

class Session extends \Stackable
{

    public function __construct($id = null, $data = null)
    {
        $this->id = $id;
        $this->data = $data;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }
}

class SessionStorage extends \Stackable
{
}

class SessionManager extends \Stackable
{

    public function __construct()
    {
        $this->sessions = new SessionStorage();
    }

    public function create($id = null)
    {
        $this->addSession($session = new Session($id));
        return $session;
    }

    public function addSession($session)
    {

        foreach ($this->sessions as $sess) {
            if ($sess->getId() == $session->getId()) {
                return;
            }
        }

        $this->sessions[] = $session;
    }

    public function getSession($id = null, $create = true)
    {
        foreach ($this->sessions as $session) {
            if ($session->getId() === $id) {
                return $session;
            }
        }

        if ($create) {
            return $this->create($id);
        }
    }
}

class Container extends \Thread
{

    public function run()
    {
        try {

            if (($socket = stream_socket_server("tcp://127.0.0.1:8586", $errno, $errstr)) === false) {
                throw new \Exception(socket_last_error());
            }

            $sessionManager = new SessionManager();

            $servers = array();

            for ($i = 0; $i < 2; $i++) {
                $servers[$i] = new Server($socket, $sessionManager);
                $servers[$i]->start();
            }

            for ($i = 0; $i < 2; $i++) {
                $servers[$i]->join();
            }

            if (is_resource($socket)) {
                stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
            }

        } catch (\Exception $e) {
            stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
            echo $e->__toString() . PHP_EOL;
        }
    }
}

class Server extends \Thread
{
    protected $socket;
    protected $sessionManager;

    public function __construct($socket, $sessionManager)
    {
        $this->socket = $socket;
        $this->sessionManager = $sessionManager;
    }

    public function run()
    {

        $socket = $this->socket;

        while (true) {

            echo 'Now wait for new request in thread: ' . $this->getThreadId() . PHP_EOL;

            if ($client = stream_socket_accept($socket)) {

                echo 'Now handle request in thread: ' . $this->getThreadId() . PHP_EOL;

    			$session = $this->sessionManager->getSession($id = $this->getThreadId(), false);

    			if ($session == null) {
    			    echo "Manually created new session!" . PHP_EOL;
                    $session = $this->sessionManager->create();
                    $session->setId($id);
    			} else {
    			    echo "Successfully found session with ID: $id" . PHP_EOL;
    			}

    			$session->setData(time());
    			$request = fgets($client);

    			echo 'Found request: ' . $request . PHP_EOL;

    			$response = array(
    				"head" => array(
    					"HTTP/1.0 200 OK",
    					"Content-Type: text/html"
    				),
    				"body" => array()
    			);

    			list ($address, $port) = explode(':', stream_socket_get_name($client, true));

    			$response["body"][]="<html>";
    			$response["body"][]="<head>";
    			$response["body"][]="<title>Multithread Sockets PHP ({$address}:{$port})</title>";
    			$response["body"][]="</head>";
    			$response["body"][]="<body>";
    			$response["body"][]="<pre>";
    			$response["body"][]="</pre>";
    			$response["body"][]="</body>";
    			$response["body"][]="</html>";
    			$response["body"] = implode("\r\n", $response["body"]);
    			$response["head"][] = sprintf("Content-Length: %d", strlen($response["body"]));
    			$response["head"] = implode("\r\n", $response["head"]);

    			fwrite($client, $response["head"]);
    			fwrite($client, "\r\n\r\n");
    			fwrite($client, $response["body"]);

                echo var_export($this->sessionManager, true) . PHP_EOL;

    			stream_socket_shutdown($client, STREAM_SHUT_RDWR);
            }
        }
    }
}

$container = new Container();
$container->start();
