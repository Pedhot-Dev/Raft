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

namespace pedhot\Raft\provider\json;


use pocketmine\utils\Config;
use pedhot\Raft\isle\Isle;
use pedhot\Raft\provider\Provider;
use pedhot\Raft\session\iSession;
use pedhot\Raft\session\Session;

class JSONProvider extends Provider {
    
    public function initialize(): void {
        $dataFolder = $this->plugin->getDataFolder();
        if(!is_dir($dataFolder . "isles")) {
            mkdir($dataFolder . "isles");
        }
        if(!is_dir($dataFolder . "users")) {
            mkdir($dataFolder . "users");
        }
    }
    
    /**
     * @param string $username
     * @return Config
     */
    private function getUserConfig(string $username): Config {
        return new Config($this->plugin->getDataFolder() . "users/$username.json", Config::JSON, [
                "isle" => null,
                "rank" => Session::RANK_DEFAULT
            ]);
    }
    
    /**
     * @param string $isleId
     * @return Config
     */
    private function getIsleConfig(string $isleId): Config {
        return new Config($this->plugin->getDataFolder() . "isles/$isleId.json", Config::JSON);
    }
    
    /**
     * @param iSession $session
     */
    public function loadSession(iSession $session): void {
        $config = $this->getUserConfig($session->getUsername());
        $session->setIsleId($config->get("isle"));
        $session->setRank($config->get("rank"));
    }
    
    /**
     * @param iSession $session
     */
    public function saveSession(iSession $session): void {
        $config = $this->getUserConfig($session->getUsername());
        $config->set("isle", $session->getIsleId());
        $config->set("rank", $session->getRank());
        $config->save();
    }
    
    /**
     * @param string $identifier
     */
    public function loadIsle(string $identifier): void {
        if($this->plugin->getIsleManager()->getIsle($identifier) != null) {
            return;
        }
        $config = $this->getIsleConfig($identifier);
        $server = $this->plugin->getServer();
        if(!$server->isLevelLoaded($identifier)) {
            $server->loadLevel($identifier);
        }
        
        $members = [];
        foreach($config->get("members", []) as $username) {
            $members[] = $this->plugin->getSessionManager()->getOfflineSession($username);
        }
        
        $this->plugin->getIsleManager()->openIsle(
            $identifier,
            $members,
            $config->get("locked"),
            $config->get("type"),
            $server->getLevelByName($identifier),
            $config->get("blocks")
        );
    }
    
    /**
     * @param Isle $isle
     */
    public function saveIsle(Isle $isle): void {
        $config = $this->getIsleConfig($isle->getIdentifier());
        $config->set("identifier", $isle->getIdentifier());
        $config->set("locked", $isle->isLocked());
        $config->set("type", $isle->getType());
        $config->set("blocks", $isle->getBlocksBuilt());
        
        $members = [];
        foreach($isle->getMembers() as $member) {
            $members[] = $member->getUsername();
        }
        $config->set("members", $members);
        
        $config->save();
    }
    
}
