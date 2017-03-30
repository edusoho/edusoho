<?php

namespace ApiBundle\Api\Util;

use AppBundle\Common\ArrayToolkit;

class UserAssociateUtil
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function single(&$object, array $userIdFields)
    {
        $userIds = $this->findUserIds($object, $userIdFields);

        $users = $this->findUsers($userIds);
        $this->replaceUser($users, $object, $userIdFields);
    }

    public function multiple(&$objects, array $userIdFields)
    {
        $userIds = array();
        foreach ($objects as $object) {
            $userIds = array_merge($userIds, $this->findUserIds($object, $userIdFields));
        }

        $users = $this->findUsers($userIds);

        foreach ($objects as &$object) {
            $this->replaceUser($users, $object, $userIdFields);
        }
    }

    private function findUserIds($object, $userIdFields)
    {
        $userIds = array();
        foreach ($userIdFields as $userIdField) {
            if (is_array($object[$userIdField])) {
                $userIds = $userIds + $object[$userIdField];
            } else {
                $userIds[] = $object[$userIdField];
            }
        }

        return $userIds;
    }

    private function replaceUser($users, &$object, $userIdFields)
    {
        foreach ($userIdFields as $userIdField) {
            $newUserField = str_replace('Id', '', $userIdField);
            $object[$newUserField] = array();
            $userId = $object[$userIdField];
            if (is_array($userId)) {
                foreach ($userId as $uid) {
                    $object[$newUserField][] = $users[$uid];
                }
            } else {
                $object[$newUserField] = $users[$object[$userIdField]];
            }

            unset($object[$userIdField]);
        }

    }

    /**
     * @param $userIds
     * @return mixed
     */
    private function findUsers($userIds)
    {
        $userIds = array_unique($userIds);
        $users = $this->getUserService()->findUsersByIds($userIds);
        return ArrayToolkit::index($users, 'id');
    }

    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}