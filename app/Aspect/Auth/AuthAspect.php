<?php


namespace App\Aspect\Auth;

use App\Constant\Constant;
use Swoft\Aop\Annotation\Mapping\Aspect;
use Swoft\Aop\Annotation\Mapping\Before;
use Swoft\Aop\Annotation\Mapping\PointAnnotation;
use Swoft\Context\Context;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

/**
 * 鉴权失败，请抛出AuthException，code为Constant::AUTH_FAIL_CODE
 * 成功不做处理
 * Class AuthAspect
 * @author gyy
 * @Aspect(order=1)
 * @PointAnnotation(include={RequestMapping::class})
 */
class AuthAspect
{
    /**
     * @Before()
     * @throws AuthException
     */
    public function before()
    {
        $request = Context::mustGet()->getRequest();
        //var_dump($request->json());
        //throw new AuthException('auth failed', Constant::AUTH_FAIL_CODE);
        // todo 鉴权
    }
}
