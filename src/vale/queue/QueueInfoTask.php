<?php

namespace vale\queue;

use pocketmine\scheduler\Task;

use vale\Hub;
use vale\HubPlayer;

class QueueInfoTask extends Task{

	public ?Hub $plugin = null;

	public function __construct(Hub $plugin){
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick)
	{
		foreach (\pocketmine\Server::getInstance()->getOnlinePlayers() as $player){
			if($player instanceof HubPlayer){
				if(Hub::getQueueManager()->isInQueue($player->getName(),"player")){
					$queue = array_search($player->getName(), Hub::getQueueManager()->getQueue("player"));
					$all = count(Hub::getQueueManager()->getQueue("player"));
					$player->sendMessage("§r§e§l(!) §r§eYou are Queued §r§f#{$queue} §r§eout of §r§f#{$all}");
				}
			}
		}
	}

}
