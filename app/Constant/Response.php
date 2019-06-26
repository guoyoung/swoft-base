<?php


namespace App\Constant;

/**
 * response配置文件
 * Class Response
 * @author gyy
 * @package App\Response
 */
class Response
{
    /**
     * 统一异常处理返回
     */
    const EXCEPTION_RESPONSE = [
        'code' => 10,
        'status' => false,
        'msg' => '系统错误，请联系管理员',
        'data' => []
    ];

    const AUTH_FAIL_RESPONSE = [
        'code' => Constant::AUTH_FAIL_CODE,
        'status' => false,
        'msg' => '鉴权失败，请联系管理员',
        'data' => []
    ];

    const RESPONSE_CODE_MSG = [
        0 => 'success',
        1 => 'fail'
    ];
}
