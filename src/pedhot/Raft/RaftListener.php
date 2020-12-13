<?php
/**
 *   |  _ \ __ _ / _| |_
 *   | |_) / _` | |_| __|
 *   |  _ < (_| |  _| |_
 *   |_| \_\__,_|_|  \__|
 *
 *   Copyright 2020 PedhotDev
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 */

namespace pedhot\Raft;

use pocketmine\block\Solid;
use pocketmine\entity\object\Painting;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pedhot\Raft\generator\IsleGenerator;
use pedhot\Raft\isle\IsleManager;
use pedhot\Raft\session\Session;
use pedhot\Raft\session\SessionManager;

class RaftListener implements Listener {

    /** @var Raft */
    private $plugin;
    
    /** @var SessionManager */
    private $sessionManager;
    
    /** @var IsleManager */
    private $isleManager;

    /**
     * RaftListener constructor.
     *
     * @param Raft $plugin
     */
    public function __construct(Raft $plugin) {
        $this->plugin = $plugin;
        $this->sessionManager = $plugin->getSessionManager();
        $this->isleManager = $plugin->getIsleManager();
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }
    
    /**
     * @param Player $player
     * @return Session
     */
    public function getSession(Player $player): Session {
        return $this->plugin->getSessionManager()->getSession($player);
    }
    
    /**
     * @param ChunkLoadEvent $event
     */
    public function onChunkLoad(ChunkLoadEvent $event): void {
        $level = $event->getLevel();
        $isle = $this->plugin->getIsleManager()->getIsle($level->getName());
        if($isle == null) {
            return;
        }
        $generator = $this->plugin->getGeneratorManager()->getGenerator($type = $isle->getType());
        /** @var IsleGenerator $generator */
        $position = $generator::getChestPosition();
        if($level->getChunk($position->x >> 4, $position->z >> 4) === $event->getChunk() and $event->isNewChunk()) {
            /** @var Chest $chest */
            $chest = Tile::createTile(Tile::CHEST, $level, Chest::createNBT($position));
            foreach($this->plugin->getSettings()->getChestPerGenerator($type) as $item) {
                $chest->getInventory()->addItem($item);
            }
        }
    }
    
    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $session = $this->getSession($player);
        $isle = $this->isleManager->getIsle($player->getLevel()->getName());
        if($isle != null) {
            if(!$isle->canInteract($session)) {
                $session->sendTranslatedPopup("MUST_ME_MEMBER");
                $event->setCancelled();
            } elseif(!($event->isCancelled()) and $event->getBlock() instanceof Solid) {
                $isle->destroyBlock();
            }
        }
    }
    
    /**
     * @param BlockPlaceEvent $event
     */
    public function onPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $session = $this->getSession($player);
        $isle = $this->isleManager->getIsle($player->getLevel()->getName());
        if($isle != null) {
            if(!$isle->canInteract($session)) {
                $session->sendTranslatedPopup("MUST_ME_MEMBER");
                $event->setCancelled();
            } elseif(!($event->isCancelled()) and $event->getBlock() instanceof Solid) {
                $isle->addBlock();
            }
        }
    }
    
    /**
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $session = $this->getSession($player);
        $isle = $this->plugin->getIsleManager()->getIsle($player->getLevel()->getName());
        if($isle != null and !($isle->canInteract($session))) {
            $session->sendTranslatedPopup("MUST_ME_MEMBER");
            $event->setCancelled();
        }
    }
    
    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event): void {
        $sessionManager = $this->plugin->getSessionManager();
        $session = $sessionManager->getSession($event->getPlayer());
        if(!($session->hasIsle()) or !($session->isInChat())) {
            return;
        }
        $recipients = [];
        foreach($sessionManager->getSessions() as $userSession) {
            if($userSession->isInChat() and $userSession->getIsle() === $session->getIsle()) {
                $recipients[] = $userSession->getPlayer();
            }
        }
        $event->setRecipients($recipients);
    }
    
    /**
     * @param EntityDamageEvent $event
     */
    public function onHurt(EntityDamageEvent $event): void {
        if($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            $isle = $this->isleManager->getIsle($entity->getLevel()->getName());
            if($isle != null and ($entity instanceof Player or ($entity instanceof Painting and $damager instanceof Player
                and !$isle->canInteract($this->getSession($damager))))) {
                $event->setCancelled();
            }
        }
    }
    
    
    /**
     * @param LevelUnloadEvent $event
     */
    public function onUnloadLevel(LevelUnloadEvent $event): void {
        foreach($event->getLevel()->getPlayers() as $player) {
            $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
        }
    }
    
    /**
     * @param PlayerQuitEvent $event
     * @priority LOWEST
     */
    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $session = $this->getSession($player);
        $isleManager = $this->plugin->getIsleManager();
        foreach($isleManager->getIsles() as $isle) {
            if($isle->isCooperator($session)) {
                $isle->removeCooperator($session);
            }
        }
        $isle = $isleManager->getIsle($player->getLevel()->getName());
        if($isle != null) {
            $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
            $isle->tryToClose();
        }
    }

}
