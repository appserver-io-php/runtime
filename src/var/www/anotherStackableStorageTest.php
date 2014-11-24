<?php

// bootstrap the application
require __DIR__ . '/../../bootstrap.php';

use TechDivision\Storage\StackableStorage;
use TechDivision\ServletEngine\StandardSessionManager;
use TechDivision\ServletEngine\DefaultSessionSettings;

class Server extends \Thread
{

    protected $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function run()
    {

        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        $socket = stream_socket_server("tcp://0.0.0.0:8111", $errno, $errstr);

        $application = $this->application;
        $workers = array();

        for ($i = 0; $i < 100; $i++) {
            $workers[$i] = new ServerWorker($socket, $application);
            $workers[$i]->start();
        }

        while (true) {

            for ($i = 0; $i < 100; $i++) {

                if ($workers[$i]->shouldRestart()) {

                    unset($workers[$i]);

                    echo 'RESTART worker ...' . PHP_EOL;

                    $workers[$i] = new ServerWorker($socket, $application);
                    $workers[$i]->start();

                    echo 'RESTARTED worker ' . $workers[$i]->getThreadId() . PHP_EOL;
                }
            }
        }
    }
}

class ServerWorker extends \Thread
{

    protected $socket;
    protected $application;
    protected $shouldRestart;

    public function __construct($socket, $application)
    {
        $this->socket = $socket;
        $this->application = $application;

        $this->shouldRestart = false;
    }

    public function run()
    {

        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        $socket = $this->socket;
        $application = $this->application;

        $handle = 0;
        while ($handle < 100) {

            $client = stream_socket_accept($socket);

            if (is_resource($client)) {

                $startLine = fgets($client);

                $messageHeaders = '';
                while ($line != "\r\n") {
                    $line = fgets($client);
                    $messageHeaders .= $line;
                }

                $sessionManager = $application->getSessionManager();
                $servlet = $application->getServlet();

                $sessionId = $servlet->service($sessionManager);

                $response = array(
                    "head" => array(
                        "HTTP/1.0 200 OK",
                        "Content-Type: text/html",
                        "Connection: close"
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
    			$response["body"][]="Session-ID: $sessionId";
    			$response["body"][]="</pre>";
    			$response["body"][]="</body>";
    			$response["body"][]="</html>";
    			$response["body"] = implode("\r\n", $response["body"]);

    			$response["head"][] = sprintf("Content-Length: %d", strlen($response["body"]));
    			$response["head"] = implode("\r\n", $response["head"]);

    			fwrite($client, $response["head"]);
    			fwrite($client, "\r\n\r\n");
    			fwrite($client, $response["body"]);

    			stream_socket_shutdown($client, STREAM_SHUT_RDWR);

                $removedSessions = $sessionManager->collectGarbage();

                if ($removedSessions > 0) {
                    echo 'REMOVED ' . $removedSessions . ' sessions [' . date('Y-m-d: H:i:s') . '] - Thread-ID: ' . $this->getThreadId() . PHP_EOL;
                }
            }

            $handle++;
        }

        $this->shouldRestart = true;

        echo 'FINISHED worker ' . $this->getThreadId() . PHP_EOL;
    }

    public function shouldRestart()
    {
        return $this->shouldRestart;
    }
}

class Servlet
{

    protected $offset;

    public function __construct($offset)
    {
        $this->offset = $offset;
    }

    public function service($sessionManager)
    {

        $id = md5(rand(0, $this->offset));

        if ($session = $sessionManager->find($id)) {

            echo "FOUND session $id" . PHP_EOL;

            $session->putData('requests', rand(0, $this->offset));

        } else {

            $session = $sessionManager->create($id, 'test_session');
            $session->start();
            $session->putData('username', 'appsever');

            echo "CREATED session with $id" . PHP_EOL;
        }

        return $id;
    }
}

class Application
{

    protected $sessionManager;
    protected $servlet;

    public function injectSessionManager($sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    public function injectServlet($servlet)
    {
        $this->servlet = $servlet;
    }

    public function getServlet()
    {
        return $this->servlet;
    }
}

$sessionManager = new StandardSessionManager();
$sessionManager->injectSessions(new StackableStorage());
$sessionManager->injectSettings(new DefaultSessionSettings());

$servlet = new Servlet(10000);

$application = new Application();
$application->injectServlet($servlet);
$application->injectSessionManager($sessionManager);

$server = new Server($application);
$server->start();
$server->join();