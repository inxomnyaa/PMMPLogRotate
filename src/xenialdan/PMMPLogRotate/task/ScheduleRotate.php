<?php

namespace xenialdan\PMMPLogRotate\task;

use pocketmine\scheduler\Task;
use xenialdan\PMMPLogRotate\Loader;

class ScheduleRotate extends Task {
    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        Loader::getInstance()->sliceFile();
    }
}