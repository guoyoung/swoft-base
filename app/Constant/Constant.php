<?php


namespace App\Constant;

/**
 * 常量配置文件
 * Class Constant
 * @author gyy
 * @package App\Constant
 */
class Constant
{
    /**
     * http超时时间
     */
    const HTTP_TIMEOUT = 60;

    /**
     * http连接池最大数量
     */
    const HTTP_CLIENT_POOL = 100;

    /**
     * 一次最大发送并发请求数量
     */
    const HTTP_MAX_MULTI_REQUEST_COUNT = 10;

    /**
     * 日志最大长度
     */
    const MAX_LOG_LENGTH = 1024 * 1024;

    /**
     * swoole.log
     */
    const LOG_FILE = '@runtime/log/swoole/swoole.log';

    /**
     * 参数过滤项
     */
    const FILTER = ['\'', '"', '|', '$', '#'];

    /**
     * 鉴权失败异常错误码
     */
    const AUTH_FAIL_CODE = 11;
}
