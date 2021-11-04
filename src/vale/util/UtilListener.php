<?php
namespace vale\util;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\level\Position;
use pocketmine\Server;
use vale\text\TextBase;
use vale\util\Util;
use vale\Hub;
use vale\HubPlayer;

class UtilListener implements Listener
{

	public $plugin;

	/** @var array $pvpOn */
	public array $pvpOn = [];

	public array $chat = [];


	public function __construct(Hub $plugin)
	{
		$this->plugin = $plugin;
		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	/**
	 * @param PlayerCreationEvent $event
	 */
	public function onCreation(PlayerCreationEvent $event)
	{
		$event->setPlayerClass(HubPlayer::class);
	}

	public function onDeath(PlayerDeathEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player instanceof HubPlayer) {
			return;
		}
		$event->setDrops([]);
		if (isset(Util::$pvpEnabled[$player->getName()])) {
			unset(Util::$pvpEnabled[$player->getName()]);
		}
		$player = $event->getPlayer();
		if ($player instanceof HubPlayer) {
			$cause = $player->getLastDamageCause();
			if ($cause instanceof EntityDamageByEntityEvent) {
				if ($cause->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
					$cause = $cause->getDamager();
					if ($cause instanceof HubPlayer) {
						$name = $player->getName();
						$causen = $cause->getName();
						$event->setDeathMessage("§r§b{$name} §r§f§lX §r§d{$causen}");
						Hub::getStatsProvider()->addKills($causen,1);
						Util::sendHubItems($player);
					}
				}
			}
		}
	}


	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event)
	{
		$entity = $event->getEntity();
		if (!$entity instanceof HubPlayer) {
			return;
		}
		if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
			$event->setCancelled(true);
		}
		if ($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
			$event->setCancelled(true);
		}
		if (!isset(Util::$pvpEnabled[$entity->getName()])) {
			$event->setCancelled();
		}
		if ($event instanceof EntityDamageByEntityEvent) {
			$entity = $event->getEntity();
			$damager = $event->getDamager();
			if ($damager instanceof HubPlayer && $entity instanceof HubPlayer) {
				if (!isset(Util::$pvpEnabled[$entity->getName()]) && !isset(Util::$pvpEnabled[$damager->getName()])) {
					$event->setCancelled();
				}

				if (!isset(Util::$pvpEnabled[$damager->getName()])) {
					$event->setCancelled(true);
				}

				if (isset(Util::$pvpEnabled[$damager->getName()]) && !isset(Util::$pvpEnabled[$entity->getName()])) {
					$event->setCancelled();
				}
				if (isset(Util::$pvpEnabled[$damager->getName()]) && isset(Util::$pvpEnabled[$entity->getName()])) {
					if (!$event->isCancelled()) {
						$event->setCancelled(false);
					}
				}
			}
		}
	}


	public function onMove(PlayerMoveEvent $event)
	{
		$p = $event->getPlayer();
		if (!$p instanceof HubPlayer) {
			return;
		}
		if ($p->getY() <= 25) {
			$p->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
		}
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		if ($player instanceof HubPlayer) {
			if(!$player->hasPlayedBefore()){
			  $player->createCustomData();
			  $player->loadData();
			  Hub::getStatsProvider()->setKills($player->getName(),0);
			} else {
				$player->loadData();
			}
			TextBase::start($player);
			$event->setJoinMessage("");
			$player->sendMessage("§8      §6");
			$player->sendMessage("§8       §6");
			$player->sendMessage("§8      §c§lSage §r§4 ~ Demonic Realm  ~   ");
			$player->sendMessage("§8  §r§fWelcome §r§4{$name} §r§fto Map §f#1-BETA");
			$player->sendMessage("§8 §b");
			$player->sendMessage("    §l§cSHOP: §r§fhttps://bit.ly/sagebuycraft");
			$player->sendMessage("     §l§4DISCORD: §r§fdiscord.gg/sagehcf");
			$player->sendMessage("      §l§cRULES: §r§fTODO");
			$player->sendMessage("        §l§4VOTE: §r§fbit.ly/sagevotemcpe");
			$player->sendMessage("  §r§c/sync §r§c- §r§fSync your minecraft account with our discord.  ");
			$player->sendMessage(" §r§d/nitro §r§d- §r§fLearn about boosting our discord for in-game perks.  ");
			$player->sendMessage("§r§b/ranks §r§b- §r§fLearn about the ranks on the demonic realm.  ");
			$player->sendMessage("§8       §6");
			$player->sendMessage("§8       §6");
			$player->sendMessage("§8       §6");
			$naseua = new EffectInstance(Effect::getEffect(Effect::NAUSEA), 20 * 15);
			$player->addEffect($naseua);
			$player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
			Util::sendHubItems($player);
		}
	}


	/**
	 * @param PlayerJumpEvent $event
	 */
	public function onJump(PlayerJumpEvent $event)
	{
		$player = $event->getPlayer();
		if ($player instanceof HubPlayer) {
			$player->knockBack($player, 0, $player->getDirectionVector()->getX(), $player->getDirectionVector()->getZ(), 1);

		}
	}

	public function onInventoryTransaction(InventoryTransactionEvent $event){
		$transaction = $event->getTransaction();
		$player = $transaction->getSource();
		$action = $transaction->getActions();
		$event->setCancelled(true);
		if($player instanceof HubPlayer) {
			if ($action instanceof SlotChangeAction) {
				$event->setCancelled();
			}
		}
	}

	public function onDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		if($player instanceof HubPlayer){
			$event->setCancelled(true);
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBlockBreak(BlockBreakEvent $event)
	{
		$p = $event->getPlayer();
		if ($p instanceof HubPlayer) {
			if (!$p->isOp()) {
				$event->setCancelled(true);
			}
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBlockPLace(BlockPlaceEvent $event)
	{
		$p = $event->getPlayer();
		if ($p instanceof HubPlayer) {
			if (!$p->isOp()) {
				$event->setCancelled(true);
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event)
	{
		$player = $event->getPlayer();
		if ($player instanceof HubPlayer) {
			$player->saveData();
			$event->setQuitMessage("");
			if (Hub::getQueueManager()->isInQueue($player->getName(), "player")) {
				Hub::getQueueManager()->removeFromQueue($player->getName(), "player");
			}
			var_dump($event->getPlayer()->getName());
		}
	}

	/**
	 * @param PlayerRespawnEvent $event
	 */
	public function onRespawn(PlayerRespawnEvent $event)
	{
		$p = $event->getPlayer();
		if ($p instanceof HubPlayer)
			Util::sendHubItems($p);
	}

	public function onExhaust(PlayerExhaustEvent $event){
		$event->setCancelled(true);
	}

	public function onChatRank(PlayerChatEvent $event)
	{
		if ($event->getPlayer() instanceof HubPlayer) {
			$player = $event->getPlayer();
			$delay = 5;
			if (isset($this->chat[$player->getName()]) && microtime(true) - $this->chat[$player->getName()] <= $delay) {
				$delayMessage = round($delay - abs($this->chat[$player->getName()] - microtime(true)), 2);
				$player->sendMessage("§r§c§l(§c!§c§l)" . " §r§cYou cannot send another message for " . $delayMessage . "ms!");
				$player->sendMessage("§r§7Purchase a rank at §r§cshop.sagepe.com §r§7to reduce the delay between chat messages!");
				$event->setCancelled(true);
			} else {
				$this->chat[$player->getName()] = microtime(true);
				$rank = $player->getRank();
				$name = $player->getName();
				$chatrank = TF::GRAY . $player->getName() . TF::WHITE . ": " . TF::GRAY;
				$tag = "§r§f#2022";
				if($rank == "Raven"){
					$chatrank =  "§r§f§l<§r§e§lVIP+++§r§f§l> §r§e{$name}§r§f:§7 ";
				} elseif($rank == "Famous"){
					$chatrank = "§r§8[§r§d§oFamous§r§8]§r§d " . $player->getName() . " §r§8[§r§e#§f". $tag."§r§8]"  . "§r§f: ";
				} elseif($rank == "Booster"){
					$chatrank = "§r§8[§d§lNitro§r§d-Booster§r§8]§r§d " . $player->getName() . " §r§8[§r§e#§f". $tag."§r§8]"  . "§r§f: ";
				} elseif($rank == "Admin"){
					$chatrank = "§r§8[§4§lAdmin§r§8]§r§4 " . $player->getName() . " §r§8[§r§e#§f". $tag."§r§8]"  . "§r§f:§r§4 ";
				} elseif($rank == "Aegis"){
					$chatrank =  "§r§f§l<§r§b§lVIP++++§r§f§l> §r§b{$name}§r§f:§7 ";
				}elseif ($rank == "Mod"){
					$chatrank = "§r§8[§l§5Mod§r§8] §r§5" . $player->getName() . " §r§8[§r§e#§f". $tag."§r§8]"  . "§r§f:§r§d ";
				} elseif($rank == "Partner"){
					$chatrank = "§r§8[§r§d§lPartner§r§8]§r§d " . $player->getName() . " §r§8[§r§e#§f". $tag."§r§8]"  . "§r§f: ";
				} elseif($rank == "Cupid"){
					$chatrank =  "§r§f§l<§r§7VIP§r§f§l> §r§7{$name}§r§7:§r§f ";
				} elseif($rank == "Sage") {
					$chatrank =  "§r§f§l<§r§6VIP+++++§r§f§l> §r§6{$name}§r§f: ";
				}elseif ($rank == "Trial"){
					$chatrank = "§r§8§l[§r§cTrial-Mod§r§8§l]§r§c§l " . $player->getName() . " §r§8[§r§e#§f". $tag."§r§8]"  . "§r§f:§r§c ";
				} elseif($rank == "Media"){
					$chatrank = "§r§8[§r§dMedia§r§8]§r§d " . $player->getName() . " §r§8[§r§e#§f". $tag."§r§8]"  . "§r§f: ";
				} else {
					$chatrank = "§r§8[§r§fTrainee§r§8]§r§f " . $player->getName() . "§r§f: ";
				}
					$event->setFormat($chatrank . $event->getMessage());
				}
			}
		}


	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player instanceof HubPlayer) {
			return;
		}
		$item = $event->getItem();
		$namedTag = $item->getNamedTag();
		$action = $event->getAction();


		if ($namedTag->hasTag("hub_cannon") && $action === PlayerInteractEvent::RIGHT_CLICK_AIR){
			$player->sendMessage("§r§c§l(§c!§c§l) §r§cYou do not have permission use the Sage-Cannon!");
			$player->sendMessage("§r§7Purchase a rank on shop.sagepe.com to unlock this feature!");
			$event->setCancelled();
		}


		if ($namedTag->hasTag("hub_fourms") && $action === PlayerInteractEvent::RIGHT_CLICK_AIR){
			$player->sendMessage("§r§l§4Sage Fourms");
			$player->sendMessage("§r§fhttps://bit.ly/sagefourms");
			$player->sendMessage("§r§7Visit our fourms to learn more about us!");
			$event->setCancelled();
		}

		if ($namedTag->hasTag("hub_buy") && $action === PlayerInteractEvent::RIGHT_CLICK_AIR){
			$player->sendMessage("§r§l§4Sage Store");
			$player->sendMessage("§r§fhttps://bit.ly/sagebuycraft");
			$player->sendMessage("§r§7Purchase a rank on shop.sagepe.com to unlock more features!");
          $event->setCancelled();
		}

		if ($namedTag->hasTag("hub_transfer") && $action === PlayerInteractEvent::RIGHT_CLICK_AIR){
			Util::sendTransferMeu($player);
			$event->setCancelled();
		}

		if($namedTag->hasTag("hub_hide")){
			$player->sendMessage("§r§c§l(!)" ." §r§cPlayers are now hidden!");
			foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer){
				$player->hidePlayer($onlinePlayer);
				$onlinePlayer->hidePlayer($player);
			}
			Util::sendBar($player, "show");
			Util::playSound($player, "step.nylium",1,1,1);
		}

		if($namedTag->hasTag("hub_show")){
			$player->sendMessage("§r§a§l(!)" ." §r§aPlayers are now visible!");
			foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer){
				$player->showPlayer($onlinePlayer);
				$onlinePlayer->showPlayer($player);
			}
			Util::sendHubItems($player);
			Util::playSound($player, "step.nylium",1,1,1);
		}
		if ($namedTag->hasTag("hub_pvp") && $action === PlayerInteractEvent::RIGHT_CLICK_AIR) {
			$delay = 10;
			if (isset($this->pvpOn[$player->getName()]) && microtime(true) - $this->pvpOn[$player->getName()] <= $delay) {
				$delayMessage = round($delay - abs($this->pvpOn[$player->getName()] - microtime(true)), 2);
				$player->sendMessage("§r§c§l(§c!§c§l)" . " §r§cYou cannot use this for another " . $delayMessage . "ms!");
				$event->setCancelled(true);
			} else {
				Util::playSound($player, "mob.enderdragon.growl", 1, 1, 1);
				Util::sendBar($player,"pvp");
				$this->pvpOn[$player->getName()] = microtime(true);
			}
		}
			if ($namedTag->hasTag("pvpoff") && $action === PlayerInteractEvent::RIGHT_CLICK_AIR) {
				$delay = 8;
				if (isset($this->pvpOn[$player->getName()]) && microtime(true) - $this->pvpOn[$player->getName()] <= $delay) {
					$delayMessage = round($delay - abs($this->pvpOn[$player->getName()] - microtime(true)), 2);
					$player->sendMessage("§r§c§l(§c!§c§l)" . " §r§cYou cannot use this for another " . $delayMessage . "ms!");
					$event->setCancelled(true);
				} else {
					Util::playSound($player, "mob.cat.meow", 1, 1, 1);
					$player->sendMessage("§r§c§l(!) PvP §r§chas been disabled.");
					$player->sendMessage("§r§c                        ――――――――");
					$player->sendMessage("§r§7Use §r§c/pvp top §r§7to view the highest active killstreaks!");
					Util::sendHubItems($player);
					unset(Util::$pvpEnabled[$player->getName()]);
				}
			}
		}
}
