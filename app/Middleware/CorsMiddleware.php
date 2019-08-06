<?php


namespace App\Middleware;

use App\Log\LogWriter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Context\Context;
use Swoft\Http\Server\Contract\MiddlewareInterface;
/**
 * @author gyy
 * @Bean()
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * 允许跨域
     * 所有请求必经过这个中间件
     * Process an incoming server request.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 设置全局traceid
        \context()->set('traceid', getLogId());
        LogWriter::pushlog('request-method', $request->getMethod());
        $allowHeaders = $request->getHeaderLine("Access-Control-Request-Headers");
        if ('OPTIONS' === $request->getMethod()) {
            $response = Context::mustGet()->getResponse();
            return $this->configResponse($response, $allowHeaders);
        }
        $response = $handler->handle($request);
        return $this->configResponse($response, $allowHeaders);
    }

    /**
     * @param ResponseInterface $response
     * @param $allowHeaders
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function configResponse(ResponseInterface $response, $allowHeaders = '')
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader("Cache-Control", "no-store")
            ->withHeader('X-Log-Id', getLogId())
            ->withHeader('Access-Control-Allow-Headers', $allowHeaders)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }
}
