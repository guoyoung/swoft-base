<?php
/**
 * Custom global functions
 */

if (!function_exists("requestBean")) {
    /**
     * 与顶级协程id绑定
     * 获取当前请求的RequestBean
     * @return mixed|object
     */
    function requestBean()
    {
        return \Swoft\Bean\BeanFactory::getRequestBean('requestBean', \Swoft\Co::tid());
    }
}

if (!function_exists("getLogId")) {
    /**
     * 与顶级协程id绑定
     * 获取当前请求的唯一log id，如通过sgo创建的协程也可获取该id
     * @return mixed
     */
    function getLogId()
    {
        return requestBean()->getLogId();
    }
}
