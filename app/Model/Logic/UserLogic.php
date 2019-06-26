<?php declare(strict_types=1);


namespace App\Model\Logic;


use App\Model\Dao\UserDao;
use App\Model\Entity\User;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * Class UserLogic
 * @Bean()
 * @package App\Model\Logic
 */
class UserLogic
{
    /**
     * @Inject()
     * @var UserDao
     */
    private $user;
    public function getUser($id)
    {
        /**
         * @var User $result
         */
        $result = $this->user->getUser($id);
//        $result = $result->toArray();
        $result->setTest('test');
        var_dump($result);
        return $result;
    }
}
