<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use Biz\InfoSecurity\Service\MobileMaskService;
use Topxia\Service\Common\ServiceKernel;
use VipPlugin\Biz\Vip\Service\VipService;

class CourseMemberFilter extends Filter
{
    protected $simpleFields = [
        'id', 'courseId', 'deadline', 'courseSetId',
    ];

    protected $publicFields = [
        'user', 'levelId', 'learnedNum', 'noteNum', 'noteLastUpdateTime', 'isLearned', 'finishedTime', 'role', 'locked', 'createdTime', 'lastLearnTime', 'lastViewTime', 'access', 'learnedCompulsoryTaskNum', 'expire',
        'isContractSigned', 'learningProgressPercent', 'joinedChannel', 'remark',
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

        // 去掉长期有效
        if (isset($data['expire']['deadline']) && 0 == $data['expire']['deadline']) {
            unset($data['expire']['deadline']);
        }

        if (!empty($data['expire']['deadline'])) {
            $data['expire']['deadline'] = date('c', $data['expire']['deadline']);
        }

        if ($this->isPluginInstalled('Vip')) {
            $vipMember = $this->getVipService()->getMemberByUserId($data['user']['id']);
            $data['levelId'] = empty($vipMember) ? 0 : $vipMember['levelId'];
        }

        $user = $data['user'];
        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
        $data['user']['encryptedMobile'] = empty($user['verifiedMobile']) ? '' : $this->getMobileMaskService()->encryptMobile($user['verifiedMobile']);
        $data['user']['verifiedMobile'] = empty($user['verifiedMobile']) ? '' : $this->getMobileMaskService()->maskMobile($user['verifiedMobile']);
        global $kernel;
        $data['user']['canSendMessage'] = $kernel->getContainer()->get('web.twig.extension')->canSendMessage($user['id']);
    }

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return ServiceKernel::instance()->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return MobileMaskService
     */
    private function getMobileMaskService()
    {
        return $this->createService('InfoSecurity:MobileMaskService');
    }
}
