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

namespace pedhot\Raft\isle;


use pocketmine\level\Level;
use pedhot\Raft\event\isle\IsleCreateEvent;
use pedhot\Raft\event\isle\IsleDisbandEvent;
use pedhot\Raft\event\isle\IsleOpenEvent;
use pedhot\Raft\event\isle\IsleCloseEvent;
use pedhot\Raft\generator\IsleGenerator;
use pedhot\Raft\session\iSession;
use pedhot\Raft\session\Session;
use pedhot\Raft\Raft;
use pocketmine\Player;

class IsleManager {
    
    /** @var Raft */
    private $plugin;
    
    /** @var Isle[] */
    private $isles = [];
    
    /**
     * IsleManager constructor.
     * @param Raft $plugin
     */
    public function __construct(Raft $plugin) {
        $this->plugin = $plugin;
    }
    
    /**
     * @return Raft
     */
    public function getPlugin(): Raft {
        return $this->plugin;
    }
    
    /**
     * @return Isle[]
     */
    public function getIsles(): array {
        return $this->isles;
    }
    
    /**
     * @param string $identifier
     * @return null|Isle
     */
    public function getIsle(string $identifier): ?Isle {
        return $this->isles[$identifier] ?? null;
    }
    
    /**
     * @param Session $session
     * @param string $type
     */
    public function createIsleFor(Session $session, string $type, Player $player): void {
        $identifier = Raft::generateUniqueId($player);
        
        $generatorManager = $this->plugin->getGeneratorManager();
        if($generatorManager->isGenerator($type)) {
            $generator = $generatorManager->getGenerator($type);
        } else {
            $generator = $generatorManager->getGenerator("Basic");
        }
    
        $server = $this->plugin->getServer();
        $server->generateLevel($identifier, null, $generator);
        $server->loadLevel($identifier);
        $level = $server->getLevelByName($identifier);
        /** @var IsleGenerator $generator */
        $level->setSpawnLocation($generator::getWorldSpawn());
        
        $this->openIsle($identifier, [$session->getOffline()], true, $type, $level, 0);
        $session->setIsle($isle = $this->isles[$identifier]);
        $session->setRank(iSession::RANK_FOUNDER);
        $session->save();
        $isle->save();
        $server->getPluginManager()->callEvent(new IsleCreateEvent($isle));
    }
    
    /**
     * @param Isle $isle
     */
    public function disbandIsle(Isle $isle): void {
        foreach($isle->getLevel()->getPlayers() as $player) {
            $player->teleport($player->getServer()->getDefaultLevel()->getSpawnLocation());
        }
        foreach($isle->getMembers() as $offlineMember) {
            $onlineSession = $offlineMember->getSession();
            if($onlineSession != null) {
                $onlineSession->setIsle(null);
                $onlineSession->setRank(Session::RANK_DEFAULT);
                $onlineSession->save();
                $onlineSession->sendTranslatedMessage("ISLE_DISBANDED");
            } else {
                $offlineMember->setIsleId(null);
                $offlineMember->setRank(Session::RANK_DEFAULT);
                $offlineMember->save();
            }
        }
        $isle->setMembers([]);
        $isle->save();
        $this->closeIsle($isle);
        $this->plugin->getServer()->getPluginManager()->callEvent(new IsleDisbandEvent($isle));
    }
    
    /**
     * @param string $identifier
     * @param array $members
     * @param bool $locked
     * @param string $type
     * @param Level $level
     * @param int $blocksBuilt
     */
    public function openIsle(string $identifier, array $members, bool $locked, string $type, Level $level, int $blocksBuilt): void {
        $this->isles[$identifier] = new Isle($this, $identifier, $members, $locked, $type, $level, $blocksBuilt);
        $this->plugin->getServer()->getPluginManager()->callEvent(new IsleOpenEvent($this->isles[$identifier]));
    }
    
    /**
     * @param Isle $isle
     */
    public function closeIsle(Isle $isle): void {
        $isle->save();
        $server = $this->plugin->getServer();
        $server->getPluginManager()->callEvent(new IsleCloseEvent($isle));
        $server->unloadLevel($isle->getLevel());
        unset($this->isles[$isle->getIdentifier()]);
    }
    
}