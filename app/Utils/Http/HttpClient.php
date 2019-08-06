<?php


namespace App\Utils\Http;

use App\Constant\Constant;
use App\Log\LogWriter;
use Swlib\Http\ContentType;
use Swlib\Http\Exception\HttpExceptionMask;
use Swlib\Saber;

/**
 * 使用说明：
 * 只允许通过getInstance获取httpClient对象，request单个请求，multi并发请求
 * 配置说明：
| base_uri              | string                | 基础路径           如：http://127.0.0.1:8081
| uri                   | string                | 资源标识符         如：/service/dispatch?node=1001000
| method                | string                | 请求方法           如：GET
| headers               | array                 | 请求报头
| cookies               | `array`|`string`      |
| useragent             | string                | 用户代理
| referer               | string                | 来源地址
| redirect              | int                   | 最大重定向次数
| keep_alive            | bool                  | 是否保持连接
| content_type          | string                | 发送的内容编码类型
| data                  | `array`  | `string`   | 发送的数据
| before                | `callable`  | `array` | 请求前拦截器
| after                 | `callable`  | `array` | 响应后拦截器
| before_redirect       | `callable`  | `array` | 重定向后拦截器
| timeout               | float                 | 超时时间
| proxy                 | string                | 代理
| ssl                   | int                   | 是否开启ssl连接
| cafile                | string                | ca文件
| ssl_verify_peer       | bool                  | 验证服务器端证书
| ssl_allow_self_signed | bool                  | 允许自签名证书
| iconv                 | array                 | 指定编码转换
| exception_report      | int                   | 异常报告级别
| exception_handle      | callable\|array       | 异常自定义处理函数
| retry                 | callable              | 自动重试拦截器
| retry_time            | int                   | 自动重试次数
| use_pool              | bool\|int             | 连接池
 * Class HttpClient
 * @author gyy
 * @package App\Utils\Http
 */
class HttpClient
{
    private static $instance = null;

    private function __construct(){}

    private function __clone(){}

    /**
     * 单例获取
     * @return HttpClient|null
     */
    public static function getInstance()
    {
        self::$instance || self::$instance = new static();
        return self::$instance;
    }

    /**
     * 单个请求
     * 默认content-type: application/json
     * 默认超时60s
     * @param null $data 请求数据
     * @param array $options 请求设置
     * @param bool $isRaw
     * @return bool|Saber\Request|Saber\Response|array
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function request($data = null, $options = [], $isRaw = false)
    {
        if (!$options['base_uri'] || !$options['uri']) {
            return false;
        }

        $saber = Saber::create();
        $saber->exceptionReport(HttpExceptionMask::E_ALL);
        $uri = $options['uri'] ?? '';
        $saber->exceptionHandle(function (\Exception $e) use ($uri) {
            LogWriter::error('request ' . $uri . '\'s exception' . get_class($e) . ' occurred, exception message: ' . $e->getMessage());
        });

        !isset($options['method']) && $options['method'] = 'GET';

        $options['headers']['X-Log-Id'] = getLogId();

        null !== $data && $options['data'] = $data;

        if (!isset($options['content-type']) || !isset($options['headers']['content-type']) || !isset($options['headers']['Content-Type'])) {
            $options['content-type'] = ContentType::JSON;
        }

        if (!isset($options['timeout'])) {
            $options['timeout'] = Constant::HTTP_TIMEOUT;
        }

        if (!isset($options['use_pool'])) {
            $options['use_pool'] = Constant::HTTP_CLIENT_POOL;
        }

        $response = $saber->request($options);

        $return = null;
        if ($isRaw) {
            $return = $response;
        } elseif (false !== strpos($response->getHeaderLine('Content-Type'), ContentType::JSON)) {
            $return = $response->getParsedJsonArray();
        } elseif (false !== strpos($response->getHeaderLine('Content-Type'), ContentType::XML)) {
            $return = $response->getParsedXmlArray();
        } elseif (false !== strpos($response->getHeaderLine('Content-Type'), ContentType::HTML)) {
            $return = $response->getParsedDomObject();
        } else {
            $return = $response;
        }
        $response = null;
        unset($response);
        return $return;
    }

    /**
     * 并发请求
     *
     * @param array $requests       并发请求，配置参数与上面的通用配置一样
     * @example $request = [
     *      'key1' => [
     *               'method' => 'post', 'uri' => '/service/dispatch?node=1001000', 'data' => ['test' => 1]
     *           ],
     *      'key2' => [
     *               'method' => 'post', 'uri' => '/service/dispatch?node=1001001', 'data' => ['test' => 2]
     *           ]
     * ]
     * @param array $commonOptions  通用配置，如果每个需并发的请求的配置一样，只需在这里设置，配置参数与上面的通用配置一样
     * @param null $baseURi         通用baseUri，如：http://127.0.0.1，如果不是通用的，请在$request里面单独设置base_uri
     * @return array|bool           返回结果与$request中的key值对应
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function multi(array $requests, $commonOptions = [], $baseURi = null)
    {
        if (count($requests) == 0 || count($requests) > Constant::HTTP_MAX_MULTI_REQUEST_COUNT) {
            return false;
        }

        $new = [];
        $requestsKey = [];
        $requestsContentType = [];
        foreach ($requests as $key => $request) {
            if (!isset($request['method']) || !isset($request['uri'])) {
                return false;
            }
            if (!$baseURi && !isset($request['base_uri'])) {
                return false;
            }
            if (!isset($request['base_uri'])) {
                $request['base_uri'] = $baseURi;
            }
            if (!isset($request['use_pool'])) {
                $request['use_pool'] = Constant::HTTP_CLIENT_POOL;
            }
            $new[] = $request;
            $requestsKey[] = $key;
            $contentType = '';
            isset($request['content-type']) && $contentType = $request['content-type'];
            isset($request['headers']['content-type']) && $contentType = $request['headers']['content-type'];
            isset($request['headers']['Content-Type']) && $contentType = $request['headers']['Content-Type'];
            $requestsContentType[$key] = $contentType;
        }

        $saber = Saber::create();
        $saber->exceptionReport(HttpExceptionMask::E_ALL);
        $saber->exceptionHandle(function (\Exception $e) {
            LogWriter::error('multi requests\'s exception' . get_class($e) . ' occurred, exception message: ' . $e->getMessage());
        });

        if (!isset($commonOptions['content-type']) || !isset($commonOptions['headers']['content-type']) || !isset($commonOptions['headers']['Content-Type'])) {
            $commonOptions['content-type'] = ContentType::JSON;
        }

        $commonOptions['headers']['X-Log-Id'] = getLogId();

        if (!isset($commonOptions['timeout'])) {
            $commonOptions['timeout'] = Constant::HTTP_TIMEOUT;
        }

        $responses = $saber->requests($new, $commonOptions);
        $returns = [];
        foreach ($responses as $key => $val) {
            $trueKey = $requestsKey[$key];
            $returns[$trueKey] = $val;
        }
        $responses = null;
        unset($responses);
        return $returns;
    }
}
