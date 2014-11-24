<?php

class Request extends Stackable
{
    protected $uri;
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function run()
    {
        parse_str(parse_url($this->uri, PHP_URL_QUERY));
        sleep((integer) $ttl);
        echo $this->uri . PHP_EOL;
    }
}

class ConnectionHandler extends Thread
{

    public $request;
    public $done;
    public function injectRequest($request)
    {
        $this->done = false;
        $this->request = $request;
    }

    public function done()
    {
        $this->done = true;
    }

    public function run()
    {

        while ($this->done == false) {

            $this->synchronized(function() {

                echo "Start waiting in thread " . $this->getThreadId() . PHP_EOL;

                $this->wait();

                $request = $this->request;

                $request->run();

            });
        }
    }
}

class Runner extends Thread
{

    protected $counter;

    public function __construct()
    {
        $this->counter = 10;
    }

    public function run()
    {

        $startTime = microtime(true);

        $connectionHandler = new ConnectionHandler();
        $connectionHandler->start();

        $requests = array();

        while ($this->counter > 0) {

            $this->synchronized(function ($connectionHandler) use ($requests) {

                if ($connectionHandler->isWaiting()) {

                    $requests[$this->counter] = new Request("/index.php?ttl=" . $this->counter);

                    $connectionHandler->injectRequest($requests[$this->counter]);

                    echo "Start notifying thread " . $connectionHandler->getThreadId() . PHP_EOL;

                    $connectionHandler->notify();

                    echo "Handled request " . $this->counter . PHP_EOL;

                    $this->counter--;
                }

            }, $connectionHandler);

        }

        $connectionHandler->done();

        $executionTime = microtime(true) - $startTime;

        echo sprintf('Successfully finished example in %f seconds', $executionTime) . PHP_EOL;
    }
}

$runner = new Runner();
$runner->start();