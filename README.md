# 规范说明
## 1.环境要求
- PHP版本：7.2.19+
- swoole版本：4.3.5+
## 2.Response Header
- 返回的header头都会包含：X-Log-Id，该id为当前请求的唯一标记，通过该id可以方便的查询该请求的日志
## 3.日志
- 日志统一使用LogWriter记录，如

```
LogWriter::info('logid: ' . getLogId() . ' request ' . $options['uri'] . '\'s result: ' . $response);
```
- LogWriter提供info，error，warning，pushlog等方法
- 使用==除pushlog方法外的方法记录日志时，必须手动调用getLogId()方法获取当前请求的唯一logid，并记录在日志中，方便后续链路追踪及查询
- 建议使用pushlog记录日志，该方法无需关心logid，已经对该方法自动绑定了当前请求的logid
- 所有方法记录日志的长度不能超过1024*1024字节，超过会自动截断，对于数组是转为json后再截断
## 4.权限切面
- 权限切面会针对所有使用了@RequestMapping()注解的方法，再调用该方法之前执行
- 在权限切面判断权限，通过不用做任何返回，失败抛出code为Constant::AUTH_FAIL_CODE的AuthException异常，抛出异常后，会自动返回给调用方，返回结果为：

```
{
    "code": 11,
    "status": false,
    "msg": "鉴权失败，请联系管理员",
    "data": []
}
```
## 5.常量配置
- 所有常量配置统一在Constant文件夹
- Constant.php通用常量配置
- Response.php返回常量配置
## 6.统一异常处理
- 现只有针对http的统一异常处理，如有对RPC等异常的统一处理需求，请自行实现
- http统一异常处理会记录该异常的详细信息，方便查询日志
- 异常返回：当APP_DEBUG设置为1时，会返回该异常的详细信息，建议在开发时使用；当APP_DEBUG为0时（生产环境必须设置为0），会返回

```
{
    "code": 10,
    "status": false,
    "msg": "系统错误，请联系管理员",
    "data": []
}
```
## 7.RequestBean
- 现实现了针对当前请求的RequestBean，该bean的生命周期：当前请求，当前请求结束后，该bean自动销毁，包括里面保存的上下文（只有RequestBean的生命周期是当前请求，其他类型的bean的生命周期等于该应用进程的生命周期）
- 该bean提供了set，get方法保存、读取上下文，要保存、读取上下文时请调用该方法
- 提供requestBean()函数获取当前RequestBean，requestBean()->get获取当前请求上下文，requestBean()->set()保存当前请求上下文
- 提供getLogId()函数获取当前请求的唯一请求id
> 禁止使用static定义静态变量来保存数据，不要定义类的属性用来保存动态数据等，这些操作都可能造成数据混乱，要保存数据统一使用上下文保存，上下文都是绑定了当前请求的协程id的（非常重要）
## 8.Controller
- 所有新建控制器必须继承BaseController
- 所有结果返回必须通过$this->json()返回
- BaseController提供paramsFilter()方法过滤参数的特殊字符
- 所有新建controller必须在类上使用@Controller注解，所有提供给对外的接口，必须在方法下使用@RequestMapping注解
- 建议controller只做简单参数判断，所有逻辑处理请在Model/Logic中处理
- 如controller有特殊中间件要求，可在Http/Middleware文件夹中实现，对controller使用@Middleware注解该中间件即可
## 9.Listener
- 事件监听，框架提供很多事件监听，有需要的可自行实现。如需要在swoole启动调用set设置swoole参数之前时做某些操作，可以使用@Listener(ServerEvent::BEFORE_SETTING)注解，实现该事件监听即可
## 10.Middleware
- 框架通用中间件，Middleware目录下，现默认实现了跨域中间件，且已对每个请求生效，使用者无需关心跨域
- 如有其他通用中间件，自行实现，只需在bean.php中添加如下配置
- 
```
'httpDispatcher' => [
        // Add global http middleware
        'middlewares' => [
            \App\Middleware\CorsMiddleware::class,
            // other middleware
        ],
    ],
```
## 11.Model
- 数据层包括Dao，Entity，Logic
- Entity实体，每张表必须对应一个实体，可使用php bin/swoft entity:c [table]自动生成实体，生成实体自动保存在该文件夹
- Dao数据库操作层，通过对实体操作实现增删改查，如有特别复杂的业务，才可使用原生sql，其他情况统一使用实体
> 通过实体查询的数据返回的都是该实体，可通过get/set方法获取/设置数据
- Logic逻辑处理层，controller先调用该层，进行逻辑处理，拼接数据等，然后调用Dao层增删改查数据
## 12.Task
- Task分为协程任务和异步任务
- 协程任务：任务开始后会进行协程切换，让出时间片，执行其他协程，当任务完成后，在回到该协程，继续向下执行。使用范围：对任务返回结果强依赖的
- 异步任务：任务开始后直接向下执行，不会进行协程切换。适用范围：对结果不依赖的请求。异步任务会在执行完成后，触发TaskEvent::FINISH事件，需要监听该事件，如有需要，可以进行后续处理
## 13.Utils
- 实现httpClient，所有请求有需要http请求的，必须使用该httpClient
- httpClient为单例，获取httpClient

```
$http = HttpClient::getInstance();
```
- httpClient提供两个方法：request（单个请求）和multi（多个请求，并发），具体适用方法查看类注释
## 14.配置
- config目录编写通用配置
- config目录下有dev,test,pre,pro四个目录，对应开发，测试，预发布，正式环境，同过.env文件ENV = dev|test|pre|pro切换，各个目录下的配置在不同环境互不影响
- base.php所有环境通用配置
- 配置文件通过函数config()读取
- bean.php为系统，连接池等配置，也包括一些swoole的配置，如每个环境不同，可根据config目录下的四个目录来配置，再在这个文件读取配置，达到不同环境配置不同的目的
## 15.严禁使用
- 禁止die()、exit()函数
- 禁止使用$_GET、$_POST、$GLOBALS、$_SERVER、$_FILES、$_COOKIE、$_SESSION、$_REQUEST、$_ENV等超全局变量
- 谨慎使用global、static关键字
## 16.IDE
- 如使用PHPstorm，可以安装PHP Annotations插件
- 注意@var的使用，在注解或其他变量说明时，可通过@var来制定变量属性，方便IDE代码提示
## 17.部署及运行
- docker镜像：直接运行命令：docker pull ccr.ccs.tencentyun.com/young/swoole-php:swoole4.4.2 拉取镜像
- 启动容器：docker run -d --name $name -p $port1:80 -p $port2:22 -v $path:/var/www/swoft ccr.ccs.tencentyun.com/young/swoole-php:swoole4.4.2 启动容器
> 其中，$name为你容器名称，$port1,$port2分别为你宿主机暴露的http端口和ssh端口，$path为你当前框架的完整目录
- 通过ssh进入刚启动的容器，账号：root,密码：123456，进入容器后，执行命令：php /var/www/swoft/bin/swoft http:start启动即可
