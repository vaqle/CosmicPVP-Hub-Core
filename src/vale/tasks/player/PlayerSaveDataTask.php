<?php
declare(strict_types=1);
namespace vale\tasks\player;

use pocketmine\Server;
use vale\Hub;


use pocketmine\scheduler\Task;
use vale\HubPlayer;

class PlayerSaveDataTask extends Task {
    /** @var Hub $plugin */
    private $plugin;

    /**
     * PlayerSaveDataTask constructor.
     *
     * @param Hub $plugin
     */
    public function __construct(Hub $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     * @return void
     */
    public function onRun(int $currentTick) {
        foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            if ($onlinePlayer instanceof HubPlayer) {
                if ($onlinePlayer->isRegistered("player")) {
                    $onlinePlayer->loadData();
                  
                }
            }
        }
    }
}