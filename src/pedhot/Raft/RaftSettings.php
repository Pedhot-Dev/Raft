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


use pocketmine\item\Item;

class RaftSettings {
    
    /** @var Raft */
    private $plugin;
    
    /** @var array */
    private $data;
    
    /** @var string[] */
    private $slotsBySize = [];
    
    /** @var Item[] */
    private $defaultChest;
    
    /** @var array */
    private $chestPerGenerator;
    
    /** @var string[] */
    private $messages = [];
    
    /**
     * RaftSettings constructor.
     * @param Raft $plugin
     */
    public function __construct(Raft $plugin) {
        $this->plugin = $plugin;
        $this->refresh();
    }
    
    /**
     * @param string $size
     * @return int
     */
    public function getSlotsBySize(string $size): int {
        return $this->slotsBySize[$size] ?? 1;
    }
    
    /**
     * @return Item[]
     */
    public function getDefaultChest(): array {
        return $this->defaultChest;
    }
    
    /**
     * @param string $generator
     * @return array
     */
    public function getChestPerGenerator(string $generator): array {
        return $this->chestPerGenerator[$generator] ?? $this->defaultChest;
    }
    
    /**
     * @return string[]
     */
    public function getMessages(): array {
        return $this->messages;
    }
    
    /**
     * @param string $identifier
     * @param array $args
     * @return string
     */
    public function getMessage(string $identifier, array $args = []): string {
        $message = $this->messages[$identifier] ?? "Message ($identifier) not found";
        $message = Raft::translateColors($message);
        foreach($args as $arg => $value) {
            $message = str_replace("{" . $arg . "}", $value, $message);
        }
        return $message;
    }
    
    public function refresh(): void {
        $this->data = json_decode(file_get_contents($this->plugin->getDataFolder() . "settings.json"), true);
        $this->messages = json_decode(file_get_contents($this->plugin->getDataFolder() . "messages.json"), true);
        $this->slotsBySize = $this->data["slots-by-size"];
        $this->defaultChest = Raft::parseItems($this->data["default-chest"]);
        $this->chestPerGenerator = [];
        foreach($this->data["chest-per-generator"] as $world => $items) {
            $this->chestPerGenerator[$world] = Raft::parseItems($items);
        }
    }
    
}
