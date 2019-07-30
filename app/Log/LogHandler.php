<?php


namespace App\Log;

use http\Exception\UnexpectedValueException;
use Swoft\Log\Handler\FileHandler;

/**
 * 日志处理文件
 * 日志每天生成一个文件夹，每小时切割一次
 * Class LogHandler
 * @author gyy
 */
class LogHandler extends FileHandler
{
    private $firstTime = true;

    public function write(array $records): void
    {
        $len = strlen(alias('@runtime/log/'));
        $date = substr($this->logFile, $len, 8);
        if ($date != date('Ymd')) {
            $this->logFile = str_replace($date, date('Ymd'), $this->logFile);
            $this->createDir();
        }
        if ($this->firstTime) {
            $this->logFile = str_replace('.', date('H') . '.', $this->logFile);
            $this->firstTime = false;
        } else {
            $hour = substr($this->logFile, -6, 2);
            if ($hour != date('H')) {
                $this->logFile = str_replace($hour . '.', date('H') . '.', $this->logFile);
            }
        }

        parent::write($records);
    }

    /**
     * Create dir
     */
    private function createDir(): void
    {
        $logDir = dirname($this->logFile);

        if ($logDir !== null && !is_dir($logDir)) {
            $status = mkdir($logDir, 0777, true);
            if ($status === false) {
                throw new UnexpectedValueException(
                    sprintf('There is no existing directory at "%s" and its not buildable: ', $logDir)
                );
            }
        }
    }
}
