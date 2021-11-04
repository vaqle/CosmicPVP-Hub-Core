<?php
declare(strict_types=1);
namespace vale\tasks\database\async;

use mysqli;
use vale\Hub;
use vale\HubPlayer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class SaveDataTask extends AsyncTask {
	private $data;
	private $kits;
	private $pvp;
	private $player;

	/**
	 * SaveItTask constructor.
	 * @param array $data
	 * @param array $kits
	 * @param array $pvp
	 * @param string $player
	 */
	public function __construct (array $data, string $player) {
		$this->setData($data);
		$this->setPlayer($player);
	}
	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	public function onRun(){
		$results = array();
		$db = new mysqli("sql11.freemysqlhosting.net:3306", "sql11438971", "8csy3Itux9", "sql11438971");
		$name = $this->getPlayer();
		$rank = (string) $this->getData()["rank"];
		$permissions = json_encode($this->getData()["permissions"]);
		$linked = $this->getData()["linked"];
		$linkCode = (string) $this->getData()["linkCode"];
		$discordID = (string) $this->getData()["discordID"];
		$pdata = $db->prepare("UPDATE playerdata SET rank=?, permissions=?, linked=?, linkCode=?, discordID=? WHERE username=?");
		$pdata->bind_param("sssiss", $rank, $permissions, $linked, $linkCode,$discordID,$name);
		$pdata->execute();
		var_dump($pdata);
		$pdata->close();
	}

	/**
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
	}

	/**
	 * @return array
	 */
	public function getData(): array {
		return (array) $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data) {
		$this->data = $data;
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