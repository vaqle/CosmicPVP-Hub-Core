<?php
namespace vale\queue;
use pocketmine\block\Sand;
use vale\Hub;
use vale\HubPlayer;

class Queue{

	private $queue = [
		"player" => []
	];

	/**
	 * @param Hub $plugin
	 */
	public function __construct(Hub $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * @param string $player
	 * @param string $group
	 * @return bool
	 */
	public function isInQueue(string $player, string $group){
		if(in_array($player, $this->queue[$group])){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param string $player
	 * @param string $group
	 */
	public function addToQueue(string $player, string $group){
		$this->queue[$group][] = $player;
	}

	/**
	 * @param string $player
	 * @param string $group
	 */
	public function removeFromQueue(string $player, string $group){
		if($this->isInQueue($player, $group)){
			$key = array_search($player, $this->queue[$group]);
			unset($this->queue[$group][$key]);
			$array = $this->queue[$group];
			$this->queue[$group] = array_merge($array);
		}
	}

	/**
	 * @param string $group
	 * @return array
	 */
	public function getQueue(string $group): array {
		return $this->queue[$group];
	}
}