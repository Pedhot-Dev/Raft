<?php

namespace pedhot\Raft\command;

use pedhot\Raft\form\RaftForm;
use pedhot\Raft\Raft;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class IsleCommandMap extends PluginCommand
{

    /** @var Raft $plugin */
    private $plugin;

    public function __construct(string $name, Raft $owner)
    {
        parent::__construct($name, $owner);
        $this->plugin = $owner;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) {
            $sender->sendMessage("Please, run this command in game");
            return;
        }
        $ui = new RaftForm($this->plugin);
        $ui->init($sender);
        return true;
    }

}