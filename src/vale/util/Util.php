<?php

namespace vale\util;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;
use vale\Hub;
use vale\HubPlayer;

class Util{

	/** @var array $pvpEnabled */
	public static array $pvpEnabled = [];


	public static function sendTransferMeu(Player $player)
	{
		$menu = \muqsit\invmenu\InvMenu::create(\muqsit\invmenu\MenuIds::TYPE_DOUBLE_CHEST)->
		setName("Server Selector");
		$pane = Item::get(Item::STAINED_GLASS_PANE,0,1)->setCustomName("''");
		$pane2 = Item::get(Item::STAINED_GLASS_PANE,14,1)->setCustomName("''");
		$black = Item::get(Item::STAINED_GLASS_PANE,15,1)->setCustomName("''");
		$slots1 = [36,46,7,17,1,9,52,53];
		$slots2 = [2,3,4,5,6,10,11,12,13,14,15,16,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,35,37,38,39,40,41,42,43,44,47,48,49,50,51];
		foreach ($slots2 as $slotval2){
			$menu->getInventory()->setItem($slotval2, $black);
		}
		foreach ($slots1 as $slotval){
			$menu->getInventory()->setItem($slotval, $pane2);
		}
		$slots = [0,8,45,53];
		foreach ($slots as $slotID) {
			$menu->getInventory()->setItem($slotID,$pane);
		}
		$transfer = Item::get(Item::BEACON)->setCustomName("§r§4~ §4§lDemonic Realm §r§4")->setLore([
			'§r§fMax-Players: 0/150 ',
			'§r§a§lONLINE',
			'',
			'§r§c§lServer Stats',
			'§r§c§l* §r§7Map: §r§f§l1',
			'§r§c§l* §r§7F-Top #1 Payout: §r§f50$ Buycraft Voucher',
			'',
			'§r§c§lFeatures:',
			'§r§c§l* §r§fCustom Enchantments §r§7(150+)',
			'§r§c§l* Custom Blocks',
			'§r§c§l* Team Duels, §r§fDuels, Envoys, Bosses, Armor Sets',
			'§r§c§l* §r§fGambling:',
			' §r§c- §r§fCoinflip, Jackpot, Item Flip, Roulette',
			'§r§c§l* §r§fDungeons:',
			' §r§c- §r§fAbandoned Spaceship, Destroyed OutPost, §r§c§lPlanet Null',
			'§r§c§l* §r§fDemonic Chests, Crate Keys, Lootboxes,Bundles',
			'§r§c§l* §r§fMasks, Custom TnT, Custom Creeper Eggs',
			'§r§c§l* §r§fBlack Market Auction',
			'§r§c§l* §r§fCustom End Grinding',
			'§r§c§l* §r§fAbyssal Stronghold',
			'',
			'§r§f§l* §r§7Keep up with updates via §r§cbit.ly/sagediscord',
			'',
			'§r§f§oMuch more...',
			'',
			'§r§c§l(!) §r§cClick to join server queue.'
		]);
		$menu->getInventory()->setItem(22,$transfer);
		$menu->send($player);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($player, $menu): void {
			$clicked = $transaction->getItemClicked();
			if($clicked->getCustomName() === "§r§4~ §4§lDemonic Realm §r§4"){
				if(Hub::getQueueManager()->isInQueue($player->getName(),"player")){
					Hub::getQueueManager()->removeFromQueue($player->getName(), "player");
					$player->sendMessage("§r§e§l(QUEUE) §r§eYou left the queue for§r§7: §r§fdemonic realm.");
					Util::playSound($player,"mob.enderdragon.flap",1,1,1);
				}elseif (!Hub::getQueueManager()->isInQueue($player->getName(), "player")){
					$queue =  array_search($player->getName(), Hub::getQueueManager()->getQueue("player"));
					$rand = rand(1,200);
					$player->sendMessage("§r§e§l(!) §r§ePlease standby, you are being shuttled to the planet..");
					$player->sendMessage("§r§e§l(QUEUE) §r§eYou joined the queue for§r§7: §r§fdemonic realm §r§7[eta:{$rand}s]");
					Hub::getQueueManager()->addToQueue($player->getName(), "player");
					Util::playSound($player,"mob.enderdragon.flap",1,1,1);
				}
			}

		}));
	}

	public static function playSound(Entity $player, string $sound, $volume = 1, $pitch = 1, int $radius = 5): void{
		foreach($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p){
			if($p instanceof HubPlayer){
				$spk = new PlaySoundPacket();
				$spk->soundName = $sound;
				$spk->x = $p->getX();
				$spk->y = $p->getY();
				$spk->z = $p->getZ();
				$spk->volume = $volume;
				$spk->pitch = $pitch;
				$p->dataPacket($spk);
			}
		}
	}


	public static function sendBar(HubPlayer $player, string $type)
	{
		if ($type === "pvp") {
			$player->getInventory()->clearAll();
			$sword = Item::get(Item::DIAMOND_SWORD, 0, 1)->setCustomName("§r§b§lCombat Sword §r§7(Right-Click)");
			$sword->getNamedTag()->setTag(new StringTag("pvpoff"));
			$player->getInventory()->setItem(0, $sword);
			self::$pvpEnabled[$player->getName()] = true;
			$player->sendMessage("§r§a§l(!) PvP §r§ahas been enabled.");
			$player->sendMessage("§r§a                       ――――――――");
			$player->sendMessage("§r§7Use §r§a/pvp top §r§7to view the highest active killstreaks!");
		}
		if($type === "show"){
			$showPlayers = Item::get(Item::LEVER)->setCustomName("§r§b§lShow Players §r§7(Right-Click)")->setLore([
				'§r§dRight-Click to show players!'
			]);
			$showPlayers->getNamedTag()->setTag(new StringTag("hub_show"));
			$player->getInventory()->setItem(3,$showPlayers);
		}
	}

	public static function sendHubItems(HubPlayer $player): void{
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$player->setFood($player->getMaxFood());
		$player->setHealth($player->getMaxHealth());
		$chest = Item::get(Item::IRON_CHESTPLATE);
		$chest->setCustomName("§r§f§l< §r§bCosmonaut Chestplate §r§f§l>");
		$legg = Item::get(Item::IRON_LEGGINGS);
		$legg->setCustomName("§r§f§l< §r§bCosmonaut Leggings §r§f§l>");
		$boots = Item::get(Item::IRON_BOOTS);
		$boots->setCustomName("§r§f§l< §r§bCosmonaut Boots §r§f§l>");
		$allArmour = [
			$chest,
			$legg,
			$boots
		];
		foreach ($allArmour as $amour) {
			$amour->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION),1));
			$amour->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING),10));
		}
		$player->getArmorInventory()->setHelmet(Item::get(Item::SKULL));
		$player->getArmorInventory()->setChestplate($chest);
		$player->getArmorInventory()->setLeggings($legg);
		$player->getArmorInventory()->setBoots($boots);

		$enablePvp = Item::get(Item::DIAMOND)->setCustomName("§r§b§lEnable PvP §r§7(Right-Click)")->setLore([
			'§r§dRight-Click to enable PvP!'
		]);
		$enablePvp->getNamedTag()->setTag(new StringTag("hub_pvp"));
		$cannon = Item::get(Item::HORSE_ARMOR_DIAMOND)->setCustomName("§r§b§lSage Cannon §r§7(Right-Click)")->setLore([
			'§r§dRight-Click to shoot the cosmic cannon!'
		]);
		$cannon->getNamedTag()->setTag(new StringTag("hub_cannon"));
		$hidePlayers = Item::get(Item::TORCH)->setCustomName("§r§b§lHide Players §r§7(Right-Click)")->setLore([
			'§r§dRight-Click to hide players!'
		]);
		$hidePlayers->getNamedTag()->setTag(new StringTag("hub_hide"));
		$transfer = Item::get(Item::COMPASS)->setCustomName("§r§b§lSpaceship §r§7(Right-Click)")->setLore([
			'§r§dRight-Click to travel to distant planets in the universe!'
		]);
		$transfer->getNamedTag()->setTag(new StringTag("hub_transfer"));
		$shuttle = Item::get(Item::CLOCK)->setCustomName("§r§b§lI.S.S Shuttle §r§7(Right-Click)")->setLore([
			'§r§dRight-Click to travel between space stations!'
		]);
		$shuttle->getNamedTag()->setTag(new StringTag("hub_shuttle"));

		$fourm = Item::get(Item::BOOK)->setCustomName("§r§b§lfourm.sagepe.org §r§7(Right-Click)")->setLore([
			'§r§dRight-Click to read our forms!'
		]);
		$fourm->getNamedTag()->setTag(new StringTag("hub_fourms"));

		$buy = Item::get(Item::CHEST)->setCustomName("§r§b§lshop.sagepe.org §r§7(Right-Click)")->setLore([
			'§r§dRight-Click to read our forms!'
		]);
		$buy->getNamedTag()->setTag(new StringTag("hub_buy"));

		$player->getInventory()->setItem(0,$enablePvp);
		$player->getInventory()->setItem(1,$cannon);
		$player->getInventory()->setItem(3,$hidePlayers);
		$player->getInventory()->setItem(4,$transfer);
		$player->getInventory()->setItem(5,$shuttle);
		$player->getInventory()->setItem(7,$fourm);
		$player->getInventory()->setItem(8,$buy);

	}

}
