<?php


namespace App\Bean;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Co;

/**
 * 生命周期：当前请求
 * 当前请求结束，自动销毁
 * Class RequestBean
 * @package App\Bean
 * @Bean(name="requestBean", scope=Bean::REQUEST)
 */
class RequestBean
{
    private $logId = null;

    private $context = [];

    /**
     * 当前请求的log id
     * @return bool|string|null
     */
    public function getLogId()
    {
        null === $this->logId && $this->logId = substr(md5(Co::tid() . microtime() . rand(100000, 999999)), 0, 13);
        return $this->logId;
    }

    /**
     * 当前请求需要保存的上下文
     * @param $key
     * @param $context
     */
    public function set($key, $context)
    {
        $this->context[$key] = $context;
    }

    /**
     * 获取当前请求的上下文
     * @param $key
     * @return array|mixed
     */
    public function get($key)
    {
        return $this->context[$key] ?? [];
    }
}
