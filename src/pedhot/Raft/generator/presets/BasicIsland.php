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

namespace pedhot\Raft\generator\presets;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pedhot\Raft\generator\IsleGenerator;

class BasicIsland extends IsleGenerator {

    /**
     * @return string
     */
    public function getName(): string {
        return "Basic";
    }

    /**
     * @param int $chunkX
     * @param int $chunkZ
     */
    public function generateChunk(int $chunkX, int $chunkZ) : void {
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        $chunk->setGenerated();
        for($Z = 0; $Z < 16; ++$Z){
            for($X = 0; $X < 16; ++$X){
                $chunk->setBlock($X, 0, $Z, Block::BEDROCK);
                for ($y = 1; $y < 65; ++$y) {
                    $chunk->setBlock($X, $y, $Z, Block::WATER);
                }}}
        if($chunkX == 0 && $chunkZ == 0) {
            for($x = 4; $x < 11; $x++){
                for($z = 4; $z < 11; $z++){
                    //$chunk->setBlock($x, 64, $z, Block::GRASS);
                }
            }
            for($x = 4; $x < 11; $x++){
                for($z = 4; $z < 11; $z++){
                    //$chunk->setBlock($x, 63, $z, Block::GRASS);
                }
            }
            for($x = 5; $x < 10; $x++){
                for($z = 5; $z < 10; $z++){
                    //$chunk->setBlock($x, 63, $z, Block::DIRT);
                    //$chunk->setBlock($x, 68, $z, Block::LEAVES); // 68
                }
            }
            for($x = 6; $x < 9; $x++){
                for($z = 6; $z < 9; $z++){
                    //$chunk->setBlock($x, 69, $z, Block::LEAVES); // 69
                    //$chunk->setBlock($x, 62, $z, Block::DIRT); // 62
                }
            }
            $chunk->setBlock(7, 65, 7, Block::CHEST);
            $chunk->setBlock(5, 64, 5, Block::PLANKS);
            $chunk->setBlock(5, 64, 6, Block::PLANKS);
            $chunk->setBlock(5, 64, 7, Block::PLANKS);
            $chunk->setBlock(5, 64, 8, Block::PLANKS);
            $chunk->setBlock(5, 64, 9, Block::PLANKS);
            $chunk->setBlock(5, 64, 10, Block::PLANKS);
            $chunk->setBlock(6, 64, 5, Block::PLANKS);
            $chunk->setBlock(6, 64, 6, Block::PLANKS);
            $chunk->setBlock(6, 64, 7, Block::PLANKS);
            $chunk->setBlock(6, 64, 8, Block::PLANKS);
            $chunk->setBlock(6, 64, 9, Block::PLANKS);
            $chunk->setBlock(6, 64, 10, Block::PLANKS);
            $chunk->setBlock(7, 64, 5, Block::PLANKS);
            $chunk->setBlock(7, 64, 6, Block::PLANKS);
            $chunk->setBlock(7, 64, 7, Block::PLANKS);
            $chunk->setBlock(7, 64, 8, Block::PLANKS);
            $chunk->setBlock(7, 64, 9, Block::PLANKS);
            $chunk->setBlock(7, 64, 10, Block::PLANKS);
            $chunk->setBlock(8, 64, 5, Block::PLANKS);
            $chunk->setBlock(8, 64, 6, Block::PLANKS);
            $chunk->setBlock(8, 64, 7, Block::PLANKS);
            $chunk->setBlock(8, 64, 8, Block::PLANKS);
            $chunk->setBlock(8, 64, 9, Block::PLANKS);
            $chunk->setBlock(8, 64, 10, Block::PLANKS);
            $chunk->setBlock(9, 64, 5, Block::PLANKS);
            $chunk->setBlock(9, 64, 6, Block::PLANKS);
            $chunk->setBlock(9, 64, 7, Block::PLANKS);
            $chunk->setBlock(9, 64, 8, Block::PLANKS);
            $chunk->setBlock(9, 64, 9, Block::PLANKS);
            $chunk->setBlock(9, 64, 10, Block::PLANKS);
            $chunk->setBlock(9, 64, 4, Block::WOODEN_SLAB);
            $chunk->setBlock(7, 64, 4, Block::WOODEN_SLAB);
            $chunk->setBlock(5, 64, 4, Block::WOODEN_SLAB);
            $chunk->setBlock(9, 64, 11, Block::WOODEN_SLAB);
            $chunk->setBlock(7, 64, 11, Block::WOODEN_SLAB);
            $chunk->setBlock(5, 64, 11, Block::WOODEN_SLAB);
            $chunk->setBlock(7, 65, 6, Block::FENCE);
            $chunk->setBlock(7, 66, 6, Block::FENCE);
            $chunk->setBlock(7, 67, 6, Block::FENCE);
            $chunk->setBlock(7, 68, 6, Block::FENCE);
            $chunk->setBlock(8, 68, 6, Block::WOOL);
            $chunk->setBlock(8, 67, 6, Block::WOOL);
            $chunk->setBlock(9, 68, 6, Block::WOOL);
            $chunk->setBlock(9, 67, 6, Block::WOOL);
            $chunk->setBlock(10, 68, 6, Block::WOOL);
            $chunk->setBlock(6, 68, 6, Block::WOOL);
            $chunk->setBlock(6, 67, 6, Block::WOOL);
            $chunk->setBlock(5, 68, 6, Block::WOOL);
            $chunk->setBlock(5, 67, 6, Block::WOOL);
            $chunk->setBlock(4, 68, 6, Block::WOOL);
            $chunk->setX($chunkX);
            $chunk->setZ($chunkZ);
            $this->level->setChunk($chunkX, $chunkZ, $chunk);
        }
    }

    /**
     * @param int $chunkX
     * @param int $chunkZ
     */
    public function populateChunk(int $chunkX, int $chunkZ) : void {
        return;
    }

    /**
     * @return Vector3
     */
    public static function getWorldSpawn(): Vector3 {
        return new Vector3(7, 65, 8);
    }

    /**
     * @return Vector3
     */
    public static function getChestPosition(): Vector3 {
        return new Vector3(7, 65, 7);
    }

    /**
     * @return Vector3
     */
    public function getSpawn(): Vector3 {
        return new Vector3(7, 65, 8);
    }

}
