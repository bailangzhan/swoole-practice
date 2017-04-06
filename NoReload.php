<?php

require_once("./Test.php");

class NoReload
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

		$this->_test = new Test;
	}
	/**
	 * start server
	 */
	public function start()
	{
		$this->_serv->start();
	}
	public function onReceive($serv, $fd, $fromId, $data)
	{
		$this->_test->run($data);
	}
}

$reload = new NoReload;
$reload->start();






