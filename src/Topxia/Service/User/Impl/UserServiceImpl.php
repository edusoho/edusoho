<?php
namespace Topxia\Service\User\Impl;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\SimpleValidator;
use Topxia\Common\StringToolkit;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\User\UserService;

class UserServiceImpl extends BaseService implements UserService
{
    public function getUser($id, $lock = false)
    {
        $user = $this->getUserDao()->getUser($id, $lock);
        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function findUsersCountByLessThanCreatedTime($endTime)
    {
        return $this->getUserDao()->findUsersCountByLessThanCreatedTime($endTime);
    }

    public function getUserProfile($id)
    {
        return $this->getProfileDao()->getProfile($id);
    }

    public function getUserByNickname($nickname)
    {
        $user = $this->getUserDao()->findUserByNickname($nickname);
        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function getUserByLoginField($keyword)
    {
        if (SimpleValidator::email($keyword)) {
            $user = $this->getUserDao()->findUserByEmail($keyword);
        } elseif (SimpleValidator::mobile($keyword)) {
            $user = $this->getUserDao()->findUserByVerifiedMobile($keyword);
        } else {
            $user = $this->getUserDao()->findUserByNickname($keyword);
        }

        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function getUserByVerifiedMobile($mobile)
    {
        $user = $this->getUserDao()->findUserByVerifiedMobile($mobile);
        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function getUserCountByMobileNotEmpty()
    {
        return $this->getUserDao()->getCountByMobileNotEmpty();
    }

    public function getUserByEmail($email)
    {
        if (empty($email)) {
            return null;
        }

        $user = $this->getUserDao()->findUserByEmail($email);
        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function findUsersByIds(array $ids)
    {
        $users = UserSerialize::unserializes(
            $this->getUserDao()->findUsersByIds($ids)
        );
        return ArrayToolkit::index($users, 'id');
    }

    public function findUserProfilesByIds(array $ids)
    {
        $userProfiles = $this->getProfileDao()->findProfilesByIds($ids);
        return ArrayToolkit::index($userProfiles, 'id');
    }

    public function searchUsers(array $conditions, array $orderBy, $start, $limit)
    {
        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = strtoupper($conditions['nickname']);
        }

        $users = $this->getUserDao()->searchUsers($conditions, $orderBy, $start, $limit);
        return UserSerialize::unserializes($users);
    }

    public function searchUserCount(array $conditions)
    {
        return $this->getUserDao()->searchUserCount($conditions);
    }

    public function searchUserProfiles(array $conditions, array $orderBy, $start, $limit)
    {
        $profiles = $this->getProfileDao()->searchProfiles($conditions, $orderBy, $start, $limit);
        return $profiles;
    }

    public function searchUserProfileCount(array $conditions)
    {
        return $this->getProfileDao()->searchProfileCount($conditions);
    }

    public function searchApprovals(array $conditions, array $orderBy, $start, $limit)
    {
        $approvals = $this->getUserApprovalDao()->searchApprovals($conditions, $orderBy, $start, $limit);
        return $approvals;
    }

    public function searchApprovalsCount(array $conditions)
    {
        return $this->getUserApprovalDao()->searchApprovalsCount($conditions);
    }

    public function setEmailVerified($userId)
    {
        $this->getUserDao()->updateUser($userId, array('emailVerified' => 1));
        $user = $this->getUser($userId);
        $this->dispatchEvent('email.verify', new ServiceEvent($user));
    }

    public function changeNickname($userId, $nickname)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，设置帐号失败！');
        }

        if (!SimpleValidator::nickname($nickname)) {
            throw $this->createServiceException('用户昵称格式不正确，设置帐号失败！');
        }

        $existUser = $this->getUserDao()->findUserByNickname($nickname);

        if ($existUser && $existUser['id'] != $userId) {
            throw $this->createServiceException('昵称已存在！');
        }

        $updatedUser = $this->getUserDao()->updateUser($userId, array('nickname' => $nickname));
        $this->dispatchEvent('user.change_nickname', new ServiceEvent($updatedUser));
        $this->getLogService()->info('user', 'nickname_change', "修改用户名{$user['nickname']}为{$nickname}成功");
    }

    public function changeUserOrg($userId, $orgCode)
    {
        $user = $this->getUser($userId);
        if (empty($user) || ($user['orgCode'] == $orgCode)) {
            return;
        }

        if (empty($orgCode)) {
            $fields = array('orgCode' => '1.', 'orgId' => 1);
        } else {
            $org = $this->getOrgService()->getOrgByOrgCode($orgCode);
            if (empty($org)) {
                throw $this->createNotFoundException("org #{$orgCode} not found");
            }
            $fields = array('orgCode' => $org['orgCode'], 'orgId' => $org['id']);
        }

        $user = $this->getUserDao()->updateUser($userId, $fields);

        return $user;
    }

    public function batchUpdateOrg($userIds, $orgCode)
    {
        if (!is_array($userIds)) {
            $userIds = array($userIds);
        }
        $fields = $this->fillOrgId(array('orgCode' => $orgCode));

        foreach ($userIds as $userId) {
            $user = $this->getUserDao()->updateUser($userId, $fields);
        }
    }

    public function changeEmail($userId, $email)
    {
        if (!SimpleValidator::email($email)) {
            throw $this->createServiceException('Email格式不正确，变更Email失败。');
        }

        $user = $this->getUserDao()->findUserByEmail($email);

        if ($user && $user['id'] != $userId) {
            throw $this->createServiceException('Email({$email})已经存在，Email变更失败。');
        }

        $updatedUser = $this->getUserDao()->updateUser($userId, array('email' => $email));
        $this->dispatchEvent('user.change_email', new ServiceEvent($updatedUser));
        return $updatedUser;
    }

    public function changeAvatar($userId, $data)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，头像更新失败！');
        }

        $fileIds = ArrayToolkit::column($data, "id");
        $files   = $this->getFileService()->getFilesByIds($fileIds);

        $files   = ArrayToolkit::index($files, "id");
        $fileIds = ArrayToolkit::index($data, "type");

        $fields = array(
            'smallAvatar'  => $files[$fileIds["small"]["id"]]["uri"],
            'mediumAvatar' => $files[$fileIds["medium"]["id"]]["uri"],
            'largeAvatar'  => $files[$fileIds["large"]["id"]]["uri"]
        );

        $oldAvatars = array(
            'smallAvatar'  => $user['smallAvatar'] ? $user['smallAvatar'] : null,
            'mediumAvatar' => $user['mediumAvatar'] ? $user['mediumAvatar'] : null,
            'largeAvatar'  => $user['largeAvatar'] ? $user['largeAvatar'] : null
        );

        $fileService = $this->getFileService();
        array_map(function ($oldAvatar) use ($fileService) {
            if (!empty($oldAvatar)) {
                $fileService->deleteFileByUri($oldAvatar);
            }
        }, $oldAvatars);

        $user = $this->getUserDao()->updateUser($userId, $fields);
        return UserSerialize::unserialize($user);
    }

    public function changeAvatarFromImgUrl($userId, $imgUrl, $options = array())
    {
        $filePath = $this->getKernel()->getParameter('topxia.upload.public_directory').'/tmp/'.$userId.'_'.time().'.jpg';
        $filePath = FileToolkit::downloadImg($imgUrl, $filePath);

        $file = new File($filePath);

        $groupCode = "tmp";
        $imgs      = array(
            'large'  => array("200", "200"),
            'medium' => array("120", "120"),
            'small'  => array("48", "48")
        );
        $options = array_merge($options, array(
            'x'      => "0",
            'y'      => "0",
            'x2'     => "200",
            'y2'     => "200",
            'w'      => "200",
            'h'      => "200",
            'width'  => "200",
            'height' => "200",
            'imgs'   => $imgs
        ));

        if (empty($options['group'])) {
            $options['group'] = "default";
        }

        $record    = $this->getFileService()->uploadFile($groupCode, $file);
        $parsed    = $this->getFileService()->parseFileUri($record['uri']);
        $filePaths = FileToolKit::cropImages($parsed["fullpath"], $options);

        $fields = array();

        foreach ($filePaths as $key => $value) {
            $file     = $this->getFileService()->uploadFile($options["group"], new File($value));
            $fields[] = array(
                "type" => $key,
                "id"   => $file['id']
            );
        }

        if (isset($options["deleteOriginFile"]) && $options["deleteOriginFile"] == 0) {
            $fields[] = array(
                "type" => "origin",
                "id"   => $record['id']
            );
        } else {
            $this->getFileService()->deleteFileByUri($record["uri"]);
        }

        return $this->changeAvatar($userId, $fields);
    }

    public function isNicknameAvaliable($nickname)
    {
        if (empty($nickname)) {
            return false;
        }

        $user = $this->getUserDao()->findUserByNickname($nickname);

        return empty($user) ? true : false;
    }

    public function isEmailAvaliable($email)
    {
        if (empty($email)) {
            return false;
        }

        $user = $this->getUserDao()->findUserByEmail($email);
        return empty($user) ? true : false;
    }

    public function isMobileAvaliable($mobile)
    {
        if (empty($mobile)) {
            return false;
        }

        $user = $this->getUserDao()->findUserByVerifiedMobile($mobile);
        return empty($user) ? true : false;
    }

    public function changePassword($id, $password)
    {
        $user = $this->getUser($id);

        if (empty($user) || empty($password)) {
            throw $this->createServiceException('参数不正确，更改密码失败。');
        }

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $fields = array(
            'salt'     => $salt,
            'password' => $this->getPasswordEncoder()->encodePassword($password, $salt)
        );

        $this->getUserDao()->updateUser($id, $fields);

        $this->markLoginSuccess($user['id'], $this->getCurrentUser()->currentIp);

        $this->getLogService()->info('user', 'password-changed', "用户{$user['email']}(ID:{$user['id']})重置密码成功");

        return true;
    }

    public function changePayPassword($userId, $newPayPassword)
    {
        $user = $this->getUser($userId);

        if (empty($user) || empty($newPayPassword)) {
            throw $this->createServiceException('参数不正确，更改支付密码失败。');
        }

        $payPasswordSalt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $fields = array(
            'payPasswordSalt' => $payPasswordSalt,
            'payPassword'     => $this->getPasswordEncoder()->encodePassword($newPayPassword, $payPasswordSalt)
        );

        $this->getUserDao()->updateUser($userId, $fields);

        $this->getLogService()->info('user', 'pay-password-changed', "用户{$user['email']}(ID:{$user['id']})重置支付密码成功");

        return true;
    }

    public function isMobileUnique($mobile)
    {
        $count = $this->searchUserCount(array('verifiedMobile' => $mobile));

        if ($count > 0) {
            return false;
        }

        return true;
    }

    public function changeMobile($id, $mobile)
    {
        $user = $this->getUser($id);

        if (empty($user) || empty($mobile)) {
            throw $this->createServiceException('参数不正确，更改失败。');
        }

        $fields = array(
            'verifiedMobile' => $mobile
        );

        $this->getUserDao()->updateUser($id, $fields);
        $this->updateUserProfile($id, array(
            'mobile' => $mobile
        ));
        $this->dispatchEvent('mobile.change', new ServiceEvent($user));
        $this->getLogService()->info('user', 'verifiedMobile-changed', "用户{$user['email']}(ID:{$user['id']})重置mobile成功");

        return true;
    }

    public function getUserSecureQuestionsByUserId($userId)
    {
        return $this->getUserSecureQuestionDao()->getUserSecureQuestionsByUserId($userId);
    }

    public function addUserSecureQuestionsWithUnHashedAnswers($userId, $fieldsWithQuestionTypesAndUnHashedAnswers)
    {
        $encoder               = $this->getPasswordEncoder();
        $userSecureQuestionDao = $this->getUserSecureQuestionDao();

        for ($questionNum = 1; $questionNum <= (count($fieldsWithQuestionTypesAndUnHashedAnswers) / 2); $questionNum++) {
            $fields = array('userId' => $userId);

            $fields['securityQuestionCode'] = $fieldsWithQuestionTypesAndUnHashedAnswers['securityQuestion'.$questionNum];
            $fields['securityAnswerSalt']   = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $fields['securityAnswer']       =
            $encoder->encodePassword($fieldsWithQuestionTypesAndUnHashedAnswers['securityAnswer'.$questionNum], $fields['securityAnswerSalt']);
            $fields['createdTime'] = time();

            $userSecureQuestionDao->addOneUserSecureQuestion($fields);
        }

        return true;
    }

    public function verifyInSaltOut($in, $salt, $out)
    {
        return $out == $this->getPasswordEncoder()->encodePassword($in, $salt);
    }

    public function verifyPassword($id, $password)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('参数不正确，校验密码失败。');
        }

        return $this->verifyInSaltOut($password, $user['salt'], $user['password']);
    }

    public function verifyPayPassword($id, $payPassword)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('参数不正确，校验密码失败。');
        }

        return $this->verifyInSaltOut($payPassword, $user['payPasswordSalt'], $user['payPassword']);
    }

    public function parseRegistration($registration)
    {
        $mode = $this->getRegisterMode();

        if ($mode == 'email_or_mobile') {
            if (isset($registration['emailOrMobile']) && !empty($registration['emailOrMobile'])) {
                if (SimpleValidator::email($registration['emailOrMobile'])) {
                    $registration['email'] = $registration['emailOrMobile'];
                    $registration['type']  = isset($registration['type']) ? $registration['type'] : 'web_email';
                } elseif (SimpleValidator::mobile($registration['emailOrMobile'])) {
                    $registration['mobile']         = $registration['emailOrMobile'];
                    $registration['verifiedMobile'] = $registration['emailOrMobile'];
                    $registration['type']           = isset($registration['type']) ? $registration['type'] : 'web_mobile';
                } else {
                    throw $this->createServiceException('emailOrMobile error!');
                }
            } else {
                throw $this->createServiceException('参数不正确，邮箱或手机不能为空。');
            }
        } elseif ($mode == 'mobile') {
            if (isset($registration['mobile']) && !empty($registration['mobile'])) {
                if (SimpleValidator::mobile($registration['mobile'])) {
                    $registration['verifiedMobile'] = $registration['mobile'];
                    $registration['type']           = isset($registration['type']) ? $registration['type'] : 'web_mobile';
                } else {
                    throw $this->createServiceException('mobile error!');
                }
            } else {
                throw $this->createServiceException('参数不正确，手机不能为空。');
            }
        } else {
            $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_email';
            return $registration;
        }

        return $registration;
    }

    public function isMobileRegisterMode()
    {
        $authSetting = $this->getSettingservice()->get('auth');
        return (isset($authSetting['register_mode']) && (($authSetting['register_mode'] == 'email_or_mobile') || ($authSetting['register_mode'] == 'mobile')));
    }

    /**
     * email, email_or_mobile, mobile, null
     */
    private function getRegisterMode()
    {
        $authSetting = $this->getSettingservice()->get('auth');

        if (isset($authSetting['register_mode'])) {
            return $authSetting['register_mode'];
        } else {
            return null;
        }
    }

    protected function getRandomChar()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    protected function validateNickname($nickname)
    {
        if (!SimpleValidator::nickname($nickname)) {
            throw $this->createServiceException('Invalid nickname: '.$nickname);
        }
    }

    public function register($registration, $type = 'default')
    {
        $this->validateNickname($registration['nickname']);

        if (!$this->isNicknameAvaliable($registration['nickname'])) {
            throw $this->createServiceException('昵称已存在');
        }

        if (!SimpleValidator::email($registration['email'])) {
            throw $this->createServiceException('email error!');
        }

        if (!$this->isEmailAvaliable($registration['email'])) {
            throw $this->createServiceException('Email已存在');
        }

        $user = array();

        if (isset($registration['verifiedMobile'])) {
            $user['verifiedMobile'] = $registration['verifiedMobile'];
        } else {
            $user['verifiedMobile'] = '';
        }

        $user['email']         = $registration['email'];
        $user['emailVerified'] = isset($registration['emailVerified']) ? $registration['emailVerified'] : 0;
        $user['nickname']      = $registration['nickname'];
        $user['roles']         = array('ROLE_USER');
        $user['type']          = isset($registration['type']) ? $registration['type'] : $type;
        $user['createdIp']     = empty($registration['createdIp']) ? '' : $registration['createdIp'];

        $user['createdTime'] = time();

        $thirdLoginInfo = $this->getSettingService()->get('login_bind', array());

        if (in_array($type, array('default', 'phpwind', 'discuz'))) {
            $user['salt']     = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup']    = 1;
        } elseif (in_array($type, array('qq', 'weibo', 'renren', 'weixinweb', 'weixinmob')) && isset($thirdLoginInfo["{$type}_set_fill_account"]) && $thirdLoginInfo["{$type}_set_fill_account"]) {
            $user['salt']     = '';
            $user['password'] = '';
            $user['setup']    = 1;
        } else {
            $user['salt']     = '';
            $user['password'] = '';
            $user['setup']    = 0;
        }
        if (isset($registration['orgId'])) {
            $user['orgId']   = $registration['orgId'];
            $user['orgCode'] = $registration['orgCode'];
        }
        $user = UserSerialize::unserialize(
            $this->getUserDao()->addUser(UserSerialize::serialize($user))
        );

        if (!empty($registration['invite_code'])) {
            $inviteUser = $this->getUserDao()->getUserByInviteCode($registration['invite_code']);
        }

        if (!empty($inviteUser)) {
            $this->getInviteRecordService()->createInviteRecord($inviteUser['id'], $user['id']);
            $invitedCoupon = $this->getCouponService()->generateInviteCoupon($user['id'], 'register');

            if (!empty($invitedCoupon)) {
                $card = $this->getCardService()->getCardByCardId($invitedCoupon['id']);
                $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($user['id'], array('invitedUserCardId' => $card['cardId']));
            }

            $this->dispatchEvent(
                'user.register',
                new ServiceEvent(array('userId' => $user['id'], 'inviteUserId' => $inviteUser['id']))
            );
        }

        if (isset($registration['mobile']) && $registration['mobile'] != "" && !SimpleValidator::mobile($registration['mobile'])) {
            throw $this->createServiceException('mobile error!');
        }

        if (isset($registration['idcard']) && $registration['idcard'] != "" && !SimpleValidator::idcard($registration['idcard'])) {
            throw $this->createServiceException('idcard error!');
        }

        if (isset($registration['truename']) && $registration['truename'] != "" && !SimpleValidator::truename($registration['truename'])) {
            throw $this->createServiceException('truename error!');
        }

        $profile             = array();
        $profile['id']       = $user['id'];
        $profile['mobile']   = empty($registration['mobile']) ? '' : $registration['mobile'];
        $profile['idcard']   = empty($registration['idcard']) ? '' : $registration['idcard'];
        $profile['truename'] = empty($registration['truename']) ? '' : $registration['truename'];
        $profile['company']  = empty($registration['company']) ? '' : $registration['company'];
        $profile['job']      = empty($registration['job']) ? '' : $registration['job'];
        $profile['weixin']   = empty($registration['weixin']) ? '' : $registration['weixin'];
        $profile['weibo']    = empty($registration['weibo']) ? '' : $registration['weibo'];
        $profile['qq']       = empty($registration['qq']) ? '' : $registration['qq'];
        $profile['site']     = empty($registration['site']) ? '' : $registration['site'];
        $profile['gender']   = empty($registration['gender']) ? 'secret' : $registration['gender'];

        for ($i = 1; $i <= 5; $i++) {
            $profile['intField'.$i]   = empty($registration['intField'.$i]) ? null : $registration['intField'.$i];
            $profile['dateField'.$i]  = empty($registration['dateField'.$i]) ? null : $registration['dateField'.$i];
            $profile['floatField'.$i] = empty($registration['floatField'.$i]) ? null : $registration['floatField'.$i];
        }

        for ($i = 1; $i <= 10; $i++) {
            $profile['varcharField'.$i] = empty($registration['varcharField'.$i]) ? "" : $registration['varcharField'.$i];
            $profile['textField'.$i]    = empty($registration['textField'.$i]) ? "" : $registration['textField'.$i];
        }

        $this->getProfileDao()->addProfile($profile);

        if ($type != 'default') {
            $this->bindUser($type, $registration['token']['userId'], $user['id'], $registration['token']);
        }

        $this->getDispatcher()->dispatch('user.service.registered', new ServiceEvent($user));

        return $user;
    }

    public function generateNickname($registration, $maxLoop = 100)
    {
        for ($i = 0; $i < $maxLoop; $i++) {
            $registration['nickname'] = 'user'.substr($this->getRandomChar(), 0, 6);

            if ($this->isNicknameAvaliable($registration['nickname'])) {
                break;
            }
        }

        return $registration['nickname'];
    }

    public function generateEmail($registration, $maxLoop = 100)
    {
        for ($i = 0; $i < $maxLoop; $i++) {
            $registration['email'] = 'user_'.substr($this->getRandomChar(), 0, 9).'@edusoho.net';

            if ($this->isEmailAvaliable($registration['email'])) {
                break;
            }
        }

        return $registration['email'];
    }

    public function importUpdateEmail($users)
    {
        $this->getUserDao()->getConnection()->beginTransaction();
        try {
            for ($i = 0; $i < count($users); $i++) {
                $member = $this->getUserDao()->findUserByEmail($users[$i]["email"]);
                $member = UserSerialize::unserialize($member);
                $this->changePassword($member["id"], $users[$i]["password"]);
                $this->updateUserProfile($member["id"], $users[$i]);
            }

            $this->getUserDao()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function setupAccount($userId)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，设置帐号失败！');
        }

        if ($user['setup']) {
            throw $this->createServiceException('该帐号，已经设置过帐号信息，不能再设置！');
        }

        $user = $this->getUserDao()->updateUser($userId, array('setup' => 1));
        return $this->getUser($userId);
    }

    public function updateUserProfile($id, $fields)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，更新用户失败。');
        }

        $fields = ArrayToolkit::filter($fields, array(
            'truename'       => '',
            'gender'         => 'secret',
            'iam'            => '',
            'idcard'         => '',
            'birthday'       => null,
            'city'           => '',
            'mobile'         => '',
            'qq'             => '',
            'school'         => '',
            'class'          => '',
            'company'        => '',
            'job'            => '',
            'signature'      => '',
            'title'          => '',
            'about'          => '',
            'weibo'          => '',
            'weixin'         => '',
            'site'           => '',
            'isWeiboPublic'  => '',
            'isWeixinPublic' => '',
            'isQQPublic'     => '',
            'intField1'      => null,
            'intField2'      => null,
            'intField3'      => null,
            'intField4'      => null,
            'intField5'      => null,
            'dateField1'     => null,
            'dateField2'     => null,
            'dateField3'     => null,
            'dateField4'     => null,
            'dateField5'     => null,
            'floatField1'    => null,
            'floatField2'    => null,
            'floatField3'    => null,
            'floatField4'    => null,
            'floatField5'    => null,
            'textField1'     => "",
            'textField2'     => "",
            'textField3'     => "",
            'textField4'     => "",
            'textField5'     => "",
            'textField6'     => "",
            'textField7'     => "",
            'textField8'     => "",
            'textField9'     => "",
            'textField10'    => "",
            'varcharField1'  => "",
            'varcharField2'  => "",
            'varcharField3'  => "",
            'varcharField4'  => "",
            'varcharField5'  => "",
            'varcharField6'  => "",
            'varcharField7'  => "",
            'varcharField8'  => "",
            'varcharField9'  => "",
            'varcharField10' => ""
        ));

        if (empty($fields)) {
            return $this->getProfileDao()->getProfile($id);
        }

        if (isset($fields['title'])) {
            $this->getUserDao()->updateUser($id, array('title' => $fields['title']));
            $this->dispatchEvent('user.update', new ServiceEvent(array('user' => $user, 'fields' => $fields)));
        }

        unset($fields['title']);

        if (!empty($fields['gender']) && !in_array($fields['gender'], array('male', 'female', 'secret'))) {
            throw $this->createServiceException('性别不正确，更新用户失败。');
        }

        if (!empty($fields['birthday']) && !SimpleValidator::date($fields['birthday'])) {
            throw $this->createServiceException('生日不正确，更新用户失败。');
        }

        if (!empty($fields['mobile']) && !SimpleValidator::mobile($fields['mobile'])) {
            throw $this->createServiceException('手机不正确，更新用户失败。');
        }

        if (!empty($fields['qq']) && !SimpleValidator::qq($fields['qq'])) {
            throw $this->createServiceException('QQ不正确，更新用户失败。');
        }

        if (!empty($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about']);
        }

        if (empty($fields['isWeiboPublic'])) {
            $fields['isWeiboPublic'] = 0;
        } else {
            $fields['isWeiboPublic'] = 1;
        }

        if (empty($fields['isWeixinPublic'])) {
            $fields['isWeixinPublic'] = 0;
        } else {
            $fields['isWeixinPublic'] = 1;
        }

        if (empty($fields['isQQPublic'])) {
            $fields['isQQPublic'] = 0;
        } else {
            $fields['isQQPublic'] = 1;
        }

        $userProfile = $this->getProfileDao()->updateProfile($id, $fields);

        $this->dispatchEvent('profile.update', new ServiceEvent(array('user' => $user, 'fields' => $fields)));

        return $userProfile;
    }

    public function changeUserRoles($id, array $roles)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，设置用户角色失败。');
        }

        if (empty($roles)) {
            throw $this->createServiceException('用户角色不能为空');
        }

        if (!in_array('ROLE_USER', $roles)) {
            throw $this->createServiceException('用户角色必须包含ROLE_USER');
        }

        $allowedRoles = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');

        $notAllowedRoles = array_diff($roles, $allowedRoles);

        if (!empty($notAllowedRoles)) {
            throw $this->createServiceException('用户角色不正确，设置用户角色失败。');
        }

        $this->getUserDao()->updateUser($id, UserSerialize::serialize(array('roles' => $roles)));

        $this->getLogService()->info('user', 'change_role', "设置用户{$user['nickname']}(#{$user['id']})的角色为：".implode(',', $roles));
    }

    public function makeToken($type, $userId = null, $expiredTime = null, $data = null)
    {
        $token                = array();
        $token['type']        = $type;
        $token['userId']      = $userId ? (int) $userId : 0;
        $token['token']       = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $token['data']        = serialize($data);
        $token['expiredTime'] = $expiredTime ? (int) $expiredTime : 0;
        $token['createdTime'] = time();
        $token                = $this->getUserTokenDao()->addToken($token);
        return $token['token'];
    }

    public function getToken($type, $token)
    {
        $token = $this->getUserTokenDao()->getTokenByToken($token);

        if (empty($token) || $token['type'] != $type) {
            return null;
        }

        if ($token['expiredTime'] > 0 && $token['expiredTime'] < time()) {
            return null;
        }

        $token['data'] = unserialize($token['data']);
        return $token;
    }

    public function searchTokenCount($conditions)
    {
        return $this->getUserTokenDao()->searchTokenCount($conditions);
    }

    public function deleteToken($type, $token)
    {
        $token = $this->getUserTokenDao()->getTokenByToken($token);

        if (empty($token) || $token['type'] != $type) {
            return false;
        }

        $this->getUserTokenDao()->deleteToken($token['id']);
        return true;
    }

    public function findBindsByUserId($userId)
    {
        $user = $this->getUserDao()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException('获取用户绑定信息失败，当前用户不存在');
        }

        return $this->getUserBindDao()->findBindsByToId($userId);
    }

    protected function typeInOAuthClient($type)
    {
        $types = array_keys(OAuthClientFactory::clients());
        $types = array_merge($types, array('discuz', 'phpwind'));

        return in_array($type, $types);
    }

    public function unBindUserByTypeAndToId($type, $toId)
    {
        $user = $this->getUserDao()->getUser($toId);

        if (empty($user)) {
            throw $this->createServiceException('解除第三方绑定失败，该用户不存在');
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createServiceException("{$type}类型不正确，解除第三方绑定失败。");
        }

        $bind = $this->getUserBindByTypeAndUserId($type, $toId);

        if ($bind) {
            $bind        = $this->getUserBindDao()->deleteBind($bind['id']);
            $currentUser = $this->getCurrentUser();
            $this->getLogService()->info('user', 'unbind', "用户名{$user['nickname']}解绑成功，操作用户为{$currentUser['nickname']}");
        }

        return $bind;
    }

    public function getUserBindByTypeAndFromId($type, $fromId)
    {
        if ($type == 'weixinweb' || $type == 'weixinmob') {
            $type = 'weixin';
        }

        return $this->getUserBindDao()->getBindByTypeAndFromId($type, $fromId);
    }

    public function getUserBindByToken($token)
    {
        return $this->getUserBindDao()->getBindByToken($token);
    }

    public function getUserBindByTypeAndUserId($type, $toId)
    {
        $user = $this->getUserDao()->getUser($toId);

        if (empty($user)) {
            throw $this->createServiceException('获取用户绑定信息失败，该用户不存在');
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createServiceException("{$type}类型不正确，获取第三方登录信息失败。");
        }

        if ($type == 'weixinweb' || $type == 'weixinmob') {
            $type = 'weixin';
        }

        return $this->getUserBindDao()->getBindByToIdAndType($type, $toId);
    }

    public function bindUser($type, $fromId, $toId, $token)
    {
        $user = $this->getUserDao()->getUser($toId);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，第三方绑定失败');
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createServiceException("{$type}类型不正确，第三方绑定失败。");
        }

        if ($type == 'weixinweb' || $type == 'weixinmob') {
            $type = 'weixin';
        }

        $this->getUserBindDao()->addBind(array(
            'type'        => $type,
            'fromId'      => $fromId,
            'toId'        => $toId,
            'token'       => empty($token['token']) ? '' : $token['token'],
            'createdTime' => time(),
            'expiredTime' => empty($token['expiredTime']) ? 0 : $token['expiredTime']
        ));
    }

    public function markLoginInfo()
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            return;
        }

        $this->getUserDao()->updateUser($user['id'], array(
            'loginIp'   => $user['currentIp'],
            'loginTime' => time()
        ));

        $this->getLogService()->info('user', 'login_success', '登录成功');
    }

    public function markLoginFailed($userId, $ip)
    {
        $user = $userId ? $this->getUser($userId) : null;

        $setting = $this->getSettingService()->get('login_bind', array());

        $default = array(
            'temporary_lock_enabled'       => 0,
            'temporary_lock_allowed_times' => 5,
            'temporary_lock_minutes'       => 20
        );
        $setting = array_merge($default, $setting);

        $fields = array();

        if ($user && $setting['temporary_lock_enabled']) {
            if (time() > $user['lastPasswordFailTime'] + $setting['temporary_lock_minutes'] * 60) {
                $fields['consecutivePasswordErrorTimes'] = 1;
            } else {
                $fields['consecutivePasswordErrorTimes'] = $user['consecutivePasswordErrorTimes'] + 1;
            }

            if ($fields['consecutivePasswordErrorTimes'] >= $setting['temporary_lock_allowed_times']) {
                $fields['lockDeadline'] = time() + $setting['temporary_lock_minutes'] * 60;
            }

            $fields['lastPasswordFailTime'] = time();

            $user = $this->getUserDao()->updateUser($user['id'], $fields);
        }

        if ($user) {
            $log = "用户({$user['nickname']})，".($user['consecutivePasswordErrorTimes'] ? "连续第{$user['consecutivePasswordErrorTimes']}次登录失败" : '登录失败');
        } else {
            $log = "用户(IP: $ip)，".($user['consecutivePasswordErrorTimes'] ? "连续第{$user['consecutivePasswordErrorTimes']}次登录失败" : '登录失败');
        }

        $this->getLogService()->info('user', 'login_fail', $log);

        $ipFailedCount = $this->getIpBlacklistService()->increaseIpFailedCount($ip);

        return array(
            'failedCount'     => $user['consecutivePasswordErrorTimes'],
            'leftFailedCount' => $setting['temporary_lock_allowed_times'] - $user['consecutivePasswordErrorTimes'],
            'ipFaildCount'    => $ipFailedCount
        );
    }

    public function markLoginSuccess($userId, $ip)
    {
        $fields = array(
            'lockDeadline'                  => 0,
            'consecutivePasswordErrorTimes' => 0,
            'lastPasswordFailTime'          => 0
        );

        $this->getUserDao()->updateUser($userId, $fields);
        $this->getIpBlacklistService()->clearFailedIp($ip);
    }

    public function checkLoginForbidden($userId, $ip)
    {
        $user = $userId ? $this->getUser($userId) : null;

        $setting = $this->getSettingService()->get('login_bind', array());

        $default = array(
            'temporary_lock_enabled'          => 0,
            'temporary_lock_allowed_times'    => 5,
            'ip_temporary_lock_allowed_times' => 20,
            'temporary_lock_minutes'          => 20
        );
        $setting = array_merge($default, $setting);

        if (empty($setting['temporary_lock_enabled'])) {
            return array('status' => 'ok');
        }

        $ipFailedCount = $this->getIpBlacklistService()->getIpFailedCount($ip);

        if ($ipFailedCount >= $setting['ip_temporary_lock_allowed_times']) {
            return array('status' => 'error', 'code' => 'max_ip_failed_limit');
        }

        if ($user && $setting['temporary_lock_enabled'] && ($user['lockDeadline'] > time())) {
            return array('status' => 'error', 'code' => 'max_failed_limit');
        }

        if ($user && $setting['temporary_lock_enabled'] && ($user['consecutivePasswordErrorTimes'] >= $setting['temporary_lock_allowed_times']) && ($user['lockDeadline'] > time())) {
            return array('status' => 'error', 'code' => 'max_failed_limit');
        }

        return array('status' => 'ok');
    }

    public function lockUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，封禁失败！');
        }

        $this->getUserDao()->updateUser($user['id'], array('locked' => 1));
        $this->dispatchEvent("user.lock", new ServiceEvent($user));

        $this->getLogService()->info('user', 'lock', "封禁用户{$user['nickname']}(#{$user['id']})");

        return true;
    }

    public function unlockUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，解禁失败！');
        }

        $this->getUserDao()->updateUser($user['id'], array('locked' => 0));

        $this->dispatchEvent("user.unlock", new ServiceEvent($user));

        $this->getLogService()->info('user', 'unlock', "解禁用户{$user['nickname']}(#{$user['id']})");

        return true;
    }

    public function promoteUser($id, $number)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，推荐失败！');
        }

        $user = $this->getUserDao()->updateUser($user['id'], array('promoted' => 1, 'promotedSeq' => $number, 'promotedTime' => time()));
        $this->getLogService()->info('user', 'recommend', "推荐用户{$user['nickname']}(#{$user['id']})");
        return $user;
    }

    public function cancelPromoteUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，取消推荐失败！');
        }

        $user = $this->getUserDao()->updateUser($user['id'], array('promoted' => 0, 'promotedSeq' => 0, 'promotedTime' => 0));

        $this->getLogService()->info('user', 'cancel_recommend', "取消推荐用户{$user['nickname']}(#{$user['id']})");
        return $user;
    }

    public function findLatestPromotedTeacher($start, $limit)
    {
        return $this->searchUsers(array('roles' => 'ROLE_TEACHER', 'promoted' => 1), array('promotedTime', 'DESC'), $start, $limit);
    }

    public function waveUserCounter($userId, $name, $number)
    {
        if (!ctype_digit((string) $number)) {
            throw $this->createServiceException('计数器的数量，必须为数字');
        }

        $this->getUserDao()->waveCounterById($userId, $name, $number);
    }

    public function clearUserCounter($userId, $name)
    {
        $this->getUserDao()->clearCounterById($userId, $name);
    }

    public function filterFollowingIds($userId, array $followingIds)
    {
        if (empty($followingIds)) {
            return array();
        }

        $friends = $this->getFriendDao()->getFriendsByFromIdAndToIds($userId, $followingIds);
        return ArrayToolkit::column($friends, 'toId');
    }

    public function findUserFollowing($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->findFriendsByFromId($userId, $start, $limit);
        $ids     = ArrayToolkit::column($friends, 'toId');
        return $this->findUsersByIds($ids);
    }

    public function findAllUserFollowing($userId)
    {
        $friends = $this->getFriendDao()->findAllUserFollowingByFromId($userId);
        $ids     = ArrayToolkit::column($friends, 'toId');
        return $this->findUsersByIds($ids);
    }

    public function findUserFollowingCount($userId)
    {
        return $this->getFriendDao()->findFriendCountByFromId($userId);
    }

    public function findUserFollowers($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->findFriendsByToId($userId, $start, $limit);
        $ids     = ArrayToolkit::column($friends, 'fromId');
        return $this->findUsersByIds($ids);
    }

    public function findAllUserFollower($userId)
    {
        $friends = $this->getFriendDao()->findAllUserFollowerByToId($userId);
        $ids     = ArrayToolkit::column($friends, 'fromId');
        return $this->findUsersByIds($ids);
    }

    public function findUserFollowerCount($userId)
    {
        return $this->getFriendDao()->findFriendCountByToId($userId);
    }

    public function findFriends($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->findFriendsByUserId($userId, $start, $limit);
        $ids     = ArrayToolkit::column($friends, 'toId');
        return $this->findUsersByIds($ids);
    }

    public function findFriendCount($userId)
    {
        return $this->getFriendDao()->findFriendCountByUserId($userId);
    }

    public function follow($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser   = $this->getUser($toId);

        if (empty($fromUser) || empty($toUser)) {
            throw $this->createServiceException('用户不存在，关注失败！');
        }

        if ($fromId == $toId) {
            throw $this->createServiceException('不能关注自己！');
        }

        $blacklist = $this->getBlacklistService()->getBlacklistByUserIdAndBlackId($toId, $fromId);

        if (!empty($blacklist)) {
            throw $this->createServiceException('关注失败！');
        }

        $friend = $this->getFriendDao()->getFriendByFromIdAndToId($fromId, $toId);

        if (!empty($friend)) {
            throw $this->createServiceException('不允许重复关注!');
        }

        $isFollowed = $this->isFollowed($toId, $fromId);
        $pair       = $isFollowed ? 1 : 0;
        $friend     = $this->getFriendDao()->addFriend(array(
            'fromId'      => $fromId,
            'toId'        => $toId,
            'createdTime' => time(),
            'pair'        => $pair
        ));
        $this->getFriendDao()->updateFriendByFromIdAndToId($toId, $fromId, array('pair' => $pair));
        $this->getDispatcher()->dispatch('user.service.follow', new ServiceEvent($friend));
        return $friend;
    }

    public function unFollow($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser   = $this->getUser($toId);

        if (empty($fromUser) || empty($toUser)) {
            throw $this->createServiceException('用户不存在，取消关注失败！');
        }

        $friend = $this->getFriendDao()->getFriendByFromIdAndToId($fromId, $toId);

        if (empty($friend)) {
            throw $this->createServiceException('不存在此关注关系，取消关注失败！');
        }

        $result     = $this->getFriendDao()->deleteFriend($friend['id']);
        $isFollowed = $this->isFollowed($toId, $fromId);

        if ($isFollowed) {
            $this->getFriendDao()->updateFriendByFromIdAndToId($toId, $fromId, array('pair' => 0));
        }

        $this->getDispatcher()->dispatch('user.service.unfollow', new ServiceEvent($friend));
        return $result;
    }

    public function hasAdminRoles($userId)
    {
        $user = $this->getUser($userId);

        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
            return true;
        }

        return false;
    }

    public function isFollowed($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser   = $this->getUser($toId);

        if (empty($fromUser)) {
            throw $this->createServiceException('用户不存在，检测关注状态失败！');
        }

        if (empty($toUser)) {
            throw $this->createServiceException('被关注者不存在，检测关注状态失败！');
        }

        $friend = $this->getFriendDao()->getFriendByFromIdAndToId($fromId, $toId);

        if (empty($friend)) {
            return false;
        } else {
            return true;
        }
    }

    public function getLastestApprovalByUserIdAndStatus($userId, $status)
    {
        return $this->getUserApprovalDao()->getLastestApprovalByUserIdAndStatus($userId, $status);
    }

    public function findUserApprovalsByUserIds($userIds)
    {
        return $this->getUserApprovalDao()->findApprovalsByUserIds($userIds);
    }

    public function applyUserApproval($userId, $approval, $faceImg, $backImg, $directory)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户#{$userId}不存在！");
        }

        $faceImgPath = 'userFaceImg'.$userId.time().'.'.$faceImg->getClientOriginalExtension();
        $backImgPath = 'userbackImg'.$userId.time().'.'.$backImg->getClientOriginalExtension();
        $faceImg     = $faceImg->move($directory, $faceImgPath);
        $backImg     = $backImg->move($directory, $backImgPath);

        $approval['userId']      = $user['id'];
        $approval['faceImg']     = $faceImg->getPathname();
        $approval['backImg']     = $backImg->getPathname();
        $approval['status']      = 'approving';
        $approval['createdTime'] = time();

        $this->getUserDao()->updateUser($userId, array(
            'approvalStatus' => 'approving',
            'approvalTime'   => time()
        ));

        $this->getUserApprovalDao()->addApproval($approval);
        return true;
    }

    public function passApproval($userId, $note = null)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户#{$userId}不存在！");
        }

        $this->getUserDao()->updateUser($user['id'], array(
            'approvalStatus' => 'approved',
            'approvalTime'   => time()
        ));

        $lastestApproval = $this->getUserApprovalDao()->getLastestApprovalByUserIdAndStatus($user['id'], 'approving');

        $this->getProfileDao()->updateProfile($userId, array(
            'truename' => $lastestApproval['truename'],
            'idcard'   => $lastestApproval['idcard'])
        );

        $currentUser = $this->getCurrentUser();
        $this->getUserApprovalDao()->updateApproval($lastestApproval['id'],
            array(
                'userId'     => $user['id'],
                'note'       => $note,
                //'status' => 'approved',
                'operatorId' => $currentUser['id'])
        );

        $this->getLogService()->info('user', 'approved', "用户{$user['nickname']}实名认证成功，操作人:{$currentUser['nickname']} !");

        $message = array(
            'note' => $note ? $note : '',
            'type' => 'through');
        $this->getNotificationService()->notify($user['id'], 'truename-authenticate', $message);
        return true;
    }

    public function rejectApproval($userId, $note = null)
    {
        $user = $this->getUserDao()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户#{$userId}不存在！");
        }

        $this->getUserDao()->updateUser($user['id'], array(
            'approvalStatus' => 'approve_fail',
            'approvalTime'   => time()
        ));

        $lastestApproval = $this->getUserApprovalDao()->getLastestApprovalByUserIdAndStatus($user['id'], 'approved');
        $currentUser     = $this->getCurrentUser();
        $this->getUserApprovalDao()->updateApproval($lastestApproval['id'],
            array(
                'userId'     => $user['id'],
                'note'       => $note,
                'status'     => 'approve_fail',
                'operatorId' => $currentUser['id'])
        );

        $this->getLogService()->info('user', 'approval_fail', "用户{$user['nickname']}实名认证失败，操作人:{$currentUser['nickname']} !");
        $message = array(
            'note' => $note ? $note : '',
            'type' => 'reject');
        $this->getNotificationService()->notify($user['id'], 'truename-authenticate', $message);
        return true;
    }

    public function dropFieldData($fieldName)
    {
        $this->getProfileDao()->dropFieldData($fieldName);
    }

    protected function getUserApprovalDao()
    {
        return $this->createDao("User.UserApprovalDao");
    }

    public function rememberLoginSessionId($id, $sessionId)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，检测关注状态失败！');
        }

        return $this->getUserDao()->updateUser($id, array(
            'loginSessionId' => $sessionId
        ));
    }

    public function analysisRegisterDataByTime($startTime, $endTime)
    {
        return $this->getUserDao()->analysisRegisterDataByTime($startTime, $endTime);
    }

    public function analysisUserSumByTime($endTime)
    {
        $perDayUserAddCount = $this->getUserDao()->analysisUserSumByTime($endTime);
        $dayUserTotals      = array();

        foreach ($perDayUserAddCount as $key => $value) {
            $dayUserTotals[$key]          = array();
            $dayUserTotals[$key]["date"]  = $value["date"];
            $dayUserTotals[$key]["count"] = 0;

            for ($i = $key; $i < count($perDayUserAddCount); $i++) {
                $dayUserTotals[$key]["count"] += $perDayUserAddCount[$i]["count"];
            }
        }

        return $dayUserTotals;
    }

    public function parseAts($text)
    {
        preg_match_all('/@([\x{4e00}-\x{9fa5}\w]{2,16})/u', $text, $matches);

        $users = $this->getUserDao()->findUsersByNicknames(array_unique($matches[1]));

        if (empty($users)) {
            return array();
        }

        $ats = array();

        foreach ($users as $user) {
            $ats[$user['nickname']] = $user['id'];
        }

        return $ats;
    }

    public function getUserByInviteCode($inviteCode)
    {
        return $this->getUserDao()->getUserByInviteCode($inviteCode);
    }

    public function findUserIdsByInviteCode($inviteCode)
    {
        $inviteUser = $this->getUserDao()->getUserByInviteCode($inviteCode);
        $record     = $this->getInviteRecordService()->findRecordsByInviteUserId($inviteUser['id']);
        $userIds    = ArrayToolkit::column($record, 'invitedUserId');
        return $userIds;
    }

    public function createInviteCode($userId)
    {
        $inviteCode = StringToolkit::createRandomString(5);
        $inviteCode = strtoupper($inviteCode);
        $code       = array(
            'inviteCode' => $inviteCode
        );

        return $this->getUserDao()->updateUser($userId, $code);
    }

    public function findUnlockedUserMobilesByUserIds($userIds)
    {
        $users = $this->findUsersByIds($userIds);

        foreach ($users as $key => $value) {
            if ($value['locked']) {
                unset($users[$key]);
            }
        }

        if (empty($users)) {
            return array();
        }

        $verifiedMobiles = ArrayToolkit::column($users, 'verifiedMobile');

        $userIds        = ArrayToolkit::column($users, 'id');
        $userProfiles   = $this->findUserProfilesByIds($userIds);
        $profileMobiles = ArrayToolkit::column($userProfiles, 'mobile');

        $mobiles = array_merge($verifiedMobiles, $profileMobiles);
        $mobiles = array_filter($mobiles);
        return array_unique($mobiles);
    }

    public function getUserPayAgreement($id)
    {
        return $this->getUserPayAgreementDao()->getUserPayAgreement($id);
    }

    public function getUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth)
    {
        return $this->getUserPayAgreementDao()->getUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth);
    }

    public function getUserPayAgreementByUserId($userId)
    {
        return $this->getUserPayAgreementDao()->getUserPayAgreementByUserId($userId);
    }

    public function createUserPayAgreement($field)
    {
        $field = ArrayToolkit::parts($field, array('userId', 'type', 'bankName', 'bankNumber', 'userAuth', 'bankAuth', 'bankId', 'createdTime'));
        return $this->getUserPayAgreementDao()->addUserPayAgreement($field);
    }

    public function updateUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth, $fields)
    {
        return $this->getUserPayAgreementDao()->updateUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth, $fields);
    }

    public function findUserPayAgreementsByUserId($userId)
    {
        return $this->getUserPayAgreementDao()->findUserPayAgreementsByUserId($userId);
    }

    public function deleteUserPayAgreements($id)
    {
        return $this->getUserPayAgreementDao()->deleteUserPayAgreements($id);
    }

    protected function getFriendDao()
    {
        return $this->createDao("User.FriendDao");
    }

    protected function getCouponDao()
    {
        return $this->createDao('Coupon.CouponDao');
    }

    protected function getUserDao()
    {
        return $this->createDao('User.UserDao');
    }

    protected function getProfileDao()
    {
        return $this->createDao('User.UserProfileDao');
    }

    protected function getUserSecureQuestionDao()
    {
        return $this->createDao('User.UserSecureQuestionDao');
    }

    protected function getUserBindDao()
    {
        return $this->createDao('User.UserBindDao');
    }

    protected function getUserTokenDao()
    {
        return $this->createDao('User.TokenDao');
    }

    protected function getUserFortuneLogDao()
    {
        return $this->createDao('User.UserFortuneLogDao');
    }

    protected function getCardService()
    {
        return $this->createService('Card.CardService');
    }

    protected function getCouponService()
    {
        return $this->createService('Coupon.CouponService');
    }

    protected function getUserPayAgreementDao()
    {
        return $this->createDao('User.UserPayAgreementDao');
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getIpBlacklistService()
    {
        return $this->createService('System.IpBlacklistService');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    protected function getBlacklistService()
    {
        return $this->createService('User.BlacklistService');
    }

    protected function getInviteRecordService()
    {
        return $this->createService('User.InviteRecordService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:Org.OrgService');
    }
}

class UserSerialize
{
    public static function
    serialize(array $user) {
        $user['roles'] = empty($user['roles']) ? '' : '|'.implode('|', $user['roles']).'|';
        return $user;
    }

    public static function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }

        $user['roles'] = empty($user['roles']) ? array() : explode('|', trim($user['roles'], '|'));

        $user = UserSerialize::_userRolesSort($user);

        return $user;
    }

    public static function unserializes(array $users)
    {
        return array_map(function ($user) {
            return UserSerialize::unserialize($user);
        }, $users);
    }

    private static function _userRolesSort($user)
    {
        if (!empty($user['roles'][1]) && $user['roles'][1] == 'ROLE_USER') {
            $temp             = $user['roles'][1];
            $user['roles'][1] = $user['roles'][0];
            $user['roles'][0] = $temp;
        }

        //交换学员角色跟roles数组第0个的位置;

        return $user;
    }
}
