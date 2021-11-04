<?php
declare(strict_types=1);
namespace vale\tasks\database;

use mysqli;
use pocketmine\scheduler\Task;
use vale\HubPlayer;
use vale\Hub;

class MyDatabaseTask extends Task {

	private $plugin;
	private $mysqli;

	/* DatabaeTask constructor.
	 *
	 * @param Hub $plugin
	 * @param mysqli $mysqli
	 */
	public function __construct(Hub $plugin, mysqli $mysqli){
		$this->plugin = $plugin;
		$this->mysqli = $mysqli;
	}

	/**
	 * @param int $currentTick
	 * @return void
	 */
	public function onRun(int $currentTick): void {
		if(!$this->mysqli->ping()){
			Hub::getInstance()->setDatabase(new mysqli("sql11.freemysqlhosting.net:3306", "sql11438971", "8csy3Itux9", "sql11438971"));
			$this->plugin->getLogger()->info("DATBASE RESET");
			Server::getInstance()->getServer()->broadcastMessage("LOLLL");
		}
	}
}