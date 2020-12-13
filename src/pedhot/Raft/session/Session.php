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
use pedhot\Raft\isle\Isle;

class Session extends iSession {
    
    /** @var Player */
    private $player;
    
    /** @var null|Isle */
    private $isle = null;
    
    /** @var string|null */
    private $lastInvitation = null;
    
    /** @var array */
    private $invitations = [];
    
    /**
     * Session constructor.
     * @param SessionManager $manager
     * @param Player $player
     */
    public function __construct(SessionManager $manager, Player $player) {
        $this->player = $player;
        parent::__construct($manager, $player->getLowerCaseName());
    }
    
    /**
     * @return Player
     */
    public function getPlayer(): Player {
        return $this->player;
    }
    
    /**
     * @return null|Isle
     */
    public function getIsle(): ?Isle {
        return $this->isle;
    }
    
    /**
     * @return bool
     */
    public function hasIsle(): bool {
        return $this->isle != null;
    }
    
    /**
     * @return OfflineSession
     */
    public function getOffline(): OfflineSession {
        return new OfflineSession($this->manager, $this->username);
    }
    
    /**
     * @return array
     */
    public function getInvitations(): array {
        return $this->invitations;
    }
    
    /**
     * @param string $senderName
     * @return null|Isle
     */
    public function getInvitation(string $senderName): ?Isle {
        return $this->invitations[$senderName] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getLastInvitation(): ?string {
        return $this->lastInvitation;
    }
    
    /**
     * @return bool
     */
    public function hasLastInvitation(): bool {
        return $this->lastInvitation != null;
    }
    
    /**
     * @param null|string $isle
     */
    public function setIsleId(?string $isle): void {
        parent::setIsleId($isle);
        if($isle != null) {
            $this->provider->loadIsle($isle);
            $this->isle = $this->manager->getPlugin()->getIsleManager()->getIsle($isle);
        }
    }
    
    /**
     * @param null|Isle $isle
     */
    public function setIsle(?Isle $isle): void {
        $lastIsle = $this->isle;
        $this->isle = $isle;
        $this->isleId = ($isle != null) ? $isle->getIdentifier() : null;
        if($isle != null) {
            $isle->addMember($this->getOffline());
        }
        if($lastIsle != null) {
            $lastIsle->updateMembers();
        }
        $this->save();
    }
    
    /**
     * @param array $invitations
     */
    public function setInvitations(array $invitations): void {
        $this->invitations = $invitations;
    }
    
    /**
     * @param string $senderName
     * @param Isle $isle
     */
    public function addInvitation(string $senderName, Isle $isle): void {
        $this->invitations[$senderName] = $isle;
        $this->lastInvitation = $senderName;
    }
    
    /**
     * @param string $senderName
     */
    public function removeInvitation(string $senderName): void {
        if(isset($this->invitations[$senderName])) {
            unset($this->invitations[$senderName]);
        }
    }
    
    /**
     * @param null|string $senderName
     */
    public function setLastInvitation(?string $senderName): void {
        $this->lastInvitation = $senderName;
    }
    
    /**
     * @param string $identifier
     * @param array $args
     * @return string
     */
    public function translate(string $identifier, array $args = []): string {
        return $this->manager->getPlugin()->getSettings()->getMessage($identifier, $args);
    }
    
    /**
     * @param string $identifier
     * @param array $args
     */
    public function sendTranslatedMessage(string $identifier, array $args = []): void {
        $this->player->sendMessage($this->translate($identifier, $args));
    }
    
    /**
     * @param string $identifier
     * @param array $args
     */
    public function sendTranslatedPopup(string $identifier, array $args = []): void {
        $this->player->sendPopup($this->translate($identifier, $args));
    }
    
    /**
     * @param string $identifier
     * @param array $args
     */
    public function sendTranslatedTip(string $identifier, array $args = []): void {
        $this->player->sendTip($this->translate($identifier, $args));
    }
    
}