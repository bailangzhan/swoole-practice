<?php

class Reload
{
	private $_serv;
	private $_test;

	/**
	 * init
	 */
	public function __construct()
	{
		$this->_serv = new Swoole\Server("127.0.0.1", 9501);
		$this->_serv->set([
			'worker_num' => 1,
		]);
		$this->_serv->on('Receive', [$this, 'onReceive']);
		$this->_serv->on('WorkerStart', [$this, 'onWorkerStart']);
	}
	/**
	 * start server
	 */
	public function start()
	{
		$this->_serv->start();
	}
	public function onWorkerStart($serv, $workerId)
	{
		require_once("./Test.php");
		$this->_test = new Test;
	}
	public function onReceive($serv, $fd, $fromId, $data)
	{
		$this->_test->run($data);
	}
}

$reload = new Reload;
$reload->start();