<?php

namespace vale\stats;

use SQLite3;
use vale\HubPlayer;
use vale\Hub;

class StatsProvider{

	public SQLite3 $db;

	public Hub $plugin;

	public function __construct(Hub $plugin){
		$this->plugin = $plugin;
		$this->db = new SQLite3($this->plugin->getDataFolder(). "playerdata.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS playerkills(name TEXT primary key, kills int)");;
	}

	public function addKills(string $name, int $amount)
	{
		$this->setKills($name, $this->getKills($name) + $amount);
	}

	public function setKills(string $name, int $amount)
	{
		$this->getDatabase()->exec("INSERT OR REPLACE INTO playerkills(name, kills) VALUES ('$name', " . $amount . ");");
	}


	public function reduceKills(string $name,int $kills){
		$this->setKills($name, $this->getKills($name) - $kills);
	}

	/**
	 * @param string $name
	 *
	 * @return int
	 */
	public function getKills(string $name): int
	{
		$result = $this->getDatabase()->query("SELECT * FROM playerkills WHERE name = '$name';");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return intval($array["kills"] ?? 0);
	}

	public function getTopKills($s) {
		$tf = "";
		$result = $this->getDatabase()->query("SELECT name FROM playerkills ORDER BY kills DESC LIMIT 10;");
		$row = array();
		$i = 0;
		$s->sendMessage("§r§6§l~ Top Players With Most Kills ~\n  \n §r§7((§r§7Listed below are the §6§lthe §r§7Players with the most kills on the Server)) \n");
		while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
			$name = $resultArr['name'];
			$kills = $this->getKills($name);
			$i++;
			$s->sendMessage("§r§6" . $i . ". §r§f" . $name . " §r§o§7" . $kills . " §r§7Kills". "\n");
		}
	}

	public function getDatabase(): SQLite3{
		return $this->db;
	}

}