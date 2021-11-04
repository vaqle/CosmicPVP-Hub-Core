<?php
declare(strict_types=1);
namespace vale\commands;

use pocketmine\Player;
use pocketmine\command\{CommandSender, ConsoleCommandSender, PluginCommand};
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use raklib\server\SessionManager;
use vale\Hub;
use vale\HubPlayer;

class SetRankCommand extends PluginCommand
{
	/** @var Hub */
	private $plugin;

	public static $Ranks = ["Aesthete", "Sage", "Cupid", "Trial", "Aegis", "Partner", "Media", "Famous", "Mod", "Admin", "Raven", "Booster"];

	const SET_RANK_SUCCESS = "§l§e(!) §r§eYour rank has now been set to a(n) §6";

	/**
	 * BalanceCmd constructor.
	 *
	 * @param Hub $plugin
	 */
	public function __construct(Hub $plugin)
	{
		parent::__construct("setrank", $plugin);
		$this->plugin = $plugin;
		$this->setPermission("core.cmd.setrank");
		$this->setUsage("/setrank <user> <rank>");
		$this->setDescription("Sets the rank of a player on the server!");
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	public function execute(CommandSender $sender, string $label, array $args)
	{


		if ($sender instanceof ConsoleCommandSender || $sender->hasPermission("setrank.cmd") || $sender->isOp()) {
			if (count($args) < 1) {
				$sender->sendMessage("§eInvalid arguement exceptions");
				$sender->sendMessage("§7usage: §e/setrank §7<§6player§7> <§6rank§7>");

			} elseif (($player = Hub::getInstance()->getServer()->getPlayer($args[0]))) {
				if (isset($args[0]) && isset($args[1])) {
					if ($player instanceof HubPlayer && in_array($args[1], self::$Ranks)) {
						$player->setRank($args[1]);
						$player->saveData();
						$sender->sendMessage("§eYour rank transaction has been successfully completed");
						$sender->sendMessage("§7   §3");

						if ($player->isOnline()) {
							$player->sendMessage(self::SET_RANK_SUCCESS . $args[1]);
						}
					} elseif (!in_array($args[1], self::$Ranks)) {
						$sender->sendMessage("§l§c(!) §r§cThe rank name you specified does not exist");
					}
				}
			}
		} else {
			$sender->sendMessage("§l§c(!) §r§cError: you lack sufficient permissions to access this command");
		}
	}
}

