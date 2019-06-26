<?php
/**
 * Created by PhpStorm.
 * User: 74227
 * Date: 2018/12/19
 * Time: 15:08
 */

namespace App\Log;

/**
 * 记录日志类
 * Class Logger
 * @author gyy
 * @package App\Log
 */
class LogWriter extends \Swoft\Log\Helper\Log
{
    /**
     * 标记日志
     *
     * @param string $key 统计key
     * @param mixed $val 统计值
     * @param mixed $maxLength 日志最大长度
     * @param mixed $isUrlEncode 是否对日志进行urlencode
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public static function pushlog($key, $val, $maxLength = null, $isUrlEncode = false): void
    {
        /**
         * @var $logger Logger
         */
        $logger = self::getLogger();
        $logger->pushLog($key, $val, $maxLength, $isUrlEncode);
    }
}
