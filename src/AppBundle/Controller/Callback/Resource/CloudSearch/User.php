<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Controller\Callback\Resource\BaseResource;

class User extends BaseResource
{
    private $_unsetFields = array(
        'password', 'salt', 'payPassword', 'payPasswordSalt',
    );

    private $_publicFields = array(
        'id', 'nickname', 'title', 'point', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'createdTime', 'updatedTime', 'roles',
    );

    private $_publicProfileFields = array(
        'about',
    );

    private $_privateFields = array(
        'id', 'nickname', 'title', 'tags', 'type', 'roles',
        'point', 'coin', 'smallAvatar', 'mediumAvatar', 'largeAvatar',
        'email', 'emailVerified', 'promoted', 'promotedTime', 'locked', 'lockDeadline',
        'loginTime', 'loginIp', 'approvalTime', 'approvalStatus', 'newMessageNum', 'newNotificationNum',
        'createdIp', 'createdTime', 'updatedTime',
    );

    private $_privateProfileFields = array(
        'truename', 'idcard', 'gender', 'birthday', 'city', 'mobile', 'qq',
        'signature', 'about', 'company', 'job', 'school', 'class', 'weibo', 'weixin', 'site',
    );

    public function filter($res)
    {
        foreach ($this->_unsetFields as $key) {
            unset($res[$key]);
        }

        $currentUser = $this->getCurrentUser();

        $returnRes = array();

        if ($currentUser->isAdmin() || ($currentUser['id'] == $res['id'])) {
            foreach ($this->_privateFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            if (!empty($res['profile'])) {
                foreach ($this->_privateProfileFields as $key) {
                    $returnRes[$key] = $res['profile'][$key];
                }
            }
        } else {
            foreach ($this->_publicFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            if (in_array('ROLE_TEACHER', $returnRes['roles'])) {
                $returnRes['roles'] = array('ROLE_TEACHER');
            } else {
                $returnRes['roles'] = array('ROLE_USER');
            }

            if (!empty($res['profile'])) {
                foreach ($this->_publicProfileFields as $key) {
                    $returnRes[$key] = $res['profile'][$key];
                }
            }
        }

        $res = $returnRes;

        foreach (array('smallAvatar', 'mediumAvatar', 'largeAvatar') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        foreach (array('promotedTime', 'loginTime', 'approvalTime', 'createdTime') as $key) {
            if (!isset($res[$key])) {
                continue;
            }

            $res[$key] = date('c', $res[$key]);
        }

        $res['updatedTime'] = date('c', $res['updatedTime']);

        return $res;
    }
}
