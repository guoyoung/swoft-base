<?php declare(strict_types=1);


namespace App\Model\Dao;


use App\Model\Entity\User;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class UserDao
 * @Bean()
 * @package App\Model\Dao
 */
class UserDao
{
    public function getUser($id)
    {
        return User::find($id);
    }
}
