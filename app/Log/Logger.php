<?php
/**
 * Created by PhpStorm.
 * User: 74227
 * Date: 2018/12/19
 * Time: 15:08
 */

namespace App\Log;

use App\Constant\Constant;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Co;

/**
 * 日志类
 * Class Logger
 * @author gyy
 * @Bean("logger")
 * @package App\Logger
 */
class Logger extends \Swoft\Log\Logger
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * pushlog日志
     * @param string $key 记录KEY
     * @param mixed  $val 记录值
     * @param bool  $maxLength 当$val为string时，截取string的最大长度，默认不截取
     * @param bool  $isUrlEncode 当$val为string时是否urlencode，默认否
     */
    public function pushLog($key, $val, $maxLength = null, $isUrlEncode = false): void
    {
        if (!$this->enable || !$key) {
            return;
        }

        $key = $isUrlEncode ? urlencode($key) : $key;
        $cid = Co::tid();
        if (is_array($val)) {
            $val = json_encode($val, JSON_UNESCAPED_UNICODE);
            if (null !== $maxLength) {
                $val = substr($val, 0, $maxLength);
            }
            $this->pushlogs[$cid][] = "$key=" . $val;
        } elseif (is_bool($val)) {
            $this->pushlogs[$cid][] = "$key=" . var_export($val, true);
        } elseif (is_string($val)) {
            if (null !== $maxLength) {
                $val = substr($val, 0, $maxLength);
            }
            if ($isUrlEncode) {
                $this->pushlogs[$cid][] = "$key=" . urlencode($val);
            } else {
                $this->pushlogs[$cid][] = "$key=" . $val;
            }
        } elseif (null === $val) {
            $this->pushlogs[$cid][] = "$key=";
        } else {
            $this->pushlogs[$cid][] = "$key=$val";
        }
    }


    /**
     * Add record
     * @param int   $level
     * @param mixed $message
     * @param array $context
     * @return bool
     * @throws \Exception
     */
    public function addRecord($level, $message, array $context = []): bool
    {
        if (!$this->enable) {
            return true;
        }

        $levelName = static::getLevelName($level);

        if (!static::$timezone) {
            static::$timezone = new \DateTime(date_default_timezone_get() ?: 'UTC');
        }

        // php7.1+ always has microseconds enabled, so we do not need this hack
        if ($this->microsecondTimestamps && PHP_VERSION_ID < 70100) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
        } else {
            $ts = new \DateTime('now', static::$timezone);
        }

        $ts->setTimezone(static::$timezone);

        $message = $this->formatMessage($message);
        $message = $this->getTrace($message);
        $record  = $this->formatRecord($message, $context, $level, $levelName, $ts, []);

        foreach ($this->processors as $processor) {
            $record = $processor($record);
        }

        if (isset($record['messages']) && !empty($record['messages'])) {
            $record['messages'] = substr($record['messages'], 0, Constant::MAX_LOG_LENGTH);
        }

        $this->messages[] = $record;

        if (count($this->messages) >= $this->flushInterval) {
            $this->flushLog();
        }

        return true;
    }
}
