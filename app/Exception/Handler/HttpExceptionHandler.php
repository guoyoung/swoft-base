<?php declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constant\Constant;
use App\Log\LogWriter;
use App\Middleware\CorsMiddleware;
use function get_class;
use ReflectionException;
use function sprintf;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Error\Annotation\Mapping\ExceptionHandler;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Exception\Handler\AbstractHttpErrorHandler;
use Throwable;

/**
 * Class HttpExceptionHandler
 * @author  gyy
 * @ExceptionHandler(\Throwable::class)
 */
class HttpExceptionHandler extends AbstractHttpErrorHandler
{
    /**
     * @param Throwable $e
     * @param Response   $response
     *
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function handle(Throwable $e, Response $response): Response
    {
        $data = [
            'code'  => $e->getCode(),
            'error' => sprintf('(%s) %s', get_class($e), $e->getMessage()),
            'file'  => sprintf('At %s line %d', $e->getFile(), $e->getLine()),
            'trace' => $e->getTraceAsString(),
        ];

        LogWriter::error('exception occurred: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

        if (!APP_DEBUG) {
            $data = \App\Constant\Response::EXCEPTION_RESPONSE;
        }

        Constant::AUTH_FAIL_CODE == $e->getCode() && $data = \App\Constant\Response::AUTH_FAIL_RESPONSE;

        $response = $response->withData($data);
        /**
         * @var $bean CorsMiddleware
         */
        $bean = BeanFactory::getBean(CorsMiddleware::class);
        return $bean->configResponse($response);
    }
}
