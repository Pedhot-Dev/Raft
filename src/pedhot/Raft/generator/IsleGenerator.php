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


use pocketmine\level\generator\Generator;
use pocketmine\math\Vector3;

abstract class IsleGenerator extends Generator {
    
    /** @var array */
    protected $settings;
    
    /**
     * IsleGenerator constructor.
     * @param array $settings
     */
    public function __construct(array $settings = []) {
        $this->settings = $settings;
    }
    
    /**
     * @return array
     */
    public function getSettings(): array {
        return $this->settings;
    }
    
    /**
     * @return Vector3
     */
    public abstract static function getWorldSpawn(): Vector3;
    
    /**
     * @return Vector3
     */
    public abstract static function getChestPosition(): Vector3;
    
}