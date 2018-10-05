<?php

declare(strict_types=1);

namespace xenialdan\PMMPLogRotate\commands\log;

use pocketmine\command\CommandSender;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xenialdan\PMMPLogRotate\commands\Command;
use xenialdan\PMMPLogRotate\Loader;

class LogrotateCommand extends Command
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct("logrotate", $plugin);
        $this->setPermission("logrotate.command.log");
        $this->setDescription("Manual log rotation");
        $this->setUsage("/logrotate");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        /** @var Player $sender */
        $return = $sender->hasPermission($this->getPermission());
        if (!$return) {
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
            return true;
        }

        if (Loader::getInstance()->sliceFile())
            $sender->sendMessage(TextFormat::GREEN . "Successfully rotated logs");
        else
            $sender->sendMessage(TextFormat::RED . "Failed to rotate logs");
        return true;
    }
}
