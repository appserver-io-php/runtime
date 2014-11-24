<?php

// bootstrap the application
require __DIR__ . '/../../bootstrap.php';

use TechDivision\Storage\StackableStorage;
use TechDivision\ServletEngine\StandardSessionManager;
use TechDivision\ServletEngine\DefaultSessionSettings;

class Server extends \Thread
{

    protected $applications;

    public function __construct($applications)
    {
        $this->applications = $applications;
    }

    public function run()
    {

        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        $socket = stream_socket_server("tcp://0.0.0.0:8111", $errno, $errstr);

        $applications = $this->applications;
        $workers = array();
        $handlers = array();

        for ($i = 0; $i < 100; $i++) {

            foreach ($applications as $application) {
                $handlers[$i][] = new RequestHandler($application);
            }

            $workers[$i] = new ServerWorker($socket, $handlers[$i]);
            $workers[$i]->start();
        }

        while (true) {

            for ($i = 0; $i < 100; $i++) {

                if ($workers[$i]->shouldRestart()) {

                    unset($workers[$i]);
                    unset($handlers[$i]);

                    echo 'RESTART worker ...' . PHP_EOL;

                    foreach ($applications as $application) {
                        $handlers[$i][] = new RequestHandler($application);
                    }

                    $workers[$i] = new ServerWorker($socket, $handlers);
                    $workers[$i]->start();

                    echo 'RESTARTED worker ' . $workers[$i]->getThreadId() . PHP_EOL;
                }
            }
        }
    }
}

class Response extends Stackable
{

    public function __construct()
    {
        $this->head = array("HTTP/1.0 200 OK", "Content-Type: text/html", "Connection: close");
        $this->body = array();
    }
}

class Request extends Stackable
{
}

class RequestHandler extends Thread
{

    protected $worker;
    protected $request;
    protected $response;
    protected $run;
    protected $application;

    public function __construct($application)
    {
        $this->run = true;
        $this->application = $application;
        $this->start();
    }

    protected function handleRequest($worker, $request, $response)
    {
        $this->worker = $worker;
        $this->request = $request;
        $this->response = $response;

        $this->notify();
    }

    public function run()
    {

        while ($this->run) {

            $this->wait();

            $worker = $this->worker;
            $application = $this->application;
            $request = $this->request;
            $response = $this->response;

            $sessionManager = $application->getSessionManager();

            $request->sessionManager = $sessionManager;

            $servlet = $application->lookup();
            $servlet->service($request, $response);

            $removedSessions = $sessionManager->collectGarbage();

            if ($removedSessions > 0) {
                echo 'REMOVED ' . $removedSessions . ' sessions [' . date('Y-m-d: H:i:s') . '] - Thread-ID: ' . $this->getThreadId() . PHP_EOL;
            }

            $worker->notify();
        }
    }
}

class ServerWorker extends \Thread
{

    protected $socket;
    protected $handlers;
    protected $shouldRestart;

    public function __construct($socket, $handlers)
    {
        $this->socket = $socket;
        $this->handlers = $handlers;

        $this->shouldRestart = false;
    }

    public function run()
    {

        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        $socket = $this->socket;
        $handlers = $this->handlers;

        $handle = 0;
        while ($handle < 100) {

            $client = stream_socket_accept($socket);

            if (is_resource($client)) {

                $line = '';

                $startLine = fgets($client);

                $messageHeaders = '';

                while ($line != "\r\n") {
                    $line = fgets($client);
                    $messageHeaders .= $line;
                }

                $request = new Request();

                list ($address, $port) = explode(':', stream_socket_get_name($client, true));

                $request->address = $address;
                $request->port = $port;

                $response = new Response();

                $handler = $handlers[rand(0, sizeof($handlers) - 1)];
                $handler->handleRequest($this, $request, $response);

                $this->wait();

                fwrite($client, $response->head);
                fwrite($client, "\r\n\r\n");
                fwrite($client, $response->body);

                stream_socket_shutdown($client, STREAM_SHUT_RDWR);

                $handle++;
            }
        }

        $this->shouldRestart = true;

        echo 'FINISHED worker ' . $this->getThreadId() . PHP_EOL;
    }

    public function shouldRestart()
    {
        return $this->shouldRestart;
    }
}

class Application extends Thread
{

    protected $sessionManager;
    protected $servlet;
    protected $run;

    public function __construct($name)
    {
        $this->name = $name;
        $this->run = true;
    }

    public function injectSessionManager($sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    public function lookup()
    {

        $name = $this->name;

        error_log("Now lookup servlet for app $name");

        require_once __DIR__ . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'Servlet.php';

        return $this->servlet;
    }

    public function run()
    {

        $name = $this->name;

        require_once __DIR__ . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'Servlet.php';

        $this->servlet = new Servlet(10000);

        while ($this->run) {
            $this->wait();
        }
    }
}

$sessionManager = new StandardSessionManager();
$sessionManager->injectSettings(new DefaultSessionSettings());

$applications = array();
$applications[0] = new Application('app_01');
$applications[0]->injectSessionManager($sessionManager);
$applications[0]->start();

$applications[1] = new Application('app_02');
$applications[1]->injectSessionManager($sessionManager);
$applications[1]->start();

$server = new Server($applications);
$server->start();
$server->join();
