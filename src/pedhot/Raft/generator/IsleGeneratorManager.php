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

namespace pedhot\Raft\generator;

use pedhot\Raft\generator\presets\SkyGridIsland;
use pocketmine\level\generator\GeneratorManager as GManager;
use pedhot\Raft\generator\presets\BasicIsland;
use pedhot\Raft\generator\presets\LostIsland;
use pedhot\Raft\generator\presets\PalmIsland;
use pedhot\Raft\generator\presets\ShellyGenerator;
use pedhot\Raft\Raft;

class IsleGeneratorManager {

    /** @var Raft */
    private $plugin;

    /** @var string[] */
    private $generators = [
        "basic" => BasicIsland::class,
        "shelly" => ShellyGenerator::class,
        "palm" => PalmIsland::class,
        "lost" => LostIsland::class
    ];
    
    /**
     * GeneratorManager constructor.
     * @param Raft $plugin
     */
    public function __construct(Raft $plugin) {
        $this->plugin = $plugin;
        foreach($this->generators as $name => $class) {
            GManager::addGenerator($class, $name);
        }
    }
    
    /**
     * @return string[]
     */
    public function getGenerators(): array {
        return $this->generators;
    }
    
    /**
     * @param string $name
     * @return null|string
     */
    public function getGenerator(string $name): ?string {
        return $this->generators[strtolower($name)] ?? null;
    }

    /**
     * Return if a generator exists
     *
     * @param string $name
     * @return bool
     */
    public function isGenerator(string $name): bool {
        return isset($this->generators[strtolower($name)]);
    }
    
    /**
     * @param string $name
     * @param string $class
     */
    public function registerGenerator(string $name, string $class): void {
        GManager::addGenerator($class, $name);
        if(isset($this->generators[$name])) {
            $this->plugin->getLogger()->debug("Overwriting generator: $name");
        }
        $this->generators[$name] = $class;
    }

}