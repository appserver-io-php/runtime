<?php

class Servlet
{

    protected $offset;
    protected $start;

    public function __construct($offset)
    {
        $this->start = 0;
        $this->offset = $offset;
    }

    public function service($request, $response)
    {

        error_log("Now handle request with servlet " . __FILE__);

        $sessionManager = $request->sessionManager;

        $id = md5(rand(0, $this->offset));

        if ($session = $sessionManager->find($id)) {

            echo "FOUND session $id" . PHP_EOL;

            $session->putData('requests', rand($this->start, $this->offset));

        } else {

            $session = $sessionManager->create($id, 'test_session');
            $session->start();
            $session->putData('username', 'appsever');

            echo "CREATED session with $id" . PHP_EOL;
        }

        $body = $response->body;
        $body[] = "<html>";
        $body[] = "<head>";
        $body[] = "<title>Multithread Sockets PHP ({$request->address}:{$request->port})</title>";
        $body[] = "</head>";
        $body[] = "<body>";
        $body[] = "<pre>";
        $body[] = "Session-ID: $id";
        $body[] = "</pre>";
        $body[] = "</body>";
        $body[] = "</html>";

        $implodedBody = implode("\r\n", $body);

        $response->body = $implodedBody;

        $head = $response->head;
        $head[] = sprintf("Content-Length: %d", strlen($implodedBody));

        $implodedHead = implode("\r\n", $head);
        $response->head = $implodedHead;
    }
}
