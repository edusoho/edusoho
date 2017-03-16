<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Controller\Callback\Resource\BaseResource;

class User extends BaseResource
{
    private $_publicFields = array(
        'id', 'nickname', 'title', 'point', 'largeAvatar', 'createdTime', 'updatedTime', 'roles',
    );

    private $_publicProfileFields = array(
        'about',
    );

    public function filter($res)
    {
        $returnRes = array();

        foreach ($this->_publicFields as $key) {
            $returnRes[$key] = $res[$key];
        }

        if (!empty($res['profile'])) {
            foreach ($this->_publicProfileFields as $key) {
                $returnRes[$key] = $res['profile'][$key];
            }
        }

        $returnRes['roles'] = in_array('ROLE_TEACHER', $res['roles']) ? array('teacher') : array('student');

        $returnRes['avatar'] = isset($returnRes['largeAvatar']) ? $this->getFileUrl($returnRes['largeAvatar']) : '';
        unset($returnRes['largeAvatar']);

        $returnRes['createdTime'] = date('c', $returnRes['createdTime']);
        $returnRes['updatedTime'] = date('c', $returnRes['updatedTime']);

        return $returnRes;
    }
}
