<?php


namespace App\Listener;


use App\Constant\Constant;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Server\ServerEvent;

/**
 * Class BeforeSettingListener
 * @Listener(ServerEvent::BEFORE_SETTING)
 */
class BeforeSettingListener implements EventHandlerInterface
{

    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {
        // 创建swoole.log文件夹
        $dir = alias(Constant::LOG_FILE);
        $dir = dirname($dir);
        if (null !== $dir && !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
