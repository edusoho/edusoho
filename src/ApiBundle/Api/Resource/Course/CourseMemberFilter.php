<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use Topxia\Service\Common\ServiceKernel;
use VipPlugin\Biz\Vip\Service\VipService;

class CourseMemberFilter extends Filter
{
    protected $simpleFields = [
        'id', 'courseId', 'deadline', 'courseSetId',
    ];

    protected $publicFields = [
        'user', 'levelId', 'learnedNum', 'noteNum', 'noteLastUpdateTime', 'isLearned', 'finishedTime', 'role', 'locked', 'createdTime', 'lastLearnTime', 'lastViewTime', 'access', 'learnedCompulsoryTaskNum',
    ];

    protected function simpleFields(&$data)
    {
        if ($data['deadline']) {
            $data['deadline'] = date('c', $data['deadline']);
        }
    }

    protected function publicFields(&$data)
    {
        $data['noteLastUpdateTime'] = date('c', $data['noteLastUpdateTime']);
        $data['finishedTime'] = date('c', $data['finishedTime']);
        $data['lastLearnTime'] = date('c', $data['lastLearnTime']);
        $data['lastViewTime'] = date('c', $data['lastViewTime']);

        if ($this->isPluginInstalled('Vip')) {
            $vipMember = $this->getVipService()->getMemberByUserId($data['user']['id']);
            $data['levelId'] = empty($vipMember) ? 0 : $vipMember['levelId'];
        }

        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return ServiceKernel::instance()->createService('VipPlugin:Vip:VipService');
    }
}
