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

namespace pedhot\Raft\form;


use pedhot\Raft\form\libs\jojoe77777\FormAPI\CustomForm;
use pedhot\Raft\form\libs\jojoe77777\FormAPI\SimpleForm;
use pedhot\Raft\event\isle\IsleCloseEvent;
use pedhot\Raft\event\isle\IsleDisbandEvent;
use pedhot\Raft\isle\Isle;
use pedhot\Raft\session\iSession;
use pedhot\Raft\session\Session;
use pedhot\Raft\Raft;
use pocketmine\Player;

class RaftForm
{

    /** @var Raft */
    private $plugin;

    public function __construct(Raft $plugin)
    {
        $this->plugin = $plugin;
    }

    public function init(Player $sender)
    {
        $session = $this->plugin->getSessionManager()->getSession($sender);
        if($session->hasIsle()) {
            $this->Menu($sender);
        }else{
            $this->Buat($sender);
        }
    }

    public function Information($sender){
        $form = new SimpleForm(function(Player $sender, $data){
            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    $this->Menu($sender);
                    break;
            }
        });
        $form->setTitle("§lIsland Info");
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $island = $session->getIsle();
        $isMembers = count($island->getMembers());
        $content = "§eDibawah ini adalah info island mu!\n\n";
        $content .= "§eBlocks§f: §a" . $island->getBlocksBuilt() . "\n\n";
        $content .= $island->isLocked() ? "§eState§f: §cLocked\n\n" : "§eState§f: §aUnlocked\n\n";
        $content .= "§eMembers§f: §a" . $isMembers . "§f/§a" . $island->getSlots() . "\n\n";
        $content .= "§eOnline Members§f: §a" . count($island->getMembersOnline()) . "§f/§a" . $isMembers . "\n\n";
        $content .= "§eCategory§f: §a" . $island->getCategory() . "\n\n";
        $form->setContent($content);
        $form->addButton("§lBack", 0, "textures/ui/back_button_hover");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Buat($sender){
        $form = new SimpleForm(function(Player $sender, $data){
            $result = $data;
            if($result === null){
                return true;
            }
            if($result === 0){
                $session = $this->plugin->getSessionManager()->getSession($sender);
                if($session->hasIsle()){
                    $session->sendTranslatedMessage("NEED_TO_BE_FREE");
                    return;
                }
                $generator = "Basic";
                if($this->plugin->getGeneratorManager()->isGenerator($generator)){
                    $this->plugin->getIsleManager()->createIsleFor($session, $generator, $sender);
                    $session->sendTranslatedMessage("SUCCESSFULLY_CREATED_A_ISLE");
                }else{
                    $session->sendTranslatedMessage("NOT_VALID_GENERATOR", [
                        "name" => $generator
                    ]);
                }
                $session = $this->plugin->getSessionManager()->getSession($sender);
                $session->getPlayer()->teleport($session->getIsle()->getLevel()->getSpawnLocation());
                $session->sendTranslatedMessage("TELEPORTED_TO_ISLE");
            }
            if($result === 1){
                $this->Accept($sender);
            }
        });
        $name = $sender->getName();
        $form->setTitle("§lRaft Menu");
        $form->setContent("Hai {$name}, Kamu belum membuat pulau sama sekali, Buatlah dengan cara mengklik button §lCreate Island §rdi bawah ini!, dan jika ingin menerima pertemanan, klik §lAccept Invite§r!");
        $form->addButton("§lCreate Island", 0, "textures/ui/icon_recipe_nature");
        $form->addButton("§lAccept Invite", 0, "textures/ui/FriendsIcon");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Menu($sender){
        $form = new SimpleForm(function(Player $sender, $data){
            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    $session = $this->plugin->getSessionManager()->getSession($sender);
                    $session->getPlayer()->teleport($session->getIsle()->getLevel()->getSpawnLocation());
                    $session->sendTranslatedMessage("TELEPORTED_TO_ISLE");
                    break;
                case 1:
                    $this->Visit($sender);
                    break;
                case 2:
                    $this->Information($sender);
                    break;
                case 3:
                    $this->Settings($sender);
                    break;
                case 4:
                    $this->Remove($sender);
                    break;
                case 5:
                    break;
            }
        });
        $name = $sender->getName();
        $form->setTitle("§lRaft Menu");
        $form->setContent("\n");
        $form->addButton("§lTeleport Island", 0, "textures/ui/icon_recipe_nature");
        $form->addButton("§lVisit Island", 0, "textures/ui/spyglass_flat");
        $form->addButton("§lIsland Info", 0, "textures/ui/creative_icon");
        $form->addButton("§lManage Island", 0, "textures/ui/settings_glyph_color_2x");
        $form->addButton("§lRemove Island", 0, "textures/ui/trash");
        $form->addButton("§lCancel", 0, "textures/ui/cancel");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Visit($sender){
        $list = [];
        foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
            $list[] = $p->getName();
        }
        $this->playerList[$sender->getName()] = $list;
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $form = new CustomForm(function(Player $sender, $data){
            if($data === null){
                return true;
            }
            $index = $data[0];
            $playerName = $this->playerList[$sender->getName()][$index];
            $session = $this->plugin->getSessionManager()->getSession($sender);
            $offline = $this->plugin->getSessionManager()->getOfflineSession($playerName);
            $isleId = $offline->getIsleId();
            if($isleId == null) {
                $session->sendTranslatedMessage("HE_DO_NOT_HAVE_AN_ISLE", [
                    "name" => $playerName
                ]);
                return;
            }
            $this->plugin->getProvider()->loadIsle($isleId);
            $isle = $this->plugin->getIsleManager()->getIsle($isleId);
            if($isle->isLocked()) {
                $session->sendTranslatedMessage("HIS_ISLE_IS_LOCKED", [
                    "name" => $playerName
                ]);
                $isle->tryToClose();
                return;
            }
            $session->getPlayer()->teleport($isle->getLevel()->getSpawnLocation());
            $session->sendTranslatedMessage("VISITING_ISLE", [
                "name" => $playerName
            ]);
        });
        $form->setTitle("§lVisit");
        $form->addDropDown("Select a Player!", $this->playerList[$sender->getName()]);
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Settings($sender){
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $form = new SimpleForm(function(Player $sender, $data){
            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    $session = $this->plugin->getSessionManager()->getSession($sender);
                    if($this->checkLeader($session)) {
                        return;
                    }
                    $isle = $session->getIsle();
                    $isle->setLocked(!$isle->isLocked());
                    $isle->save();
                    $session->sendTranslatedMessage($isle->isLocked() ? "ISLE_LOCKED" : "ISLE_UNLOCKED");
                    break;
                case 1:
                    $session = $this->plugin->getSessionManager()->getSession($sender);
                    if($this->checkOfficer($session)) {
                        return;
                    } elseif($session->getPlayer()->getLevel() !== $session->getIsle()->getLevel()) {
                        $session->sendTranslatedMessage("MUST_BE_IN_YOUR_ISLE");
                    } else {
                        $session->getIsle()->setSpawnLocation($session->getPlayer());
                        $session->sendTranslatedMessage("SUCCESSFULLY_SET_SPAWN");
                    }
                    break;
                case 2:
                    $session = $this->plugin->getSessionManager()->getSession($sender);
                    if($this->checkIsle($session)) {
                        return;
                    }
                    $session->setInChat(!$session->isInChat());
                    $session->sendTranslatedMessage($session->isInChat() ? "JOINED_ISLE_CHAT" : "JOINED_GLOBAL_CHAT");
                    $this->Settings($sender);
                    break;
                case 3:
                    $this->Invite($sender);
                    break;
                case 4:
                    $this->Kick($sender);
                    break;
                case 5:
                    $session = $this->plugin->getSessionManager()->getSession($sender);
                    if($this->checkIsle($session)){
                        return;
                    }elseif($session->getRank() == iSession::RANK_FOUNDER){
                        $session->sendTranslatedMessage("FOUNDER_CANNOT_LEAVE");
                        return;
                    }
                    $session->setRank(iSession::RANK_DEFAULT);
                    $session->setIsle(null);
                    $session->setInChat(false);
                    $session->sendTranslatedMessage("LEFT_ISLE");
                    break;
                case 6:
                    $this->Menu($sender);
                    break;
            }
        });
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $isle = $session->getIsle();
        $form->setTitle("§lManage Island");
        $form->setContent("\n");
        if($isle->isLocked()){
            $form->addButton("§lIsland Locked", 0, "textures/ui/icon_lock");
        }else{
            $form->addButton("§lIsland Unlocked", 0, "textures/ui/icon_unlocked");
        }
        $form->addButton("§lMake Spawn", 0, "textures/ui/absorption_effect");
        if($session->isInChat()){
            $form->addButton("§lIsland Chat\n§lOn", 0, "textures/ui/mute_on");
        }else{
            $form->addButton("§lIsland Chat\n§lOff", 0, "textures/ui/mute_off");
        }
        $form->addButton("§lInvite Friend", 0, "textures/ui/FriendsIcon");
        $form->addButton("§lKick Friend", 0, "textures/ui/FriendsIcon");
        $form->addButton("§lLeave Island", 0, "textures/ui/FriendsIcon");
        $form->addButton("§lBack", 0, "textures/ui/back_button_hover");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Remove($sender){
        $form = new SimpleForm(function(Player $sender, $data){
            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    $session = $this->plugin->getSessionManager()->getSession($sender);
                    if($this->checkFounder($session)) {
                        return;
                    }
                    $this->disbandIsle($session->getIsle());
                    break;
                case 1:
                    $this->Settings($sender);
                    break;
            }
        });
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $block = $session->getIsle()->getBlocksBuilt();
        $form->setTitle("§lRemove");
        $form->setContent("§cYakin ingin menghapus pulau mu dengan jumlah block §e{$block}§c?");
        $form->addButton("§lYes", 0, "textures/ui/icon_trash");
        $form->addButton("§lNo", 0, "textures/ui/back_button_hover");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Accept($sender){
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $form = new CustomForm(function(Player $sender, $data){
            if($data === null){
                return true;
            }
            $session = $this->plugin->getSessionManager()->getSession($sender);
            if($session->hasIsle()) {
                $session->sendTranslatedMessage("NEED_TO_BE_FREE");
                return;
            } elseif(!isset($data[0]) and !$session->hasLastInvitation()) {
                $session->sendTranslatedMessage("ACCEPT_USAGE");
                return;
            }
            $isle = $session->getInvitation($data[0] ?? $session->getLastInvitation());
            if($isle == null) {
                return;
            }
            $session->setRank(iSession::RANK_DEFAULT);
            $session->setIsle($isle);
            $isle->broadcastTranslatedMessage("PLAYER_JOINED_THE_ISLE", [
                "name" => $session->getUsername()
            ]);
        });
        $form->setTitle("§lAccept");
        $form->addInput("Masukan nama player yang ingin mengajak kepulau nya", "Yang mengajak!");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Invite($sender){
        $list = [];
        foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
            $list[] = $p->getName();
        }
        $this->playerList[$sender->getName()] = $list;
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $form = new CustomForm(function(Player $sender, $data){
            if($data === null){
                return true;
            }
            $index = $data[0];
            $playerName = $this->playerList[$sender->getName()][$index];
            $session = $this->plugin->getSessionManager()->getSession($sender);
            if($this->checkOfficer($session)) {
                return;
            } elseif(!isset($playerName)) {
                $session->sendTranslatedMessage("INVITE_USAGE");
                return;
            } elseif(count($session->getIsle()->getMembers()) >= $session->getIsle()->getSlots()) {
                $isle = $session->getIsle();
                $next = $isle->getNextCategory();
                if($next != null) {
                    $session->sendTranslatedMessage("ISLE_IS_FULL_BUT_YOU_CAN_UPGRADE", [
                        "next" => $next
                    ]);
                } else {
                    $session->sendTranslatedMessage("ISLE_IS_FULL");
                }
                return;
            }
            $player = $this->plugin->getServer()->getPlayer($playerName);
            if($player == null) {
                $session->sendTranslatedMessage("NOT_ONLINE_PLAYER", [
                    "name" => $playerName
                ]);
                return;
            }
            $playerSession = $this->plugin->getSessionManager()->getSession($player);
            if($this->checkClone($session, $playerSession)) {
                return;
            } elseif($playerSession->hasIsle()) {
                $session->sendTranslatedMessage("CANNOT_INVITE_BECAUSE_HAS_ISLE", [
                    "name" => $player->getName()
                ]);
                return;
            }
            $playerSession->addInvitation($session->getUsername(), $session->getIsle());
            $playerSession->sendTranslatedMessage("YOU_WERE_INVITED_TO_AN_ISLE", [
                "name" => $session->getUsername()
            ]);
            $session->sendTranslatedMessage("SUCCESSFULLY_INVITED", [
                "name" => $player->getName()
            ]);
        });
        $form->setTitle("§lInvite Friend");
        $form->addDropDown("Pilih satu Player", $this->playerList[$sender->getName()]);
        $form->sendToPlayer($sender);
        return $form;
    }

    public function Kick($sender){
        $session = $this->plugin->getSessionManager()->getSession($sender);
        $form = new CustomForm(function(Player $sender, $data){
            if($data === null){
                return true;
            }
            $session = $this->plugin->getSessionManager()->getSession($sender);
            if($this->checkOfficer($session)) {
                return;
            } elseif(!isset($data[0])) {
                $session->sendTranslatedMessage("KICK_USAGE");
                return;
            }
            $server = $this->plugin->getServer();
            $player = $server->getPlayer($data[0]);
            if($player == null) {
                $session->sendTranslatedMessage("NOT_ONLINE_PLAYER", [
                    "name" => $data[0]
                ]);
                return;
            }
            $playerSession = $this->plugin->getSessionManager()->getSession($player);
            if($this->checkClone($session, $playerSession)) {
                return;
            } elseif($playerSession->getIsle() === $session->getIsle()) {
                $session->sendTranslatedMessage("CANNOT_KICK_A_MEMBER");
            } elseif(in_array($player, $session->getIsle()->getPlayersOnline())) {
                $player->teleport($server->getDefaultLevel()->getSpawnLocation());
                $playerSession->sendTranslatedMessage("KICKED_FROM_THE_ISLE");
                $session->sendTranslatedMessage("YOU_KICKED_A_PLAYER", [
                    "name" => $playerSession->getUsername()
                ]);
            } else {
                $session->sendTranslatedMessage("NOT_A_VISITOR", [
                    "name" => $playerSession->getUsername()
                ]);
            }
        });
        $form->setTitle("§lKick Player");
        $form->addInput("Masukan nama Player", "Yang online!");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function checkIsle(Session $session): bool {
        if($session->hasIsle()) {
            return false;
        }
        $session->sendTranslatedMessage("NEED_ISLE");
        return true;
    }

    public function checkFounder(Session $session): bool {
        if($this->checkIsle($session)) {
            return true;
        } elseif($session->getRank() == iSession::RANK_FOUNDER) {
            return false;
        }
        $session->sendTranslatedMessage("MUST_BE_FOUNDER");
        return true;
    }

    public function checkLeader(Session $session): bool {
        if($this->checkIsle($session)) {
            return true;
        } elseif($session->getRank() == iSession::RANK_FOUNDER or $session->getRank() == iSession::RANK_LEADER) {
            return false;
        }
        $session->sendTranslatedMessage("MUST_BE_LEADER");
        return true;
    }

    public function checkOfficer(Session $session): bool {
        if($this->checkIsle($session)) {
            return true;
        } elseif($session->getRank() != iSession::RANK_DEFAULT) {
            return false;
        }
        $session->sendTranslatedMessage("MUST_BE_OFFICER");
        return true;
    }

    public function checkClone(?Session $session, ?Session $ySession): bool {
        if($session === $ySession) {
            $session->sendTranslatedMessage("CANT_BE_YOURSELF");
            return true;
        }
        return false;
    }

    public function disbandIsle(Isle $isle): void {
        foreach($isle->getLevel()->getPlayers() as $player) {
            $player->teleport($player->getServer()->getDefaultLevel()->getSpawnLocation());
        }
        foreach($isle->getMembers() as $offlineMember) {
            $onlineSession = $offlineMember->getSession();
            if($onlineSession != null) {
                $onlineSession->setIsle(null);
                $onlineSession->setRank(Session::RANK_DEFAULT);
                $onlineSession->save();
                $onlineSession->sendTranslatedMessage("ISLE_DISBANDED");
            } else {
                $offlineMember->setIsleId(null);
                $offlineMember->setRank(Session::RANK_DEFAULT);
                $offlineMember->save();
            }
        }
        $isle->setMembers([]);
        $isle->save();
        $this->closeIsle($isle);
        $this->plugin->getServer()->getPluginManager()->callEvent(new IsleDisbandEvent($isle));
    }

    public function closeIsle(Isle $isle): void {
        $isle->save();
        $server = $this->plugin->getServer();
        $server->getPluginManager()->callEvent(new IsleCloseEvent($isle));
        $server->unloadLevel($isle->getLevel());
        unset($this->isles[$isle->getIdentifier()]);
    }

}