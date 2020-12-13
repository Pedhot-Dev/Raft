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

namespace pedhot\Raft\command;

use pedhot\Raft\command\IsleCommandMap;
use pedhot\Raft\session\iSession;
use pedhot\Raft\session\Session;

abstract class IsleCommand {
    
    /** @var string */
    private $name;
    
    /** @var array */
    private $aliases = [];
    
    /** @var string */
    private $usageMessageId;
    
    /** @var string */
    private $descriptionMessageId;
    
    /**
     * IsleCommand constructor.
     * @param array $aliases
     * @param string $usageMessageId
     * @param string $descriptionMessageId
     */
    public function __construct(array $aliases, string $usageMessageId, string $descriptionMessageId) {
        $this->aliases = array_map("strtolower", $aliases);
        $this->name = array_shift($this->aliases);
        $this->usageMessageId = $usageMessageId;
        $this->descriptionMessageId = $descriptionMessageId;
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return array
     */
    public function getAliases(): array {
        return $this->aliases;
    }
    
    /**
     * @return string
     */
    public function getUsageMessageId(): string {
        return $this->usageMessageId;
    }
    
    /**
     * @return string
     */
    public function getDescriptionMessageId(): string {
        return $this->descriptionMessageId;
    }
    
    /**
     * @param Session $session
     * @return bool
     */
    public function checkIsle(Session $session): bool {
        if($session->hasIsle()) {
            return false;
        }
        $session->sendTranslatedMessage("NEED_ISLE");
        return true;
    }
    
    /**
     * @param Session $session
     * @return bool
     */
    public function checkFounder(Session $session): bool {
        if($this->checkIsle($session)) {
            return true;
        } elseif($session->getRank() == iSession::RANK_FOUNDER) {
            return false;
        }
        $session->sendTranslatedMessage("MUST_BE_FOUNDER");
        return true;
    }
    
    /**
     * @param Session $session
     * @return bool
     */
    public function checkLeader(Session $session): bool {
        if($this->checkIsle($session)) {
            return true;
        } elseif($session->getRank() == iSession::RANK_FOUNDER or $session->getRank() == iSession::RANK_LEADER) {
            return false;
        }
        $session->sendTranslatedMessage("MUST_BE_LEADER");
        return true;
    }
    
    /**
     * @param Session $session
     * @return bool
     */
    public function checkOfficer(Session $session): bool {
        if($this->checkIsle($session)) {
            return true;
        } elseif($session->getRank() != iSession::RANK_DEFAULT) {
            return false;
        }
        $session->sendTranslatedMessage("MUST_BE_OFFICER");
        return true;
    }
    
    /**
     * @param null|Session $session
     * @param null|Session $ySession
     * @return bool
     */
    public function checkClone(?Session $session, ?Session $ySession): bool {
        if($session === $ySession) {
            $session->sendTranslatedMessage("CANT_BE_YOURSELF");
            return true;
        }
        return false;
    }
    
    /**
     * @param Session $session
     * @param array $args
     * @return void
     */
    public abstract function onCommand(Session $session, array $args): void;
    
}
