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

namespace pedhot\Raft\provider;


use pedhot\Raft\isle\Isle;
use pedhot\Raft\session\iSession;
use pedhot\Raft\Raft;

abstract class Provider {
    
    /** @var Raft */
    protected $plugin;
    
    /**
     * Provider constructor.
     * @param Raft $plugin
     */
    public function __construct(Raft $plugin) {
        $this->plugin = $plugin;
        $this->initialize();
    }
    
    public abstract function initialize(): void;
    
    /**
     * @param iSession $session
     */
    public abstract function loadSession(iSession $session) : void;
    
    /**
     * @param iSession $session
     */
    public abstract function saveSession(iSession $session): void;
    
    /**
     * @param string $identifier
     */
    public abstract function loadIsle(string $identifier): void;
    
    /**
     * @param Isle $isle
     */
    public abstract function saveIsle(Isle $isle): void;
    
}