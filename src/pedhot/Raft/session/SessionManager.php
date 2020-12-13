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

namespace pedhot\Raft\session;


use pocketmine\Player;
use pedhot\Raft\event\session\SessionCloseEvent;
use pedhot\Raft\event\session\SessionOpenEvent;
use pedhot\Raft\Raft;

class SessionManager {
    
    /** @var Raft */
    private $plugin;
    
    /** @var Session[] */
    private $sessions = [];
    
    /**
     * SessionManager constructor.
     * @param Raft $plugin
     */
    public function __construct(Raft $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents(new SessionListener($this), $plugin);
    }
    
    /**
     * @return Raft
     */
    public function getPlugin(): Raft {
        return $this->plugin;
    }
    
    /**
     * @return Session[]
     */
    public function getSessions(): array {
        return $this->sessions;
    }
    
    /**
     * @param string $username
     * @return null|OfflineSession
     */
    public function getOfflineSession(string $username): ?OfflineSession {
        return new OfflineSession($this, $username);
    }
    
    /**
     * @param Player $player
     * @return null|Session
     */
    public function getSession(Player $player): ?Session {
        return $this->sessions[$player->getName()] ?? null;
    }
    
    /**
     * @param Player $player
     */
    public function openSession(Player $player): void {
        $this->sessions[$username = $player->getName()] = new Session($this, $player);
        $this->plugin->getServer()->getPluginManager()->callEvent(new SessionOpenEvent($this->sessions[$username]));
    }
    
    /**
     * @param Player $player
     */
    public function closeSession(Player $player): void {
        if(isset($this->sessions[$username = $player->getName()])) {
            $session = $this->sessions[$username];
            $session->save();
            $this->plugin->getServer()->getPluginManager()->callEvent(new SessionCloseEvent($session));
            unset($this->sessions[$username]);
            if($session->hasIsle()) {
                $session->getIsle()->tryToClose();
            }
        }
    }
    
}