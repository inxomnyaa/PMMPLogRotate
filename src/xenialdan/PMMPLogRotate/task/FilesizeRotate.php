<?php

namespace xenialdan\PMMPLogRotate\task;

use pocketmine\scheduler\Task;
use xenialdan\PMMPLogRotate\Loader;

class FilesizeRotate extends Task {
    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        Loader::getInstance()->sliceFile(Loader::getInstance()->getConfig()->get("max-file-size", ""));
    }
}