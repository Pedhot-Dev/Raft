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


use pedhot\Raft\provider\Provider;

abstract class iSession {
    
    /** @var SessionManager */
    protected $manager;
    
    /** @var Provider */
    protected $provider;
    
    /** @var string */
    protected $username;
    
    /** @var string|null */
    protected $isleId = null;
    
    /** @var bool */
    protected $inChat = false;
    
    /** @var int */
    protected $rank = false;
    
    const RANK_DEFAULT = 0;
    const RANK_OFFICER = 1;
    const RANK_LEADER = 2;
    const RANK_FOUNDER = 3;
    
    /**
     * iSession constructor.
     * @param SessionManager $manager
     * @param string $username
     */
    public function __construct(SessionManager $manager, string $username) {
        $this->manager = $manager;
        $this->username = $username;
        $this->provider = $manager->getPlugin()->getProvider();
        $this->provider->loadSession($this);
    }
    
    /**
     * @return string
     */
    public function getUsername(): string {
        return $this->username;
    }
    
    /**
     * @return null|string
     */
    public function getIsleId(): ?string {
        return $this->isleId;
    }
    
    /**
     * @return bool
     */
    public function isInChat(): bool {
        return $this->inChat;
    }
    
    /**
     * @return int
     */
    public function getRank(): int {
        return $this->rank;
    }
    
    /**
     * @param null|string $isle
     */
    public function setIsleId(?string $isle): void {
        $this->isleId = $isle;
    }
    
    /**
     * @param bool $inChat
     */
    public function setInChat(bool $inChat = true): void {
        $this->inChat = $inChat;
    }
    
    /**
     * @param int $rank
     */
    public function setRank(int $rank = self::RANK_DEFAULT): void {
        $this->rank = $rank;
    }
    
    public function save(): void {
        $this->provider->saveSession($this);
    }
    
}
