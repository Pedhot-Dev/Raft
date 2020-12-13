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


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class SessionListener implements Listener {
    
    /** @var SessionManager */
    private $manager;
    
    /**
     * SessionListener constructor.
     * @param SessionManager $manager
     */
    public function __construct(SessionManager $manager) {
        $this->manager = $manager;
    }
    
    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event): void {
        $this->manager->openSession($event->getPlayer());
    }
    
    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void {
        $this->manager->closeSession($event->getPlayer());
    }
    
}