<?php

declare(strict_types=1);

namespace xenialdan\PMMPLogRotate;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use xenialdan\PMMPLogRotate\commands\log\LogrotateCommand;
use xenialdan\PMMPLogRotate\task\FilesizeRotate;
use xenialdan\PMMPLogRotate\task\ScheduleRotate;

class Loader extends PluginBase
{
    /** @var Loader */
    private static $instance = null;

    /**
     * Returns an instance of the plugin
     * @return Loader
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $this->getServer()->getCommandMap()->registerAll("log", [
            new LogrotateCommand($this)
        ]);
        if ($this->getConfig()->get("rotate-on-enable", false)) {
            $this->sliceFile();
        }
        if ($this->getConfig()->get("use-filesize-rotate", false)) {
            $this->getScheduler()->scheduleDelayedRepeatingTask(new FilesizeRotate(), 20 * 100, 20 * 60);
        }
        if ($this->getConfig()->get("use-time-rotate", false)) {
            $this->getScheduler()->scheduleDelayedRepeatingTask(new ScheduleRotate(), 20 * 100, 20 * (strtotime("+" . $this->getConfig()->get("time-rotation-schedule", "1d")) - time()));
        }
        //TODO delete option, always-at
    }

    public function getFormat(): string
    {
        return "Y_m_d-H_i_s";
    }

    /**
     * @param string $size
     * @return bool
     */
    public function sliceFile(string $size = ""): bool
    {
        $logFile = Server::getInstance()->getDataPath() . "server.log";
        if (!file_exists($logFile)) return false;
        if (empty($size)) $length = null;
        else $length = $this->human2byte($size);
        if (is_null($length))
            $file = file_get_contents($logFile);
        else
            $file = file_get_contents($logFile, false, null, 0, $length);
        if ($file === false) return false;
        $newLogFile = Server::getInstance()->getDataPath() . "logs" . DIRECTORY_SEPARATOR . "server_" . date($this->getFormat()) . ".log";
        @mkdir(Server::getInstance()->getDataPath() . "logs", 0777, true);
        if (file_put_contents($newLogFile, $file) === false) return false;
        if (is_null($length)) {
            if (file_put_contents($logFile, substr($file, strlen($file))) === false) return false;
        } else {
            if (file_put_contents($logFile, substr($file, $length)) === false) return false;
        }
        return true;
    }

    /**
     * Converts a human readable file size value to a number of bytes that it
     * represents. Supports the following modifiers: K, M, G, T, P, E, Z and Y.
     * Invalid input is returned unchanged.
     *
     * Example:
     * <code>
     * $config->human2byte(10);          // 10
     * $config->human2byte('10b');       // 10
     * $config->human2byte('10k');       // 10240
     * $config->human2byte('10K');       // 10240
     * $config->human2byte('10kb');      // 10240
     * $config->human2byte('10Kb');      // 10240
     * // and even
     * $config->human2byte('   10 KB '); // 10240
     * </code>
     *
     * @param number|string $value
     * @return number
     * @url https://stackoverflow.com/a/24676538/4532380
     */
    public function human2byte($value)
    {
        return preg_replace_callback('/^\s*(\d+)\s*(?:([kmgtpezy]?)b?)?\s*$/i', function ($m) {
            switch (strtolower($m[2])) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'y':
                    $m[1] *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'z':
                    $m[1] *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'e':
                    $m[1] *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'p':
                    $m[1] *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 't':
                    $m[1] *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'g':
                    $m[1] *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'm':
                    $m[1] *= 1024;
                case 'k':
                    $m[1] *= 1024;
            }
            return $m[1];
        }, $value);
    }
}