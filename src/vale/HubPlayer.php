<?php
declare(strict_types=1);

namespace vale;

use pocketmine\{
	Player,
};
use pocketmine\network\{
	SourceInterface,
	mcpe\PlayerNetworkSessionAdapter};
use vale\tasks\database\async\LoadDataTask;
use vale\tasks\database\async\SaveDataTask;
use vale\Hub;

class HubPlayer extends Player
{

	public int $queue = 0;

	public ?bool $isHidingPlayers = false;

	private array $ranks = [
		"rank" => "Player",
		"permissions" => [],
		"linked" => false,
		"linkCode"  => "none",
		"discordID" => "none",
	];

	/**
	 * @param SourceInterface $interface
	 * @param string $ip
	 * @param int $port
	 */
	public function __construct(SourceInterface $interface, string $ip, int $port)
	{
		parent::__construct($interface, $ip, $port);
		$this->sessionAdapter = new PlayerNetworkSessionAdapter($this->server, $this);
	}


	/**
	 * @param string $type
	 * @return bool
	 */
	public function isRegistered(string $type): bool
	{
		$db = Hub::getInstance()->getDatabase();
		$name = $this->getName();
		if ($type == "player") {
			$result = $db->query("SELECT * FROM playerdata WHERE username='" . $db->real_escape_string($name) . "'");
			return $result->num_rows > 0 ? true : false;
		}
	}

	public function createCustomData()
	{
		$db = Hub::getInstance()->getDatabase();
		$name = $this->getName();
		$rank = "Player";
		$i = 0;
		$false = "false";
		$true = 0;
		$permission = "";
		$linkCode = "code";
		$discordID = "id";
		if (!$this->isRegistered("player")) {
			$pdata = $db->prepare("INSERT INTO playerdata(username, rank, permissions, linked, linkCode, discordID) VALUES (?, ?, ?, ?, ?, ?)");
			$pdata->bind_param("sssiss", $name, $rank, $permission,$true,$linkCode, $discordID);
			$pdata->execute();
			$pdata->close();
		}
	}

	public function loadData()
	{
		$database = Hub::getInstance()->getDatabase();
		$async = Hub::getInstance()->getServer()->getAsyncPool();
		$async->submitTask(new LoadDataTask($database, $this->getName()));
	}

	public function saveData()
	{
		$async = Hub::getInstance()->getServer()->getAsyncPool();
		$async->submitTask(new SaveDataTask($this->ranks, $this->getName()));
	}

	public function getLinkedCode(): string{
		return (string) $this->ranks["linkCode"];
	}
	/**
	 * @return string
	 */
	public function getRank(): string
	{
		return (string) $this->ranks["rank"];
	}

	public function getLinked(): int{
		return  (int) $this->ranks["linked"];
	}
	/**
	 * @param string $rank
	 */
	public function setRank(string $rank)
	{
		$this->ranks["rank"] = $rank;
	}

	public function setLinked(int $linked)
	{
		$name = $this->getName();
		$this->ranks["linked"] = $linked;
	}

	public function setDiscordID(string $code){
		$this->ranks["discordID"] = $code;
	}

	public function setLinkcode(string $code){
		$this->ranks["linkCode"] = $code;
	}

	public function getQueue(): int{
		return $this->queue;
	}

	public function setQueue(int $val){
		$this->queue = $val;
	}


	public function update(): void {
			$rank = "player";
			if($rank == "player") {
				$group = "player";
			}elseif ($rank == "lol"){
				$rank = "lol";
			}
			if(Hub::getQueueManager()->isInQueue($this->getName(), $group)){
				$rank = "player";
				if($rank == "player"){
					$before = 1;
					$queue = $before + (int) array_search($this->getName(), Hub::getQueueManager()->getQueue("player"));
					#$this->sendMessage("q pos ". $queue);
				}
				if($queue == 1){
					$server = "play.cosmicpe.me";
				   #$this->transfer($server,19132);
					$this->sendTip("there is no server setup to transfer");
				}
			}
		}
	}