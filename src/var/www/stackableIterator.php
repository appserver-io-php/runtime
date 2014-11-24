<?php

class GenericStackable extends Stackable
{
	public function run()
	{
	}
}

class Configuration
{
	protected $sessionName = 'testSessionName';
	protected $lifeTime = 1440;
	public function getSessionName()
	{
		return $this->sessionName;
	}

	public function getLifeTime()
	{
		return $this->lifeTime;
	}
}

class SessionManager extends Stackable
{
	protected $configuration;

	public function __construct()
	{
		$this->sessions = new GenericStackable();

		$dummy = new stdClass();
		$dummy->id = 0;

		$this["session-0"] = $dummy;
	}

	public function injectConfiguration($configuration)
	{
		$this->configuration = $configuration;
	}

	public function create($id)
	{

		$session = new stdClass();
		$session->id = $id;

		$session->lifeTime = $this->configuration->getLifeTime();
		$session->sessionName = $this->configuration->getSessionName();

		$this->attach($session);

		return $session;
	}

	public function find($id, $create = false)
	{

		if (isset($this["session-$id"])) {
			return $this["session-$id"];
		}

		if ($create === true) {
			return $this->create($id);
		}
	}

	protected function attach($session)
	{
		$this["session-{$session->id}"] = $session;
	}
}

class SomeThread extends Thread
{

	protected $sessionManager;

	public function __construct($sessionManager)
	{
		$this->sessionManager = $sessionManager;

		register_shutdown_function(array($this, 'shutdown'));
	}

	public function shutdown()
	{
		echo __METHOD__ . ':' . __LINE__ . PHP_EOL;
	}

	public function run()
	{

		$sessionManager = $this->sessionManager;

		for ($i = 0; $i < 100; $i++) {
			$sessionManager->create($i);
		}
	}
}

$sessionManager = new SessionManager();
$sessionManager->injectConfiguration(new Configuration());

$someOtherManager = new SessionManager();
$someOtherManager->injectConfiguration(new Configuration());

$someThread = new SomeThread($sessionManager);
$someThread->start();
$someThread->join();

echo 'Now printing result ...' . PHP_EOL;

for ($i = 0; $i < 100; $i++) {
	$session = $sessionManager->find($i);
	if ($session != null) {
		print_r($session) . PHP_EOL;
	} else {
		echo 'Can\'t find session' . PHP_EOL;
	}
}

echo 'Finished!' . PHP_EOL;

for ($i = 0; $i < 100; $i++) {
	$session = $someOtherManager->find($i);
	if ($session != null) {
		print_r($session) . PHP_EOL;
	} else {
		echo 'Can\'t find session' . PHP_EOL;
	}
}
