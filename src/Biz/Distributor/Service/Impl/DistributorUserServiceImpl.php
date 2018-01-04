<?php

namespace Biz\Distributor\Service\Impl;

class DistributorUserServiceImpl extends BaseDistributorServiceImpl
{
    protected function convertData($user)
    {
        return array(
           'user_source_id' => $user['id'],
           'nickname' => $user['nickname'],
           'mobile' => $user['verifiedMobile'],
           'registered_time' => $user['createdTime'],
           'token' => $user['token'],
        );
    }

    protected function getJobType()
    {
        return 'User';
    }

    protected function getNextJobType()
    {
        return 'Order';
    }

    public function getPostMethod()
    {
        return 'postStudents';
    }
}
