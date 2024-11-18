<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use Biz\InfoSecurity\Service\MobileMaskService;

class ItemBankExerciseMemberFilter extends Filter
{
    protected $publicFields = [
        'id', 'exerciseId', 'questionBankId', 'role', 'locked', 'user', 'joinedChannel', 'deadline', 'createdTime', 'joinedChannelText', 'learningProgressPercent',
        'remark'
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['user'])) {
            $user = $data['user'];
            $userFilter = new UserFilter();
            $userFilter->filter($data['user']);
            $data['user']['encryptedMobile'] = empty($user['verifiedMobile']) ? '' : $this->getMobileMaskService()->encryptMobile($user['verifiedMobile']);
            $data['user']['verifiedMobile'] = empty($user['verifiedMobile']) ? '' : $this->getMobileMaskService()->maskMobile($user['verifiedMobile']);
            global $kernel;
            $data['user']['canSendMessage'] = $kernel->getContainer()->get('web.twig.extension')->canSendMessage($user['id']);

            if (!empty($data['isOldUser']) && is_array($data['user']['avatar'])) {
                $data['user']['avatar'] = $data['user']['avatar']['small'];
                unset($data['isOldUser']);
            }
        }
    }

    /**
     * @return MobileMaskService
     */
    private function getMobileMaskService()
    {
        return $this->createService('InfoSecurity:MobileMaskService');
    }
}
