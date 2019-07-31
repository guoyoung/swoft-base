<?php declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Base\BaseController;
use App\Model\Logic\UserLogic;
use App\Utils\Http\HttpClient;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Redis\Pool;
use Swoft\Redis\Redis;
use Swoft\Task\Task;

/**
 * Class CoController
 * @Controller()
 */
class TestController extends BaseController
{
    /**
     * @RequestMapping()
     * @return \Swoft\Http\Message\Response|\Swoft\WebSocket\Server\Message\Response
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function http()
    {
        $http = HttpClient::getInstance();
        $res = $http->multi([
            'key1' => ['method' => 'post', 'uri' => '/service/dispatch?node=1001000', 'base_uri' => 'http://10.200.81.99:8081', 'data' => ['test' => 1]],
            'key2' => ['method' => 'post', 'uri' => '/service/dispatch?node=1001001', 'data' => ['test' => 2]],
            'key3' => ['method' => 'post', 'uri' => '/service/dispatch?node=1001002', 'data' => ['test' => 3]],
        ],[
            'headers' => [
                'token' => 'eyJhbGciOiJIUzUxMiJ9.eyJjcmVhdGVkIjoxNTY0NTU4NDQwNTcwLCJleHAiOjQxMDI0NDg0NjAsInVzZXJuYW1lIjoibGljaGFvMSJ9.0naisXPgluXeIJCw2fycPscB-k7a1UMXvxDxgkmqO3PX0h7ytafswa7DeOqdm9vfqjrmJz-VaVyrKIe73qGEFw'
            ]
        ], 'http://10.200.81.99:8081');
        array_walk($res, function (&$val) {
            $val = $val->getParsedJsonArray();
        });
        return $this->json($res);
    }

    /**
     * @Inject()
     * @var UserLogic
     */
    private $user;

    /**
     * @RequestMapping()
     * @param Request $request
     * @return \Swoft\Http\Message\Response|\Swoft\WebSocket\Server\Message\Response
     */
    public function db(Request $request)
    {
        $content = $request->getParsedBody();
        $result = $this->user->getUser($content['id'] ?? '');
        return $this->json($result);
    }

    /**
     * @Inject()
     * @var Pool
     */
    private $redis;
    /**
     * @RequestMapping()
     * @return \Swoft\Http\Message\Response|\Swoft\WebSocket\Server\Message\Response
     */
    public function redis()
    {
        Redis::set('test', 'test');
        $this->redis->set('test1', ['test1']);
        return $this->json($this->redis->get('test1'));
    }

    /**
     * @RequestMapping()
     */
    public function task()
    {
        // 协程任务
//        $result = Task::co('testTask', 'list', [123]);
//        var_dump($result);

        // 异步任务
        $result = Task::async('testTask', 'list', [123]);
        var_dump($result);
    }
}
