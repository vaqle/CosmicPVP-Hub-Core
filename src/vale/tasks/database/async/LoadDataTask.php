<?php
declare(strict_types=1);
namespace vale\tasks\database\async;

use mysqli;
use vale\Hub;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use vale\HubPlayer;

class LoadDataTask extends AsyncTask {
	private $database;
	private $player;

	/**
	 * LoadItTask constructor.
	 * @param mysqli $database
	 * @param string $player
	 */
	public function __construct (mysqli $database, string $player) {

		$this->setDatabase(new mysqli("sql11.freemysqlhosting.net:3306", "sql11438971", "8csy3Itux9", "sql11438971"));
		$this->setPlayer($player);
	}
	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	public function onRun(){
		$results = array();
		$db = new mysqli("sql11.freemysqlhosting.net:3306", "sql11438971", "8csy3Itux9", "sql11438971"));
		$name = $this->getPlayer();
		$pdata = $db->prepare("SELECT rank, permissions, linked, linkCode, discordID FROM playerdata WHERE username=?");
		$pdata->bind_param("s", $name);
		$pdata->bind_result($rank, $permissions, $linked, $linkCode, $discordID);
		$pdata->execute();
		while($pdata->fetch()){
			$results["rank"] = $rank;
			$results["permissions"] = $permissions;
			$results["linkCode"] = $linkCode;
			$results["linked"] = $linked;
			$results["discordID"] = $discordID;
		}
		$this->setResult($results);
	}


	/**
	 * @param Server $server
	 */
	public function onCompletion(Server $server)
	{
		$player = $server->getPlayer($this->getPlayer());
		if(!$player instanceof HubPlayer){
			return;
		}
		if ($player !== null) {
			$player->setLinkcode((string)$this->getResult()["linkCode"]);
			$player->setLinked((int)$this->getResult()["linked"]);
			$player->setRank((string)$this->getResult()["rank"]);
			$player->setDiscordID((string)$this->getResult()["discordID"]);
			$permissions = json_decode($this->getResult()["permissions"], true);
			if ($permissions != null) {
				foreach ($permissions as $permission) {
					$session->addPermission($permission);
				}
			}
		}
	}

	/**
	 * @return mysqli
	 */
	public function getDatabase(): mysqli {
		return $this->database;
	}

	/**
	 * @param mysqli $database
	 */
	public function setDatabase(mysqli $database) {
		$this->database = $database;
	}

	/**
	 * @return string
	 */
	public function getPlayer(): string {
		return $this->player;
	}

	/**
	 * @param string $player
	 */
	public function setPlayer(string $player) {
		$this->player = $player;
	}
}