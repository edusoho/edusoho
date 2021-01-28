<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;

class User extends BaseProvider
{
    private $_publicFields = array(
        'id', 'nickname', 'title', 'point', 'largeAvatar', 'createdTime', 'updatedTime', 'roles',
    );

    private $_publicProfileFields = array(
        'about',
    );

    public function filter($res)
    {
        $filteredRes = array();

        foreach ($this->_publicFields as $key) {
            $filteredRes[$key] = $res[$key];
        }

        if (!empty($res['profile'])) {
            foreach ($this->_publicProfileFields as $key) {
                $filteredRes[$key] = $res['profile'][$key];
            }
        }

        $filteredRes['roles'] = in_array('ROLE_TEACHER', $res['roles']) ? array('teacher') : array('student');

        $filteredRes['avatar'] = isset($filteredRes['largeAvatar']) ? $this->getFileUrl($filteredRes['largeAvatar']) : '';
        unset($filteredRes['largeAvatar']);

        $filteredRes['createdTime'] = date('c', $filteredRes['createdTime']);
        $filteredRes['updatedTime'] = date('c', $filteredRes['updatedTime']);

        return $filteredRes;
    }
}
