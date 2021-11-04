<?php

namespace vale;

use muqsit\invmenu\InvMenuHandler;
use mysqli;
use pocketmine\Server;
use vale\commands\LinkCommand;
use vale\commands\SetRankCommand;
use vale\commands\StatsCommand;
use vale\HubPlayer;
use pocketmine\event\Listener;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use vale\queue\Queue;
use vale\queue\QueueInfoTask;
use vale\queue\QueueTask;
use vale\stats\StatsProvider;
use vale\tasks\TaskHandler;
use vale\tasks\database\MyDatabaseTask;
use vale\util\UtilListener;

class Hub extends PluginBase
{
	public static $qManager;

	public static $instance;

	public static $database;

	public static ?StatsProvider $statsProvider = null;

	public function onEnable()
	{
		if(!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
		self::$instance = $this;
		self::$qManager = new Queue($this);
		self::$statsProvider = new StatsProvider($this);
		new UtilListener($this);
		TaskHandler::init();
		Server::getInstance()->getCommandMap()->register("sync",new LinkCommand($this));
		Server::getInstance()->getCommandMap()->register("pvp",new StatsCommand($this));
		Server::getInstance()->getCommandMap()->register("setrank",new SetRankCommand($this));
		mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX);
		self::setDatabase(new mysqli("sql11.freemysqlhosting.net:3306", "sql11438971", "8csy3Itux9", "sql11438971"));
		self::$database = new mysqli("sql11.freemysqlhosting.net:3306", "sql11438971", "8csy3Itux9", "sql11438971");
		Server::getInstance()->getLogger()->info("LOGGING DATABASE");
		if(self::$database->connect_error){
			$this->getServer()->shutdown();
			Server::getInstance()->getLogger()->info("DATABASE ERROR");
		}
		self::$database->query("CREATE TABLE IF NOT EXISTS playerdata(username VARCHAR(20) PRIMARY KEY, rank TEXT, permissions TEXT, linked INT, linkCode TEXT, discordID TEXT);");
		$this->getScheduler()->scheduleRepeatingTask(new QueueTask($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new QueueInfoTask($this), 420);
		$this->getScheduler()->scheduleRepeatingTask(new MyDataBaseTask($this, self::$database), 800);
	}

	public function joinMappedSquare(array $array) : string {
		$square = hex2bin("e29688");
		$string = "";
		$count = 0;
		foreach ($array as $arr) {
			$count++;
			$string .= "§".$arr[0].$square;
			if ($count > 9) {
				$count = 0;
				$string .= "\n";
			}
		}
		return $string;
	}



	public function onDisable()
	{
		self::autoSave();
	}

	public static function getStatsProvider(): StatsProvider{
		return self::$statsProvider;
	}

	public function autoSave(): void{
		foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer){
			if($onlinePlayer instanceof HubPlayer){
				$onlinePlayer->saveData();
				Server::getInstance()->broadcastMessage("Saved data");
			}
		}
	}

	public function getDatabase(): mysqli {
		return self::$database;
	}

	public function setDatabase($database) {
		self::$database = $database;
	}


	public static function getInstance(): self{
		return self::$instance;
	}

	public static function getQueueManager(): Queue
	{
		return self::$qManager;
	}

}