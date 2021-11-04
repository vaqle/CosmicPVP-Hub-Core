<?php
namespace vale\queue;
use Couchbase\QueryStringSearchQuery;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use vale\Hub;
use vale\HubPlayer;

class QueueTask extends Task
{
	public function __construct(Hub $pl)
	{
		$this->pl = $pl;
	}

	public function onRun(int $currentTick)
	{
	foreach (Server::getInstance()->getOnlinePlayers() as $player){
		if($player instanceof HubPlayer){
			$player->update();
			$health = round($player->getHealth());
			$rank = $player->getRank();
			switch ($rank){
			    case "Player":
			      	$rank = "§r§7{$player->getName()}";
			        break;
				case "Sage":
					$rank = "§r§d{$player->getName()}";
					break;
				case "Aegis":
					$rank = "§r§b{$player->getName()}";
					break;
				case "Cupid":
					$rank = "§r§7{$player->getName()}";
					break;
				case "Raven":
					$rank = "§r§e{$player->getName()}";
					break;
				case "Admin":
					$rank = "§r§4{$player->getName()} \n §r§c§lSTAFF";
					break;
				case "Trial":
					$rank = "§r§c{$player->getName()} \n §r§c§lSTAFF";
					break;
				case "Mod":
					$rank = "§r§5{$player->getName()} \n §r§c§lSTAFF";
					break;
				case "Media":
					$rank = "§r§o§d{$player->getName()} \n §r§7(§r§o§dMedia)";
					break;
				case "Famous":
					$rank = "§r§o§d{$player->getName()} \n §r§7(§r§o§dFamous)";
					break;
				case "Partner":
					$rank = "§r§o§d{$player->getName()} \n §r§7(§r§o§dPartner)";
					break;
				case "Booster":
					$rank = "§r§o§d{$player->getName()} \n §r§7(§r§o§dBooster)";
					break;
			}
			$player->setNameTag("$rank \n §r§f $health §r§c§lHP");
		  }
	    }
	  }
	}