<?php


namespace App\Log;

use Swoft\Log\Handler\FileHandler;

/**
 * 日志处理文件
 * 日志每天生成一个文件夹，每小时切割一次
 * Class LogHandler
 * @author gyy
 */
class LogHandler extends FileHandler
{
    public function init(): void
    {
        $date = substr($this->logFile, 13, 8);
        if ($date != date('Ymd')) {
            $this->logFile = str_replace($date, date('Ymd'), $this->logFile);
        }
        $this->logFile = str_replace('.', date('H') . '.', $this->logFile);
        parent::init();
    }
}
