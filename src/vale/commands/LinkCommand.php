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

class LinkCommand extends PluginCommand
{

	public function __construct(Hub $plugin)
	{
		parent::__construct("sync", $plugin);
	}

	public function execute(\pocketmine\command\CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$sender instanceof HubPlayer) {
			return;
		}
		if(isset($args[0])){
			$sender->sendMessage("§r§e/syncaccount §r§7[code 8-digit num]");
			$sender->sendMessage("§r§7Join our discord server after typing /sync and type the code.");
			return;
		}
		if ($sender->getLinked() === 1) {
			$sender->sendMessage("§r§c§l(!) §r§cYour account is currently linked to another user");
			$sender->sendMessage("§r§7Believe this is an Error? Contact us on discord.");
			return;
		}
		$code = mt_rand(10000, 99999);
		$sender->setLinkcode((string)$code);
		$sender->saveData();
		$link = "link";
		$sender->sendMessage("§r§c§lDISCORD ACCOUNT LINK §r§7(Tutorial)");
		$sender->sendMessage("§r§f- Join the sage discord and find §r§c#bot-cmds§r§f.");
		$sender->sendMessage("§r§f- Type §r§c'$link' $code'§r§f.");
		$sender->sendMessage("§r§f- You're all complete and your account is linked.");
	}
}