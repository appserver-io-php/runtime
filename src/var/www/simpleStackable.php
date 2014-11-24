<?php

class GenericStackable extends Stackable
{
}

class Deployment
{

	protected $applications = array();

	public function deploy()
	{

		$app = new stdClass();
		$app->stackable = new GenericStackable();

		$this->applications[] = $app;

		return $this;
	}

	public function getApplications()
	{
		return $this->applications;
	}
}

class SomeThread extends Thread
{


	protected $deployment;

	public function __construct()
	{
		$this->deployment = new Deployment();
	}

	public function run()
	{

		/*
		$deployment = $this->deployment;
		$deployment->deploy();
		$applications = $deployment->getApplications();
		*/

		$deployment = new Deployment();
		$deployment->deploy();
		$applications = $deployment->getApplications();

		$anotherThread = new AnotherThread($applications);
		$anotherThread->start();

		$anotherThread->join();
	}
}

class AnotherThread extends Thread
{

	protected $applications;

	public function __construct($applications)
	{
		$this->applications = $applications;
	}

	public function run()
	{
		foreach ($this->applications as $application) {
			var_export($application);
		}
	}
}

echo "Now starting ..." . PHP_EOL;

$anotherThread = new SomeThread();

$anotherThread->start();
$anotherThread->join();

echo "Finished!" . PHP_EOL;