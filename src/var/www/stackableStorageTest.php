<?php

// bootstrap the application
require __DIR__ . '/../../bootstrap.php';

use TechDivision\Storage\StackableStorage;
use TechDivision\ServletEngine\StandardSessionManager;
use TechDivision\ServletEngine\DefaultSessionSettings;

class SomeThread extends \Thread
{

    protected $offset;
    protected $sessionManager;

    public function __construct($offset, $sessionManager)
    {
        $this->offset = $offset;
        $this->sessionManager = $sessionManager;
    }

    public function run()
    {

        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        $requests = 0;
        while (true) {

            $id = md5(rand(0, $this->offset));
            if ($session = $this->sessionManager->find($id)) {

                echo "Succesfully found session $id" . PHP_EOL;

                $session->putData('requests', $requests);

            } else {

                $session = $this->sessionManager->create($id, 'test_session');
                $session->start();
                $session->putData('username', 'appsever');

                echo "Succesfully created session with $id" . PHP_EOL;
            }

            $removedSessions = $this->sessionManager->collectGarbage();

            if ($removedSessions > 0) {
                echo 'Removed ' . $removedSessions . ' sessions [' . date('Y-m-d: H:i:s') . '] - Thread-ID: ' . $this->getThreadId() . PHP_EOL;
            }

            $requests++;
        }
    }
}

$sessionManager = new StandardSessionManager();
$sessionManager->injectSessions(new StackableStorage());
$sessionManager->injectSettings(new DefaultSessionSettings());

$threads = array();

for ($i = 0; $i < 100; $i++) {
    $threads[$i] = new SomeThread(10000, $sessionManager);
    $threads[$i]->start();
}

for ($i = 0; $i < 2; $i++) {
    $threads[$i]->join();
}

