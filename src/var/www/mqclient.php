<?php

namespace TechDivision\Example;

use TechDivision\MessageQueueClient\Messages\StringMessage;
use TechDivision\SplClassLoader;
use TechDivision\MessageQueueClient\Queue;
use TechDivision\MessageQueueClient\QueueConnectionFactory;
use TechDivision\MessageQueueClient\Messages\IntegerMessage;

// set the session timeout to unlimited
ini_set('session.gc_maxlifetime', 0);
ini_set('zend.enable_gc', 0);
ini_set('max_execution_time', 0);

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(dirname(__FILE__))));

$paths[] = BP . DS . 'webapps' . DS .'example' . DS . 'META-INF' . DS . 'classes';
$paths[] = BP . DS . 'webapps' . DS .'example';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'local';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'community';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'core';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'lib';

// set the new include path
set_include_path(implode(PS, $paths) . PS . get_include_path());

//require_once 'TechDivision/SplClassLoader.php';
require_once BP . DS . 'app' . DS . 'code' . DS . 'vendor' . DS . 'autoload.php';

$classLoader = new SplClassLoader();
$classLoader->register();

session_start();

// initialize the connection and the session
$queue = Queue::createQueue("queue/import");
$connection = QueueConnectionFactory::createQueueConnection();
$session = $connection->createQueueSession();
$sender = $session->createSender($queue);

// create a new message and send it
$send = $sender->send(new StringMessage('/opt/appserver/webapps/example/META-INF/data/example-persons3000.csv'), false);