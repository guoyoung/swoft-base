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

    /**
     * 当前请求的log id
     * @return bool|string|null
     */
    public function getLogId()
    {
        null === $this->logId && $this->logId = substr(md5(Co::tid() . microtime()), 0, 13);
        return $this->logId;
    }
}
