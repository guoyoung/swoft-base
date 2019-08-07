<?php declare(strict_types=1);


namespace App\Http\Base;


use App\Constant\Constant;
use App\Constant\Response;
use Swoft\Context\Context;
use Swoft\Http\Message\ContentType;

/**
 * Class BaseController
 * @author gyy
 */
class BaseController
{
    /**
     * 返回json
     * @param array $data
     * @param int $code
     * @param bool $status
     * @param int $statusCode http状态码
     * @param string $type
     * @return \Swoft\Http\Message\Response|\Swoft\WebSocket\Server\Message\Response
     * @throws \Swoft\Exception\SwoftException
     */
    public function json($data = [], $code = 0, $status = true, $statusCode = 200, $type = ContentType::JSON)
    {
        $return = [
            'code' => $code,
            'status' => $status,
            'msg' => Response::RESPONSE_CODE_MSG[$code] ?? '',
            'data' => $data,
        ];
        return $this->response($return, $statusCode, $type);
    }

    /**
     * 返回response
     * @param array $data
     * @param int $status
     * @param string $type
     * @return \Swoft\Http\Message\Response|\Swoft\WebSocket\Server\Message\Response
     * @throws \Swoft\Exception\SwoftException
     */
    public function response($data = [], $status = 200, $type = ContentType::JSON)
    {
        $response = \context()->getResponse();
        return $response->withData($data)->withStatus($status)->withContentType($type);
    }
}
