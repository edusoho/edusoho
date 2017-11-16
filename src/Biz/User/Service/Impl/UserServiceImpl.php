<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Dao\UserDao;
use Biz\User\Dao\TokenDao;
use Biz\User\Dao\FriendDao;
use Biz\Coupon\Dao\CouponDao;
use Biz\User\Dao\UserBindDao;
use Biz\Org\Service\OrgService;
use Biz\User\Dao\UserProfileDao;
use AppBundle\Common\FileToolkit;
use Biz\Card\Service\CardService;
use Biz\Role\Service\RoleService;
use Biz\User\Dao\UserApprovalDao;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\LogService;
use AppBundle\Common\StringToolkit;
use Biz\User\Dao\UserFortuneLogDao;
use Biz\Content\Service\FileService;
use AppBundle\Common\SimpleValidator;
use Biz\Coupon\Service\CouponService;
use Biz\User\Dao\UserPayAgreementDao;
use Biz\System\Service\SettingService;
use Biz\User\Service\BlacklistService;
use Biz\User\Dao\UserSecureQuestionDao;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Biz\User\Service\InviteRecordService;
use Biz\User\Service\NotificationService;
use Biz\System\Service\IpBlacklistService;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserServiceImpl extends BaseService implements UserService
{
    public function getUser($id, $lock = false)
    {
        $user = $this->getUserDao()->get($id, array('lock' => $lock));

        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function countUsers(array $conditions)
    {
        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = strtoupper($conditions['nickname']);
        }

        return $this->getUserDao()->count($conditions);
    }

    public function searchUsers(array $conditions, array $orderBy, $start, $limit)
    {
        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = strtoupper($conditions['nickname']);
        }

        $users = $this->getUserDao()->search($conditions, $orderBy, $start, $limit);

        return UserSerialize::unserializes($users);
    }

    public function changeRawPassword($id, $rawPassword)
    {
        if (empty($rawPassword)) {
            throw $this->createInvalidArgumentException('参数不正确，更改密码失败');
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("user #{$id} not found");
        }

        $this->getUserDao()->update($id, $rawPassword);

        $this->markLoginSuccess($user['id'], $this->getCurrentUser()->currentIp);

        $this->getLogService()->info('user', 'password-changed', sprintf('用户%s(ID:%u)重置密码成功', $user['email'], $user['id']));

        return true;
    }

    public function searchUserProfileCount(array $conditions)
    {
        return $this->getProfileDao()->count($conditions);
    }

    public function searchApprovalsCount(array $conditions)
    {
        $conditions = $this->_prepareApprovalConditions($conditions);

        return $this->getUserApprovalDao()->count($conditions);
    }

    public function searchTokenCount($conditions)
    {
        return $this->getUserTokenDao()->count($conditions);
    }

    public function findUserFollowing($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->searchByFromId($userId, $start, $limit);
        $ids = ArrayToolkit::column($friends, 'toId');

        return $this->findUsersByIds($ids);
    }

    public function findAllUserFollowing($userId)
    {
        $friends = $this->getFriendDao()->findFollowingsByFromId($userId);
        $ids = ArrayToolkit::column($friends, 'toId');

        return $this->findUsersByIds($ids);
    }

    public function findUserFollowingCount($userId)
    {
        return $this->getFriendDao()->count(array('fromId' => $userId));
    }

    public function findUserFollowers($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->searchByToId($userId, $start, $limit);
        $ids = ArrayToolkit::column($friends, 'fromId');

        return $this->findUsersByIds($ids);
    }

    public function findUserFollowerCount($userId)
    {
        return $this->getFriendDao()->count(array('toId' => $userId));
    }

    public function findAllUserFollower($userId)
    {
        $friends = $this->getFriendDao()->findFollowersByToId($userId);
        $ids = ArrayToolkit::column($friends, 'fromId');

        return $this->findUsersByIds($ids);
    }

    public function findFriendCount($userId)
    {
        return $this->getFriendDao()->count(array('fromId' => $userId, 'pair' => 1));
    }

    public function getSimpleUser($id)
    {
        $user = $this->getUser($id);

        $simple = array();

        $simple['id'] = $user['id'];
        $simple['nickname'] = $user['nickname'];
        $simple['title'] = $user['title'];
        $simple['avatar'] = $this->getFileService()->parseFileUri($user['smallAvatar']);

        return $simple;
    }

    public function countUsersByLessThanCreatedTime($endTime)
    {
        return $this->getUserDao()->countByLessThanCreatedTime($endTime);
    }

    public function getUserProfile($id)
    {
        return $this->getProfileDao()->get($id);
    }

    public function getUserByNickname($nickname)
    {
        $user = $this->getUserDao()->getByNickname($nickname);

        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function getUserByLoginField($keyword)
    {
        if (SimpleValidator::email($keyword)) {
            $user = $this->getUserDao()->getByEmail($keyword);
        } elseif (SimpleValidator::mobile($keyword)) {
            $user = $this->getUserDao()->getByVerifiedMobile($keyword);
        } else {
            $user = $this->getUserDao()->getByNickname($keyword);
        }

        if (isset($user['type']) && $user['type'] == 'system') {
            return null;
        }

        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function getUserByVerifiedMobile($mobile)
    {
        $user = $this->getUserDao()->getByVerifiedMobile($mobile);

        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function countUsersByMobileNotEmpty()
    {
        return $this->getUserDao()->countByMobileNotEmpty();
    }

    public function countUserHasMobile($needVerified = false)
    {
        if ($needVerified) {
            $count = $this->countUsers(array(
                'locked' => 0,
                'hasVerifiedMobile' => true,
            ));
        } else {
            $count = $this->countUsersByMobileNotEmpty();
        }

        return $count;
    }

    public function findUsersHasMobile($start, $limit, $needVerified = false)
    {
        $conditions = array(
            'locked' => 0,
        );
        $orderBy = array('createdTime' => 'ASC');
        if ($needVerified) {
            $conditions['hasVerifiedMobile'] = true;
            $users = $this->searchUsers($conditions, $orderBy, $start, $limit);
        } else {
            $profiles = $this->getProfileDao()->findDistinctMobileProfiles($start, $limit);
            $conditions['userIds'] = ArrayToolkit::column($profiles, 'id');
            $users = $this->searchUsers($conditions, $orderBy, 0, PHP_INT_MAX);
            $profiles = ArrayToolkit::index($profiles, 'id');
            $users = ArrayToolkit::index($users, 'id');
            $users = array_intersect_key($users, $profiles);
        }

        return $users;
    }

    public function getUserByEmail($email)
    {
        if (empty($email)) {
            return null;
        }

        $user = $this->getUserDao()->getByEmail($email);

        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function findUsersByIds(array $ids)
    {
        $users = UserSerialize::unserializes(
            $this->getUserDao()->findByIds($ids)
        );

        return ArrayToolkit::index($users, 'id');
    }

    public function findUserProfilesByIds(array $ids)
    {
        $userProfiles = $this->getProfileDao()->findByIds($ids);

        return ArrayToolkit::index($userProfiles, 'id');
    }

    public function searchUserProfiles(array $conditions, array $orderBy, $start, $limit)
    {
        $profiles = $this->getProfileDao()->search($conditions, $orderBy, $start, $limit);

        return $profiles;
    }

    public function searchApprovals(array $conditions, array $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareApprovalConditions($conditions);
        $approvals = $this->getUserApprovalDao()->search($conditions, $orderBy, $start, $limit);

        return $approvals;
    }

    public function setEmailVerified($userId)
    {
        $this->getUserDao()->update($userId, array('emailVerified' => 1));
        $user = $this->getUser($userId);
        $this->dispatchEvent('email.verify', new Event($user));
    }

    public function changeNickname($userId, $nickname)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        if (!SimpleValidator::nickname($nickname)) {
            throw $this->createInvalidArgumentException('nickname Invalid');
        }

        $existUser = $this->getUserDao()->getByNickname($nickname);

        if ($existUser && $existUser['id'] != $userId) {
            throw $this->createAccessDeniedException('Nickname Occupied');
        }

        $updatedUser = $this->getUserDao()->update($userId, array('nickname' => $nickname));
        $this->dispatchEvent('user.change_nickname', new Event($updatedUser));
        $this->getLogService()->info('user', 'nickname_change', "修改用户名{$user['nickname']}为{$nickname}成功");
    }

    public function changeUserOrg($userId, $orgCode)
    {
        $user = $this->getUser($userId);
        if (empty($user) || ($user['orgCode'] == $orgCode)) {
            return array();
        }

        if (empty($orgCode)) {
            $fields = array('orgCode' => '1.', 'orgId' => 1);
        } else {
            $org = $this->getOrgService()->getOrgByOrgCode($orgCode);
            if (empty($org)) {
                throw $this->createNotFoundException("Org#{$orgCode} Not Found");
            }
            $fields = array('orgCode' => $org['orgCode'], 'orgId' => $org['id']);
        }

        $user = $this->getUserDao()->update($userId, $fields);

        return $user;
    }

    public function batchUpdateOrg($userIds, $orgCode)
    {
        if (!is_array($userIds)) {
            $userIds = array($userIds);
        }
        $fields = $this->fillOrgId(array('orgCode' => $orgCode));

        foreach ($userIds as $userId) {
            $this->getUserDao()->update($userId, $fields);
        }
    }

    public function changeEmail($userId, $email)
    {
        if (!SimpleValidator::email($email)) {
            throw $this->createInvalidArgumentException('Email Invalid');
        }

        $user = $this->getUserDao()->getByEmail($email);

        if ($user && $user['id'] != $userId) {
            throw $this->createAccessDeniedException('Email Occupied');
        }

        $updatedUser = $this->getUserDao()->update($userId, array('email' => $email));
        $this->dispatchEvent('user.change_email', new Event($updatedUser));

        return $updatedUser;
    }

    public function changeAvatar($userId, $data)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        $fileIds = ArrayToolkit::column($data, 'id');
        $files = $this->getFileService()->getFilesByIds($fileIds);

        $files = ArrayToolkit::index($files, 'id');
        $fileIds = ArrayToolkit::index($data, 'type');

        $fields = array(
            'smallAvatar' => $files[$fileIds['small']['id']]['uri'],
            'mediumAvatar' => $files[$fileIds['medium']['id']]['uri'],
            'largeAvatar' => $files[$fileIds['large']['id']]['uri'],
        );

        $oldAvatars = array(
            'smallAvatar' => $user['smallAvatar'] ? $user['smallAvatar'] : null,
            'mediumAvatar' => $user['mediumAvatar'] ? $user['mediumAvatar'] : null,
            'largeAvatar' => $user['largeAvatar'] ? $user['largeAvatar'] : null,
        );

        $fileService = $this->getFileService();
        array_map(function ($oldAvatar) use ($fileService) {
            if (!empty($oldAvatar)) {
                $fileService->deleteFileByUri($oldAvatar);
            }
        }, $oldAvatars);

        $user = $this->getUserDao()->update($userId, $fields);

        return UserSerialize::unserialize($user);
    }

    public function updateUserUpdatedTime($id)
    {
        return $this->getUserDao()->update($id, array());
    }

    public function changeAvatarFromImgUrl($userId, $imgUrl, $options = array())
    {
        $filePath = $this->getKernel()->getParameter('topxia.upload.public_directory').'/tmp/'.$userId.'_'.time().'.jpg';
        $filePath = FileToolkit::downloadImg($imgUrl, $filePath);

        $file = new File($filePath);

        $groupCode = 'tmp';
        $imgs = array(
            'large' => array('200', '200'),
            'medium' => array('120', '120'),
            'small' => array('48', '48'),
        );
        $options = array_merge($options, array(
            'x' => '0',
            'y' => '0',
            'x2' => '200',
            'y2' => '200',
            'w' => '200',
            'h' => '200',
            'width' => '200',
            'height' => '200',
            'imgs' => $imgs,
        ));

        if (empty($options['group'])) {
            $options['group'] = 'default';
        }

        $record = $this->getFileService()->uploadFile($groupCode, $file);
        $parsed = $this->getFileService()->parseFileUri($record['uri']);
        $filePaths = FileToolkit::cropImages($parsed['fullpath'], $options);

        $fields = array();

        foreach ($filePaths as $key => $value) {
            $file = $this->getFileService()->uploadFile($options['group'], new File($value));
            $fields[] = array(
                'type' => $key,
                'id' => $file['id'],
            );
        }

        if (isset($options['deleteOriginFile']) && $options['deleteOriginFile'] == 0) {
            $fields[] = array(
                'type' => 'origin',
                'id' => $record['id'],
            );
        } else {
            $this->getFileService()->deleteFileByUri($record['uri']);
        }

        return $this->changeAvatar($userId, $fields);
    }

    public function isNicknameAvaliable($nickname)
    {
        if (empty($nickname)) {
            return false;
        }

        $user = $this->getUserDao()->getByNickname($nickname);

        return empty($user) ? true : false;
    }

    public function isEmailAvaliable($email)
    {
        if (empty($email)) {
            return false;
        }

        $user = $this->getUserDao()->getByEmail($email);

        return empty($user) ? true : false;
    }

    public function isMobileAvaliable($mobile)
    {
        if (empty($mobile)) {
            return false;
        }

        $user = $this->getUserDao()->getByVerifiedMobile($mobile);

        return empty($user) ? true : false;
    }

    public function changePassword($id, $password)
    {
        if (empty($password)) {
            throw $this->createInvalidArgumentException('参数不正确，更改密码失败');
        }

        if (!SimpleValidator::password($password)) {
            throw $this->createInvalidArgumentException('密码校验失败');
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException('user not found');
        }

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $fields = array(
            'salt' => $salt,
            'password' => $this->getPasswordEncoder()->encodePassword($password, $salt),
        );

        $this->getUserDao()->update($id, $fields);

        $this->markLoginSuccess($user['id'], $this->getCurrentUser()->currentIp);

        $this->getLogService()->info('user', 'password-changed', sprintf('用户%s(ID:%u)重置密码成功', $user['email'], $user['id']));

        return true;
    }

    public function changePayPassword($userId, $newPayPassword)
    {
        if (empty($newPayPassword)) {
            throw $this->createInvalidArgumentException('Invalid Argument');
        }

        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        $payPasswordSalt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $fields = array(
            'payPasswordSalt' => $payPasswordSalt,
            'payPassword' => $this->getPasswordEncoder()->encodePassword($newPayPassword, $payPasswordSalt),
        );

        $this->getUserDao()->update($userId, $fields);

        $this->getLogService()->info('user', 'pay-password-changed', sprintf('用户%s(ID:%u)重置支付密码成功', $user['email'], $user['id']));

        return true;
    }

    public function isMobileUnique($mobile)
    {
        $count = $this->countUsers(array('verifiedMobile' => $mobile));

        if ($count > 0) {
            return false;
        }

        return true;
    }

    public function changeMobile($id, $mobile)
    {
        if (empty($mobile)) {
            throw $this->createInvalidArgumentException('Invalid Argument');
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        $userGetByMobile = $this->getUserDao()->getByVerifiedMobile($mobile);
        if ($userGetByMobile && $userGetByMobile['id'] !== $user['id']) {
            throw $this->createServiceException('Mobile already existed', 10011);
        }

        $fields = array(
            'verifiedMobile' => $mobile,
        );

        $this->getUserDao()->update($id, $fields);
        $this->updateUserProfile($id, array(
            'mobile' => $mobile,
        ));
        $this->dispatchEvent('mobile.change', new Event($user));

        $this->getLogService()->info('user', 'verifiedMobile-changed', "用户{$user['email']}(ID:{$user['id']})重置mobile成功");

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
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        return $this->verifyInSaltOut($password, $user['salt'], $user['password']);
    }

    public function verifyPayPassword($id, $payPassword)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        return $this->verifyInSaltOut($payPassword, $user['payPasswordSalt'], $user['payPassword']);
    }

    public function parseRegistration($registration)
    {
        $mode = $this->getRegisterMode();

        if ($mode == 'email_or_mobile') {
            if (!empty($registration['emailOrMobile'])) {
                if (SimpleValidator::email($registration['emailOrMobile'])) {
                    $registration['email'] = $registration['emailOrMobile'];
                    $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_email';
                } elseif (SimpleValidator::mobile($registration['emailOrMobile'])) {
                    $registration['mobile'] = $registration['emailOrMobile'];
                    $registration['verifiedMobile'] = $registration['emailOrMobile'];
                    $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_mobile';
                } else {
                    throw $this->createInvalidArgumentException('Invalid Mobile or Email');
                }
            } else {
                throw $this->createInvalidArgumentException('Invalid Mobile or Email');
            }
        } elseif ($mode == 'mobile') {
            if (!empty($registration['mobile'])) {
                if (SimpleValidator::mobile($registration['mobile'])) {
                    $registration['verifiedMobile'] = $registration['mobile'];
                    $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_mobile';
                } else {
                    throw $this->createInvalidArgumentException('Invalid Mobile');
                }
            } else {
                throw $this->createInvalidArgumentException('Invalid Mobile');
            }
        } else {
            $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_email';

            return $registration;
        }

        return $registration;
    }

    public function isMobileRegisterMode()
    {
        $authSetting = $this->getSettingService()->get('auth');

        return !empty($authSetting['register_mode']) && (($authSetting['register_mode'] == 'email_or_mobile') || ($authSetting['register_mode'] == 'mobile'));
    }

    /**
     * email, email_or_mobile, mobile, null.
     */
    private function getRegisterMode()
    {
        $authSetting = $this->getSettingService()->get('auth');

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
            throw $this->createInvalidArgumentException('Invalid nickname');
        }
    }

    public function initSystemUsers()
    {
        $users = array(
            array(
                'type' => 'system',
                'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
            ),
        );
        foreach ($users as $user) {
            $existsUser = $this->getUserDao()->getUserByType($user['type']);

            if (!empty($existsUser)) {
                continue;
            }

            $user['nickname'] = $this->generateNickname($user).'(系统用户)';
            $user['emailVerified'] = 1;
            $user['orgId'] = 1;
            $user['orgCode'] = '1.';
            $user['password'] = $this->getRandomChar();
            $user['email'] = $this->generateEmail($user);
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($user['password'], $user['salt']);
            $user = UserSerialize::unserialize(
                $this->getUserDao()->create(UserSerialize::serialize($user))
            );

            $profile = array();
            $profile['id'] = $user['id'];
            $this->getProfileDao()->create($profile);
        }
    }

    public function getUserByType($type)
    {
        return $this->getUserDao()->getUserByType($type);
    }

    public function register($registration, $type = 'default')
    {
        $this->validateNickname($registration['nickname']);

        if (!$this->isNicknameAvaliable($registration['nickname'])) {
            throw $this->createInvalidArgumentException('Nickname Occupied');
        }

        if (!SimpleValidator::email($registration['email'])) {
            throw $this->createInvalidArgumentException('Invalid Email');
        }

        if (!$this->isEmailAvaliable($registration['email'])) {
            throw $this->createInvalidArgumentException('Email Occupied');
        }

        $user = array();

        if (isset($registration['verifiedMobile'])) {
            $user['verifiedMobile'] = $registration['verifiedMobile'];
        } else {
            $user['verifiedMobile'] = '';
        }

        $user['email'] = $registration['email'];
        $user['emailVerified'] = isset($registration['emailVerified']) ? $registration['emailVerified'] : 0;
        $user['nickname'] = $registration['nickname'];
        $user['roles'] = array('ROLE_USER');
        $user['type'] = isset($registration['type']) ? $registration['type'] : $type;
        $user['createdIp'] = empty($registration['createdIp']) ? '' : $registration['createdIp'];
        if (isset($registration['guid'])) {
            $user['guid'] = $registration['guid'];
        }

        $user['createdTime'] = time();
        $user['registeredWay'] = isset($registration['registeredWay']) ? $registration['registeredWay'] : '';

        $thirdLoginInfo = $this->getSettingService()->get('login_bind', array());

        if (in_array($type, array('default', 'phpwind', 'discuz'))) {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup'] = 1;
        } elseif (in_array($type, array('qq', 'weibo', 'renren', 'weixinweb', 'weixinmob')) && isset($thirdLoginInfo["{$type}_set_fill_account"]) && $thirdLoginInfo["{$type}_set_fill_account"]) {
            $user['salt'] = '';
            $user['password'] = '';
            $user['setup'] = 1;
        } elseif ($type === 'marketing') {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup'] = 0;
        } else {
            $user['salt'] = '';
            $user['password'] = '';
            $user['setup'] = 0;
        }
        if (isset($registration['orgId'])) {
            $user['orgId'] = $registration['orgId'];
            $user['orgCode'] = $registration['orgCode'];
        }
        $user = $this->getUserDao()->create($user);

        if (!empty($registration['invite_code'])) {
            $inviteUser = $this->getUserDao()->getByInviteCode($registration['invite_code']);
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
                new Event(array('userId' => $user['id'], 'inviteUserId' => $inviteUser['id']))
            );
        }

        if (isset($registration['mobile']) && $registration['mobile'] != '' && !SimpleValidator::mobile($registration['mobile'])) {
            throw $this->createInvalidArgumentException('Invalid Mobile');
        }

        if (isset($registration['idcard']) && $registration['idcard'] != '' && !SimpleValidator::idcard($registration['idcard'])) {
            throw $this->createInvalidArgumentException('Invalid ID number');
        }

        if (isset($registration['truename']) && $registration['truename'] != '' && !SimpleValidator::truename($registration['truename'])) {
            throw $this->createInvalidArgumentException('Invalid truename');
        }

        $profile = array();
        $profile['id'] = $user['id'];
        $profile['mobile'] = empty($registration['mobile']) ? '' : $registration['mobile'];
        $profile['idcard'] = empty($registration['idcard']) ? '' : $registration['idcard'];
        $profile['truename'] = empty($registration['truename']) ? '' : $registration['truename'];
        $profile['company'] = empty($registration['company']) ? '' : $registration['company'];
        $profile['job'] = empty($registration['job']) ? '' : $registration['job'];
        $profile['weixin'] = empty($registration['weixin']) ? '' : $registration['weixin'];
        $profile['weibo'] = empty($registration['weibo']) ? '' : $registration['weibo'];
        $profile['qq'] = empty($registration['qq']) ? '' : $registration['qq'];
        $profile['site'] = empty($registration['site']) ? '' : $registration['site'];
        $profile['gender'] = empty($registration['gender']) ? 'secret' : $registration['gender'];

        for ($i = 1; $i <= 5; ++$i) {
            $profile['intField'.$i] = empty($registration['intField'.$i]) ? null : $registration['intField'.$i];
            $profile['dateField'.$i] = empty($registration['dateField'.$i]) ? null : $registration['dateField'.$i];
            $profile['floatField'.$i] = empty($registration['floatField'.$i]) ? null : $registration['floatField'.$i];
        }

        for ($i = 1; $i <= 10; ++$i) {
            $profile['varcharField'.$i] = empty($registration['varcharField'.$i]) ? '' : $registration['varcharField'.$i];
            $profile['textField'.$i] = empty($registration['textField'.$i]) ? '' : $registration['textField'.$i];
        }

        $this->getProfileDao()->create($profile);

        if ($type != 'default') {
            $this->bindUser($type, $registration['token']['userId'], $user['id'], $registration['token']);
        }

        $this->dispatchEvent('user.registered', new Event($user));

        return $user;
    }

    public function newRegister($registration, $type = 'default', $registerType = 'email')
    {
        $register = $this->biz['user.register']->createRegister($registerType);

        list($user, $inviteUser) = $register->register($registration, $type);

        if (!empty($inviteUser)) {
            $this->dispatchEvent(
                'user.register',
                new Event(array('userId' => $user['id'], 'inviteUserId' => $inviteUser['id']))
            );
        }

        $this->dispatchEvent('user.registered', new Event($user));
        
        return $user;
    }

    public function generateNickname($registration, $maxLoop = 100)
    {
        $rawNickname = isset($registration['nickname']) ? $registration['nickname'] : '';
        if (!empty($rawNickname)) {
            $rawNickname = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-z0-9_.]+/u', '', $rawNickname);
            $rawNickname = str_replace(array('-'), array('_'), $rawNickname);

            if (!SimpleValidator::nickname($rawNickname)) {
                $rawNickname = '';
            }
            if ($this->isNicknameAvaliable($rawNickname)) {
                return $rawNickname;
            }
        }

        if (empty($rawNickname)) {
            $rawNickname = 'user';
        }
        $rawLen = (strlen($rawNickname) + mb_strlen($rawNickname, 'utf-8')) / 2;
        if ($rawLen > 12) {
            $rawNickname = substr($rawNickname, 0, -6);
        }
        for ($i = 0; $i < $maxLoop; ++$i) {
            $nickname = $rawNickname.substr($this->getRandomChar(), 0, 6);

            if ($this->isNicknameAvaliable($nickname)) {
                break;
            }
        }

        return $nickname;
    }

    public function generateEmail($registration, $maxLoop = 100)
    {
        for ($i = 0; $i < $maxLoop; ++$i) {
            $registration['email'] = 'user_'.substr($this->getRandomChar(), 0, 9).'@edusoho.net';

            if ($this->isEmailAvaliable($registration['email'])) {
                break;
            }
        }

        return $registration['email'];
    }

    public function importUpdateEmail($users)
    {
        $this->beginTransaction();
        try {
            for ($i = 0, $iMax = count($users); $i < $iMax; ++$i) {
                $member = $this->getUserDao()->getByEmail($users[$i]['email']);
                $member = UserSerialize::unserialize($member);
                $this->changePassword($member['id'], $users[$i]['password']);
                $this->updateUserProfile($member['id'], $users[$i]);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function setupAccount($userId)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        if ($user['setup']) {
            throw $this->createAccessDeniedException('Account has been set');
        }

        return $this->getUserDao()->update($userId, array('setup' => 1));
    }

    public function updateUserProfile($id, $fields)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException('user not found');
        }

        $fields = ArrayToolkit::filter($fields, array(
            'truename' => '',
            'gender' => 'secret',
            'iam' => '',
            'idcard' => '',
            'birthday' => null,
            'city' => '',
            'mobile' => '',
            'qq' => '',
            'school' => '',
            'class' => '',
            'company' => '',
            'job' => '',
            'signature' => '',
            'title' => '',
            'about' => '',
            'weibo' => '',
            'weixin' => '',
            'site' => '',
            'isWeiboPublic' => '',
            'isWeixinPublic' => '',
            'isQQPublic' => '',
            'intField1' => null,
            'intField2' => null,
            'intField3' => null,
            'intField4' => null,
            'intField5' => null,
            'dateField1' => null,
            'dateField2' => null,
            'dateField3' => null,
            'dateField4' => null,
            'dateField5' => null,
            'floatField1' => null,
            'floatField2' => null,
            'floatField3' => null,
            'floatField4' => null,
            'floatField5' => null,
            'textField1' => '',
            'textField2' => '',
            'textField3' => '',
            'textField4' => '',
            'textField5' => '',
            'textField6' => '',
            'textField7' => '',
            'textField8' => '',
            'textField9' => '',
            'textField10' => '',
            'varcharField1' => '',
            'varcharField2' => '',
            'varcharField3' => '',
            'varcharField4' => '',
            'varcharField5' => '',
            'varcharField6' => '',
            'varcharField7' => '',
            'varcharField8' => '',
            'varcharField9' => '',
            'varcharField10' => '',
        ));

        if (empty($fields)) {
            return $this->getProfileDao()->get($id);
        }

        if (isset($fields['title'])) {
            $this->getUserDao()->update($id, array('title' => $fields['title']));
            $this->dispatchEvent('user.update', new Event(array('user' => $user, 'fields' => $fields)));
        }

        unset($fields['title']);

        if (!empty($fields['gender']) && !in_array($fields['gender'], array('male', 'female', 'secret'))) {
            throw $this->createInvalidArgumentException('Invalid Gender');
        }

        if (!empty($fields['birthday']) && !SimpleValidator::date($fields['birthday'])) {
            throw $this->createInvalidArgumentException('Invalid Birthday');
        }

        if (!empty($fields['mobile']) && !SimpleValidator::mobile($fields['mobile'])) {
            throw $this->createInvalidArgumentException('Invalid Mobile');
        }

        if (!empty($fields['qq']) && !SimpleValidator::qq($fields['qq'])) {
            throw $this->createInvalidArgumentException('Invalid QQ');
        }

        if (!empty($fields['about'])) {
            $currentUser = $this->biz['user'];
            $trusted = $currentUser->isAdmin();
            $fields['about'] = $this->purifyHtml($fields['about'], $trusted);
        }

        if (!empty($fields['site']) && !SimpleValidator::site($fields['site'])) {
            throw $this->createInvalidArgumentException('个人空间不正确，更新用户失败');
        }
        if (!empty($fields['weibo']) && !SimpleValidator::site($fields['weibo'])) {
            throw $this->createInvalidArgumentException('微博地址不正确，更新用户失败');
        }
        if (!empty($fields['blog']) && !SimpleValidator::site($fields['blog'])) {
            throw $this->createInvalidArgumentException('地址不正确，更新用户失败');
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

        $fields = array_filter($fields, function ($value) {
            if ($value === 0) {
                return true;
            }

            return !empty($value);
        });

        $userProfile = $this->getProfileDao()->update($id, $fields);

        $this->dispatchEvent('profile.update', new Event(array('user' => $user, 'fields' => $fields)));

        return $userProfile;
    }

    public function changeUserRoles($id, array $roles)
    {
        if (empty($roles)) {
            throw $this->createInvalidArgumentException('Invalid Roles');
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        if (!in_array('ROLE_USER', $roles)) {
            throw $this->createInvalidArgumentException('Invalid Role Data');
        }
        $currentUser = $this->getCurrentUser();
        $currentUserRoles = $currentUser['roles'];

        $hiddenRoles = array();
        if (!in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
            $userRoles = $user['roles'];
            $hiddenRoles = array_diff($userRoles, $currentUserRoles);
        }

        $allowedRoles = array_merge($currentUserRoles, ArrayToolkit::column($this->getRoleService()->searchRoles(array('createdUserId' => $currentUser['id']), 'created', 0, 9999), 'code'));
        $notAllowedRoles = array_diff($roles, $allowedRoles);

        if (!empty($notAllowedRoles) && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'], true)) {
            throw $this->createInvalidArgumentException('Invalid Roles');
        }

        $roles = array_merge($roles, $hiddenRoles);

        $user = $this->getUserDao()->update($id, array('roles' => $roles));

        $this->dispatchEvent('user.role.change', new Event(UserSerialize::unserialize($user)));
        $this->getLogService()->info('user', 'change_role', "设置用户{$user['nickname']}(#{$user['id']})的角色为：".implode(',', $roles));

        return UserSerialize::unserialize($user);
    }

    public function makeToken($type, $userId = null, $expiredTime = null, $data = '', $args = array())
    {
        $token = array();
        $token['type'] = $type;
        $token['userId'] = $userId ? (int) $userId : 0;
        $token['token'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $token['data'] = $data;
        $token['times'] = empty($args['times']) ? 0 : (int) ($args['times']);
        $token['expiredTime'] = $expiredTime ? (int) $expiredTime : 0;
        $token['createdTime'] = time();
        $token = $this->getUserTokenDao()->create($token);

        return $token['token'];
    }

    public function getToken($type, $token)
    {
        $token = $this->getUserTokenDao()->getByToken($token);

        if (empty($token) || $token['type'] != $type) {
            return null;
        }

        if ($token['expiredTime'] > 0 && $token['expiredTime'] < time()) {
            return null;
        }

        return $token;
    }

    public function countTokens($conditions)
    {
        return $this->getUserTokenDao()->count($conditions);
    }

    public function deleteToken($type, $token)
    {
        $token = $this->getUserTokenDao()->getByToken($token);

        if (empty($token) || $token['type'] != $type) {
            return false;
        }

        $this->getUserTokenDao()->delete($token['id']);

        return true;
    }

    public function findBindsByUserId($userId)
    {
        $user = $this->getUserDao()->get($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        return $this->getUserBindDao()->findByToId($userId);
    }

    protected function typeInOAuthClient($type)
    {
        $types = array_keys(OAuthClientFactory::clients());
        $types = array_merge($types, array('discuz', 'phpwind', 'marketing'));

        return in_array($type, $types);
    }

    public function unBindUserByTypeAndToId($type, $toId)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createInvalidArgumentException('Invalid Type');
        }

        $bind = $this->getUserBindByTypeAndUserId($type, $toId);

        if ($bind) {
            $bind = $this->getUserBindDao()->delete($bind['id']);
            $currentUser = $this->getCurrentUser();
            $this->getLogService()->info('user', 'unbind', sprintf('用户名%s解绑成功，操作用户为%s', $user['nickname'], $currentUser['nickname']));
        }

        return $bind;
    }

    public function getUserBindByTypeAndFromId($type, $fromId)
    {
        if ($type == 'weixinweb' || $type == 'weixinmob') {
            $type = 'weixin';
        }

        return $this->getUserBindDao()->getByTypeAndFromId($type, $fromId);
    }

    public function getUserBindByToken($token)
    {
        return $this->getUserBindDao()->getByToken($token);
    }

    public function getUserBindByTypeAndUserId($type, $toId)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createInvalidArgumentException('Invalid Type');
        }

        if ($type == 'weixinweb' || $type == 'weixinmob') {
            $type = 'weixin';
        }

        return $this->getUserBindDao()->getByToIdAndType($type, $toId);
    }

    public function bindUser($type, $fromId, $toId, $token)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createInvalidArgumentException('Invalid Type');
        }

        if ($type == 'weixinweb' || $type == 'weixinmob') {
            $type = 'weixin';
        }

        $this->getUserBindDao()->create(array(
            'type' => $type,
            'fromId' => $fromId,
            'toId' => $toId,
            'token' => empty($token['token']) ? '' : $token['token'],
            'createdTime' => time(),
            'expiredTime' => empty($token['expiredTime']) ? 0 : $token['expiredTime'],
        ));
    }

    public function markLoginInfo()
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            return;
        }

        $this->getUserDao()->update($user['id'], array(
            'loginIp' => $user['currentIp'],
            'loginTime' => time(),
        ));
        //if user type is system,we do not record user login log
        if ($user['type'] == 'system') {
            return false;
        }
        $this->getLogService()->info('user', 'login_success', '登录成功');
    }

    public function markLoginFailed($userId, $ip)
    {
        $user = $userId ? $this->getUser($userId) : null;

        $setting = $this->getSettingService()->get('login_bind', array());

        $default = array(
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 5,
            'temporary_lock_minutes' => 20,
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

            $user = $this->getUserDao()->update($user['id'], $fields);
        }

        if ($user) {
            $log = sprintf('用户(%s)，', $user['nickname']).($user['consecutivePasswordErrorTimes'] ? sprintf('连续第%u次登录失败', $user['consecutivePasswordErrorTimes']) : '登录失败');
        } else {
            $log = sprintf('用户(IP: %s)，', $ip).($user['consecutivePasswordErrorTimes'] ? sprintf('连续第%u次登录失败', $user['consecutivePasswordErrorTimes']) : '登录失败');
        }

        $this->getLogService()->info('user', 'login_fail', $log);

        $ipFailedCount = $this->getIpBlacklistService()->increaseIpFailedCount($ip);

        return array(
            'failedCount' => $user['consecutivePasswordErrorTimes'],
            'leftFailedCount' => $setting['temporary_lock_allowed_times'] - $user['consecutivePasswordErrorTimes'],
            'ipFaildCount' => $ipFailedCount,
        );
    }

    public function markLoginSuccess($userId, $ip)
    {
        $fields = array(
            'lockDeadline' => 0,
            'consecutivePasswordErrorTimes' => 0,
            'lastPasswordFailTime' => 0,
        );

        $this->getUserDao()->update($userId, $fields);
        $this->getIpBlacklistService()->clearFailedIp($ip);
    }

    public function checkLoginForbidden($userId, $ip)
    {
        $user = $userId ? $this->getUser($userId) : null;

        $setting = $this->getSettingService()->get('login_bind', array());

        $default = array(
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 5,
            'ip_temporary_lock_allowed_times' => 20,
            'temporary_lock_minutes' => 20,
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
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        $this->getUserDao()->update($user['id'], array('locked' => 1));
        $this->dispatchEvent('user.lock', new Event($user));

        $this->getLogService()->info('user', 'lock', sprintf('封禁用户%s(#%u)', $user['nickname'], $user['id']));

        return true;
    }

    public function unlockUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        $this->getUserDao()->update($user['id'], array('locked' => 0));

        $this->dispatchEvent('user.unlock', new Event($user));

        $this->getLogService()->info('user', 'unlock', "解禁用户{$user['nickname']}(#{$user['id']})");

        return true;
    }

    public function promoteUser($id, $number)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        $user = $this->getUserDao()->update($user['id'], array('promoted' => 1, 'promotedSeq' => $number, 'promotedTime' => time()));
        $this->getLogService()->info('user', 'recommend', "推荐用户{$user['nickname']}(#{$user['id']})");

        return $user;
    }

    public function cancelPromoteUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        $user = $this->getUserDao()->update($user['id'], array('promoted' => 0, 'promotedSeq' => 0, 'promotedTime' => 0));

        $this->getLogService()->info('user', 'cancel_recommend', sprintf('取消推荐用户%s(#%u)', $user['nickname'], $user['id']));

        return $user;
    }

    public function findLatestPromotedTeacher($start, $limit)
    {
        return $this->searchUsers(array('roles' => 'ROLE_TEACHER', 'promoted' => 1), array('promotedTime' => 'DESC'), $start, $limit);
    }

    public function waveUserCounter($userId, $name, $number)
    {
        if (!ctype_digit((string) $number)) {
            throw $this->createInvalidArgumentException('Invalid Argument');
        }

        $this->getUserDao()->waveCounterById($userId, $name, $number);
    }

    public function clearUserCounter($userId, $name)
    {
        $this->getUserDao()->deleteCounterById($userId, $name);
    }

    public function filterFollowingIds($userId, array $followingIds)
    {
        if (empty($followingIds)) {
            return array();
        }

        $friends = $this->getFriendDao()->findByFromIdAndToIds($userId, $followingIds);

        return ArrayToolkit::column($friends, 'toId');
    }

    public function searchUserFollowings($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->searchByFromId($userId, $start, $limit);
        $ids = ArrayToolkit::column($friends, 'toId');

        return $this->findUsersByIds($ids);
    }

    public function findUserFollowings($userId)
    {
        $friends = $this->getFriendDao()->findFollowingsByFromId($userId);
        $ids = ArrayToolkit::column($friends, 'toId');

        return $this->findUsersByIds($ids);
    }

    public function countUserFollowings($userId)
    {
        return $this->getFriendDao()->count(array('fromId' => $userId));
    }

    public function searchUserFollowers($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->searchByToId($userId, $start, $limit);
        $ids = ArrayToolkit::column($friends, 'fromId');

        return $this->findUsersByIds($ids);
    }

    public function findAllUserFollowers($userId)
    {
        $friends = $this->getFriendDao()->findFollowersByToId($userId);
        $ids = ArrayToolkit::column($friends, 'fromId');

        return $this->findUsersByIds($ids);
    }

    public function countUserFollowers($userId)
    {
        return $this->getFriendDao()->count(array('toId' => $userId));
    }

    public function findFriends($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->search(
            array('fromId' => $userId, 'pair' => 1),
            null,
            $start,
            $limit
        );
        $ids = ArrayToolkit::column($friends, 'toId');

        return $this->findUsersByIds($ids);
    }

    public function countFriends($userId)
    {
        return $this->getFriendDao()->count(array('fromId' => $userId));
    }

    public function follow($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser = $this->getUser($toId);

        if (empty($fromUser)) {
            throw $this->createNotFoundException("User#{$fromId} Not Found");
        }

        if (empty($toUser)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        if ($fromId == $toId) {
            throw $this->createInvalidArgumentException('Invalid Argument');
        }

        $blacklist = $this->getBlacklistService()->getBlacklistByUserIdAndBlackId($toId, $fromId);

        if (!empty($blacklist)) {
            throw $this->createServiceException('Failed to Follow');
        }

        $friend = $this->getFriendDao()->getByFromIdAndToId($fromId, $toId);

        if (!empty($friend)) {
            throw $this->createAccessDeniedException('You have Followed User#{$toId}.');
        }

        $isFollowed = $this->isFollowed($toId, $fromId);
        $pair = $isFollowed ? 1 : 0;
        $friend = $this->getFriendDao()->create(array(
            'fromId' => $fromId,
            'toId' => $toId,
            'createdTime' => time(),
            'pair' => $pair,
        ));
        $this->getFriendDao()->updateByFromIdAndToId($fromId, $toId, array('pair' => $pair));

        if ($isFollowed) {
            $this->getFriendDao()->updateByFromIdAndToId($toId, $fromId, array('pair' => $pair));
        }

        $this->dispatchEvent('user.follow', new Event($friend));

        return $friend;
    }

    public function unFollow($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser = $this->getUser($toId);

        if (empty($fromUser)) {
            throw $this->createNotFoundException("User#{$fromId} Not Found");
        }

        if (empty($toUser)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        $friend = $this->getFriendDao()->getByFromIdAndToId($fromId, $toId);

        if (empty($friend)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $result = $this->getFriendDao()->delete($friend['id']);
        $isFollowed = $this->isFollowed($toId, $fromId);

        if ($isFollowed) {
            $this->getFriendDao()->updateByFromIdAndToId($toId, $fromId, array('pair' => 0));
        }

        $this->dispatchEvent('user.unfollow', new Event($friend));

        return $result;
    }

    public function hasAdminRoles($userId)
    {
        $user = $this->getUser($userId);

        $roles = $this->getRoleService()->findRolesByCodes($user['roles']);

        foreach ($roles as $role) {
            if (in_array('admin', $role['data'], true)) {
                return true;
            }
        }

        return false;
    }

    public function isFollowed($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser = $this->getUser($toId);

        if (empty($fromUser)) {
            throw $this->createNotFoundException("User#{$fromId} Not Found");
        }

        if (empty($toUser)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        $friend = $this->getFriendDao()->getByFromIdAndToId($fromId, $toId);

        if (empty($friend)) {
            return false;
        } else {
            return true;
        }
    }

    public function getLastestApprovalByUserIdAndStatus($userId, $status)
    {
        return $this->getUserApprovalDao()->getLastestByUserIdAndStatus($userId, $status);
    }

    public function findUserApprovalsByUserIds($userIds)
    {
        return $this->getUserApprovalDao()->findByUserIds($userIds);
    }

    public function applyUserApproval($userId, $approval, UploadedFile $faceImg, UploadedFile $backImg, $directory)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        $faceImgPath = 'userFaceImg'.$userId.time().'.'.$faceImg->getClientOriginalExtension();
        $backImgPath = 'userbackImg'.$userId.time().'.'.$backImg->getClientOriginalExtension();
        $faceImg = $faceImg->move($directory, $faceImgPath);
        $backImg = $backImg->move($directory, $backImgPath);

        $approval['userId'] = $user['id'];
        $approval['faceImg'] = $faceImg->getPathname();
        $approval['backImg'] = $backImg->getPathname();
        $approval['status'] = 'approving';
        $approval['createdTime'] = time();

        $this->getUserDao()->update($userId, array(
            'approvalStatus' => 'approving',
            'approvalTime' => time(),
        ));

        $this->getUserApprovalDao()->create($approval);

        return true;
    }

    public function passApproval($userId, $note = null)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        $this->getUserDao()->update($user['id'], array(
            'approvalStatus' => 'approved',
            'approvalTime' => time(),
        ));

        $lastestApproval = $this->getUserApprovalDao()->getLastestByUserIdAndStatus($user['id'], 'approving');

        $this->getProfileDao()->update(
            $userId,
            array(
                'truename' => $lastestApproval['truename'],
                'idcard' => $lastestApproval['idcard'],
            )
        );

        $currentUser = $this->getCurrentUser();
        $this->getUserApprovalDao()->update(
            $lastestApproval['id'],
            array(
                'userId' => $user['id'],
                'note' => $note,
                'status' => 'approved',
                'operatorId' => $currentUser['id'],
            )
        );

        $this->getLogService()->info('user', 'approved', sprintf('用户%s实名认证成功，操作人:%s !', $user['nickname'], $currentUser['nickname']));

        $message = array(
            'note' => $note ? $note : '',
            'type' => 'through',
        );
        $this->getNotificationService()->notify($user['id'], 'truename-authenticate', $message);

        return true;
    }

    public function rejectApproval($userId, $note = null)
    {
        $user = $this->getUserDao()->get($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        $this->getUserDao()->update($user['id'], array(
            'approvalStatus' => 'approve_fail',
            'approvalTime' => time(),
        ));

        $lastestApproval = $this->getUserApprovalDao()->getLastestByUserIdAndStatus($user['id'], 'approving');
        $currentUser = $this->getCurrentUser();
        $this->getUserApprovalDao()->update(
            $lastestApproval['id'],
            array(
                'userId' => $user['id'],
                'note' => $note,
                'status' => 'approve_fail',
                'operatorId' => $currentUser['id'],
            )
        );

        $this->getLogService()->info('user', 'approval_fail', sprintf('用户%s实名认证失败，操作人:%s !', $user['nickname'], $currentUser['nickname']));
        $message = array(
            'note' => $note ? $note : '',
            'type' => 'reject',
        );
        $this->getNotificationService()->notify($user['id'], 'truename-authenticate', $message);

        return true;
    }

    public function dropFieldData($fieldName)
    {
        $this->getProfileDao()->dropFieldData($fieldName);
    }

    public function rememberLoginSessionId($id, $sessionId)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$id} Not Found");
        }

        return $this->getUserDao()->update($id, array(
            'loginSessionId' => $sessionId,
        ));
    }

    public function analysisRegisterDataByTime($startTime, $endTime)
    {
        return $this->getUserDao()->analysisRegisterDataByTime($startTime, $endTime);
    }

    public function parseAts($text)
    {
        preg_match_all('/@([\x{4e00}-\x{9fa5}\w]{2,16})/u', $text, $matches);

        $users = $this->getUserDao()->findByNicknames(array_unique($matches[1]));

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
        return $this->getUserDao()->getByInviteCode($inviteCode);
    }

    public function findUserIdsByInviteCode($inviteCode)
    {
        $inviteUser = $this->getUserDao()->getByInviteCode($inviteCode);
        $record = $this->getInviteRecordService()->findRecordsByInviteUserId($inviteUser['id']);
        $userIds = ArrayToolkit::column($record, 'invitedUserId');

        return $userIds;
    }

    public function createInviteCode($userId)
    {
        $inviteCode = StringToolkit::createRandomString(5);
        $inviteCode = strtoupper($inviteCode);
        $code = array(
            'inviteCode' => $inviteCode,
        );

        return $this->getUserDao()->update($userId, $code);
    }

    public function findUnlockedUserMobilesByUserIds($userIds, $needVerified = false)
    {
        if (empty($userIds)) {
            return array();
        }

        $conditions = array(
            'locked' => 0,
            'userIds' => $userIds,
        );

        if ($needVerified) {
            $conditions['hasVerifiedMobile'] = true;
            $count = $this->countUsers($conditions);
            $users = $this->searchUsers($conditions, array('createdTime' => 'ASC'), 0, $count);
            $mobiles = ArrayToolkit::column($users, 'verifiedMobile');

            return $mobiles;
        } else {
            $profiles = $this->searchUserProfiles(array('mobileNotEqual' => '', 'ids' => $userIds), array('id' => 'ASC'), 0, PHP_INT_MAX);
            $profileMobiles = ArrayToolkit::column($profiles, 'mobile');

            return array_unique($profileMobiles);
        }
    }

    public function updateUserLocale($id, $locale)
    {
        $this->getUserDao()->update($id, array('locale' => $locale));
    }

    public function getUserPayAgreement($id)
    {
        return $this->getUserPayAgreementDao()->get($id);
    }

    public function getUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth)
    {
        return $this->getUserPayAgreementDao()->getByUserIdAndBankAuth($userId, $bankAuth);
    }

    public function getUserPayAgreementByUserId($userId)
    {
        return $this->getUserPayAgreementDao()->getByUserId($userId);
    }

    public function createUserPayAgreement($field)
    {
        $field = ArrayToolkit::parts($field, array('userId', 'type', 'bankName', 'bankNumber', 'userAuth', 'bankAuth', 'bankId', 'createdTime'));

        return $this->getUserPayAgreementDao()->create($field);
    }

    public function updateUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth, $fields)
    {
        return $this->getUserPayAgreementDao()->updateByUserIdAndBankAuth($userId, $bankAuth, $fields);
    }

    public function findUserPayAgreementsByUserId($userId)
    {
        return $this->getUserPayAgreementDao()->findByUserId($userId);
    }

    public function deleteUserPayAgreements($id)
    {
        return $this->getUserPayAgreementDao()->delete($id);
    }

    public function getUserIdsByKeyword($keyword)
    {
        if (SimpleValidator::email($keyword)) {
            $user = $this->getUserByEmail($keyword);

            return $user ? array($user['id']) : array(-1);
        }

        if (SimpleValidator::mobile($keyword)) {
            $mobileVerifiedUser = $this->getUserByVerifiedMobile($keyword);
            $profileUsers = $this->searchUserProfiles(
                array('tel' => $keyword),
                array('id' => 'DESC'),
                0,
                PHP_INT_MAX
            );
            $mobileNameUser = $this->getUserByNickname($keyword);
            $userIds = $profileUsers ? ArrayToolkit::column($profileUsers, 'id') : null;

            $userIds[] = $mobileVerifiedUser ? $mobileVerifiedUser['id'] : null;
            $userIds[] = $mobileNameUser ? $mobileNameUser['id'] : null;

            $userIds = array_unique($userIds);

            return $userIds ? $userIds : array(-1);
        }
        $user = $this->getUserByNickname($keyword);

        return $user ? array($user['id']) : array(-1);
    }

    protected function _prepareApprovalConditions($conditions)
    {
        if (!empty($conditions['keywordType']) && $conditions['keywordType'] == 'truename') {
            $conditions['truename'] = trim($conditions['keyword']);
        }

        if (!empty($conditions['keywordType']) && $conditions['keywordType'] == 'idcard') {
            $conditions['idcard'] = trim($conditions['keyword']);
        }

        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        return $conditions;
    }

    /**
     * @return UserApprovalDao
     */
    protected function getUserApprovalDao()
    {
        return $this->createDao('User:UserApprovalDao');
    }

    /**
     * @return FriendDao
     */
    protected function getFriendDao()
    {
        return $this->createDao('User:FriendDao');
    }

    /**
     * @return CouponDao
     */
    protected function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    /**
     * @return UserProfileDao
     */
    protected function getProfileDao()
    {
        return $this->createDao('User:UserProfileDao');
    }

    /**
     * @return UserSecureQuestionDao
     */
    protected function getUserSecureQuestionDao()
    {
        return $this->createDao('User:UserSecureQuestionDao');
    }

    /**
     * @return UserBindDao
     */
    protected function getUserBindDao()
    {
        return $this->createDao('User:UserBindDao');
    }

    /**
     * @return TokenDao
     */
    protected function getUserTokenDao()
    {
        return $this->createDao('User:TokenDao');
    }

    /**
     * @return UserFortuneLogDao
     */
    protected function getUserFortuneLogDao()
    {
        return $this->createDao('User:UserFortuneLogDao');
    }

    /**
     * @return CardService
     */
    protected function getCardService()
    {
        return $this->getKernel()->createService('Card:CardService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->getKernel()->createService('Coupon:CouponService');
    }

    /**
     * @return UserPayAgreementDao
     */
    protected function getUserPayAgreementDao()
    {
        return $this->createDao('User:UserPayAgreementDao');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return IpBlacklistService
     */
    protected function getIpBlacklistService()
    {
        return $this->createService('System:IpBlacklistService');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    /**
     * @return BlacklistService
     */
    protected function getBlacklistService()
    {
        return $this->createService('User:BlacklistService');
    }

    /**
     * @return InviteRecordService
     */
    protected function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->getKernel()->createService('Role:RoleService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->getKernel()->createService('Org:OrgService');
    }

    public function getKernel()
    {
        return ServiceKernel::instance();
    }
}

class UserSerialize
{
    public static function serialize(array $user)
    {
        return $user;
    }

    public static function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }

        $user = self::_userRolesSort($user);

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
            $temp = $user['roles'][1];
            $user['roles'][1] = $user['roles'][0];
            $user['roles'][0] = $temp;
        }

        //交换学员角色跟roles数组第0个的位置;

        return $user;
    }
}
