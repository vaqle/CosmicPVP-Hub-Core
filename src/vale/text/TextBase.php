<?php

declare(strict_types = 1);


namespace vale\text;

//Base Libraries
use pocketmine\level\particle\{FloatingTextParticle};

use pocketmine\math\Vector3;
use vale\HubPlayer;

class TextBase
{

	public static $crate = [];
	public static $spawn_particles = [];
	public static $texts = [];


	public static function start(HubPlayer $player)
	{
		self::spawnCrateText($player);
	}

	public static function spawnCrateText(HubPlayer $player): void
	{

		$spawnx = 36;
		$spawny = 95;
		$spawnz = 33;
		$crates = self::$texts[$player->getName()]["welcome"] = new FloatingTextParticle(new Vector3($spawnx + 0.5, $spawny + 2, $spawnz + 0.5), "");
		$crates->setTitle("§r§c§lSage §r§4~ Demonic Realm ~ §r§c§lI.S.S\n§r§o§r§7Welcome to the Intergalactic Space Station!\n§r§o§r§7You can travel to many different faction galaxies from here. \n \n§r§cYour adventure as a trainee starts now!");
		$player->getLevel()->addParticle($crates, [$player]);


		$spawnx = 46;
		$spawny = 95;
		$spawnz = 32;
		$info = self::$texts[$player->getName()]["info"] = new FloatingTextParticle(new Vector3($spawnx + 0.5, $spawny + 2, $spawnz + 0.5), "");
		$info->setTitle("§r§f§lThe Sage Galaxy\n§r§f§oThe original Sage experience, and the home galaxy of all Trainees.\n§r§f* §r§c150+ Custom Enchantments\n§r§f* §r§cFULL MCMMMO\n§r§f* §r§cCustom Bosses\n§r§f* §r§cCustom TNT\n§r§f* §r§cAbility To Fly\n§r§f* §r§cPlayer Vaults\n§r§f* §r§cSage Conquests\n§r§f* §r§cSage Outposts\n§r§f* §r§cKing of The Hill\n§r§f* §r§cWarzone LMS");
		$player->getLevel()->addParticle($info, [$player]);

	}
}