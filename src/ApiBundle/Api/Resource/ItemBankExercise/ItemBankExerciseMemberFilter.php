<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use Biz\InfoSecurity\Service\MobileMaskService;

class ItemBankExerciseMemberFilter extends Filter
{
    protected $publicFields = [
        'id', 'exerciseId', 'questionBankId', 'role', 'locked', 'user', 'joinedChannel', 'deadline', 'createdTime', 'joinedChannelText', 'learningProgressPercent',
        'remark','needHideNickname'
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
            if ($data['needHideNickname']) {
                $data['user']['nickname'] = $this->hideUserNickname($data['user']['nickname']);
            }
            if (!empty($data['isOldUser']) && is_array($data['user']['avatar'])) {
                $data['user']['avatar'] = $data['user']['avatar']['small'];
                unset($data['isOldUser']);
            }
        }
    }

    protected function hideUserNickname($nickname)
    {
        // 判断用户名长度并进行处理
        if (2 == mb_strlen($nickname, 'UTF-8')) {
            // 如果用户名是两个字，显示第一个字并用 '*' 代替第二个字
            $nickname = mb_substr($nickname, 0, 1).'*';
        } elseif (mb_strlen($nickname, 'UTF-8') > 2) {
            // 如果用户名超过两个字，显示第一个字和最后一个字，中间字用 '*' 替代
            $firstChar = mb_substr($nickname, 0, 1);
            $lastChar = mb_substr($nickname, -1, 1);
            $nickname = $firstChar.str_repeat('*', mb_strlen($nickname, 'UTF-8') - 2).$lastChar;
        }

        return $nickname;
    }

    /**
     * @return MobileMaskService
     */
    private function getMobileMaskService()
    {
        return $this->createService('InfoSecurity:MobileMaskService');
    }
}
