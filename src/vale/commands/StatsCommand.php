<?php
namespace vale\commands;

use pocketmine\command\Command;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use vale\Hub;
use vale\HubPlayer;
use function mt_rand;

class StatsCommand extends PluginCommand
{

	public $plugin;

	public function __construct(Hub $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct("pvp", $plugin);
	}

	public function execute(\pocketmine\command\CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$sender instanceof HubPlayer) {
			return;
		}
		if(!isset($args[0])){
			$sender->sendMessage("§r§e/pvp §r§7[top, list, all]");
			return;
		}
		switch ($args[0]){
			case "top":
				Hub::getStatsProvider()->getTopKills($sender);
				break;
			case "list":
				Hub::getStatsProvider()->getTopKills($sender);
				break;
			case "all":
				Hub::getStatsProvider()->getTopKills($sender);
				break;
		}
	}
}