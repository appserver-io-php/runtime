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
        if ($this->worker) {
            parse_str(parse_url($this->uri, PHP_URL_QUERY));
            sleep($ttl);
            echo $this->uri . PHP_EOL;
        }
    }
}

class ConnectionHandler extends Worker
{
}

$startTime = microtime(true);

$connectionPool = new Pool(10, ConnectionHandler::class);

$requests = array();

for ($i = 10; $i > 0; $i--) {
    $requests[$i] = new Request("/index.php?ttl=$i");
    $connectionPool->submit($requests[$i]);
}

$connectionPool->shutdown();
$executionTime = microtime(true) - $startTime;

echo sprintf('Successfully finished example in %f seconds', $executionTime) . PHP_EOL;