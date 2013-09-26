<?php
namespace Topxia\Service\User\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Common\SimpleValidator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\User\UserActionService;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class UserActionServiceImpl extends BaseService implements UserActionService
{

    public function getUserAction($id)
    {
        $user = $this->getUserActionDao()->getUser($id);
        if(!$user){
            return null;
        } else {
            return UserActionSerialize::unserialize($user);
        }
    }

    public function findUserActionsByIds(array $ids)
    {
        $users = UserActionSerialize::unserializes(
            $this->getUserActionDao()->findUsersByIds($ids)
        );
        return ArrayToolkit::index($users, 'id');
    }


    public function searchUserActionCount(array $conditions)
    {
        return $this->getUserActionDao()->searchUserCount($conditions);
    }


    public function searchUserActions(array $conditions, array $orderBy, $start, $limit)
    {
        $users = $this->getUserActionDao()->searchUsers($conditions,$orderBy, $start, $limit);
        return UserActionSerialize::unserializes($users);
    }

    private function getUserActionDao()
    {
        return $this->createDao('User.UserActionDao');
    }


}

class UserActionSerialize
{
    public static function serialize(array $user)
    {
        $user['roles'] = empty($user['roles']) ? '' :  '|' . implode('|', $user['roles']) . '|';
        return $user;
    }

    public static function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }
        $user['roles'] = empty($user['roles']) ? array() : explode('|', trim($user['roles'], '|')) ;
        return $user;
    }

    public static function unserializes(array $users)
    {
        return array_map(function($user) {
            return UserActionSerialize::unserialize($user);
        }, $users);
    }

}