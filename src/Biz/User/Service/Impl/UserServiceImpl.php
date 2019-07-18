<?php

namespace Biz\User\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\SimpleValidator;
use AppBundle\Common\StringToolkit;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Biz\BaseService;
use Biz\Card\Service\CardService;
use Biz\Common\CommonException;
use Biz\Content\FileException;
use Biz\Content\Service\FileService;
use Biz\Coupon\Dao\CouponDao;
use Biz\Coupon\Service\CouponService;
use Biz\Org\OrgException;
use Biz\Org\Service\OrgService;
use Biz\Role\Service\RoleService;
use Biz\System\Service\IpBlacklistService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Dao\FriendDao;
use Biz\User\Dao\TokenDao;
use Biz\User\Dao\UserApprovalDao;
use Biz\User\Dao\UserBindDao;
use Biz\User\Dao\UserDao;
use Biz\User\Dao\UserFortuneLogDao;
use Biz\User\Dao\UserPayAgreementDao;
use Biz\User\Dao\UserProfileDao;
use Biz\User\Dao\UserSecureQuestionDao;
use Biz\User\Service\AuthService;
use Biz\User\Service\BlacklistService;
use Biz\User\Service\InviteRecordService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Topxia\Service\Common\ServiceKernel;

class UserServiceImpl extends BaseService implements UserService
{
    public function getUser($id, $lock = false)
    {
        $user = $this->getUserDao()->get($id, array('lock' => $lock));

        return !$user ? null : UserSerialize::unserialize($user);
    }

    public function getUserAndProfile($id)
    {
        $user = $this->getUserDao()->get($id);

        if (!empty($user)) {
            $profile = $this->getProfileDao()->get($id);
            $user = array_merge($user, $profile);
        }

        return $user;
    }

    public function countUsers(array $conditions)
    {
        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = strtoupper($conditions['nickname']);
        }

        return $this->getUserDao()->count($conditions);
    }

    public function searchUsers(array $conditions, array $orderBy, $start, $limit, $columns = array())
    {
        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = strtoupper($conditions['nickname']);
        }

        $users = $this->getUserDao()->search($conditions, $orderBy, $start, $limit, $columns = array());

        return UserSerialize::unserializes($users);
    }

    public function changeRawPassword($id, $rawPassword)
    {
        if (empty($rawPassword)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $this->getUserDao()->update($id, $rawPassword);

        $this->refreshLoginSecurityFields($user['id'], $this->getCurrentUser()->currentIp);

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

        if (isset($user['type']) && 'system' == $user['type']) {
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
            $users = $this->getUserDao()->findUnlockedUsersWithMobile($start, $limit);
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

    public function searchUserProfiles(array $conditions, array $orderBy, $start, $limit, $columns = array())
    {
        $profiles = $this->getProfileDao()->search($conditions, $orderBy, $start, $limit, $columns);

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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (!SimpleValidator::nickname($nickname)) {
            $this->createNewException(UserException::NICKNAME_INVALID());
        }

        $existUser = $this->getUserDao()->getByNickname($nickname);

        if ($existUser && $existUser['id'] != $userId) {
            $this->createNewException(UserException::NICKNAME_EXISTED());
        }

        $updatedUser = $this->getUserDao()->update($userId, array('nickname' => $nickname));
        $this->dispatchEvent('user.change_nickname', new Event($updatedUser));
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
                $this->createNewException(OrgException::NOTFOUND_ORG());
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
            $this->createNewException(UserException::EMAIL_INVALID());
        }

        $user = $this->getUserDao()->getByEmail($email);

        if ($user && $user['id'] != $userId) {
            $this->createNewException(UserException::EMAIL_EXISTED());
        }

        $updatedUser = $this->getUserDao()->update($userId, array('email' => $email));
        $this->dispatchEvent('user.change_email', new Event($updatedUser));

        return $updatedUser;
    }

    public function changeAvatar($userId, $data)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
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

    public function changeAvatarByFileId($userId, $fileId)
    {
        if (empty($fileId)) {
            $this->createNewException(FileException::FILE_NOT_FOUND());
        }
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        $options = $this->createImgCropOptions($naturalSize, $scaledSize);
        $record = $this->getFileService()->getFile($fileId);
        if (empty($record)) {
            $this->createNewException(FileException::FILE_NOT_FOUND());
        }
        $parsed = $this->getFileService()->parseFileUri($record['uri']);

        $filePaths = FileToolKit::cropImages($parsed['fullpath'], $options);

        $fields = array();
        foreach ($filePaths as $key => $filePath) {
            $file = $this->getFileService()->uploadFile('user', new File($filePath));
            $fields[] = array(
                'type' => $key,
                'id' => $file['id'],
            );
        }

        if (isset($options['deleteOriginFile']) && 0 == $options['deleteOriginFile']) {
            $fields[] = array(
                'type' => 'origin',
                'id' => $record['id'],
            );
        } else {
            $this->getFileService()->deleteFileByUri($record['uri']);
        }

        if (empty($fields)) {
            $this->createNewException(FileException::FILE_HANDLE_ERROR());
        }

        return $this->changeAvatar($userId, $fields);
    }

    private function createImgCropOptions($naturalSize, $scaledSize)
    {
        $options = array();

        $options['x'] = 0;
        $options['y'] = 0;
        $options['x2'] = $scaledSize->getWidth();
        $options['y2'] = $scaledSize->getHeight();
        $options['w'] = $naturalSize->getWidth();
        $options['h'] = $naturalSize->getHeight();

        $options['imgs'] = array();
        $options['imgs']['large'] = array(200, 200);
        $options['imgs']['medium'] = array(120, 120);
        $options['imgs']['small'] = array(48, 48);
        $options['width'] = $naturalSize->getWidth();
        $options['height'] = $naturalSize->getHeight();

        return $options;
    }

    public function updateUserUpdatedTime($id)
    {
        return $this->getUserDao()->update($id, array());
    }

    public function changeAvatarFromImgUrl($userId, $imgUrl, $options = array())
    {
        $filePath = $this->getKernel()->getParameter('topxia.upload.public_directory').'/tmp/'.$userId.'_'.time().'.jpg';

        $mock = isset($options['mock']) ? $options['mock'] : false;
        $filePath = FileToolkit::downloadImg($imgUrl, $filePath, $mock);

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

        if (isset($options['deleteOriginFile']) && 0 == $options['deleteOriginFile']) {
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
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        if (!SimpleValidator::password($password)) {
            $this->createNewException(UserException::PASSWORD_INVALID());
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $fields = array(
            'salt' => $salt,
            'password' => $this->getPasswordEncoder()->encodePassword($password, $salt),
        );

        $this->getUserDao()->update($id, $fields);

        $this->refreshLoginSecurityFields($user['id'], $this->getCurrentUser()->currentIp);

        return true;
    }

    public function changePayPassword($userId, $newPayPassword)
    {
        if (empty($newPayPassword)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $user = $this->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $payPasswordSalt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $fields = array(
            'payPasswordSalt' => $payPasswordSalt,
            'payPassword' => $this->getPasswordEncoder()->encodePassword($newPayPassword, $payPasswordSalt),
        );

        $this->getUserDao()->update($userId, $fields);

        return true;
    }

    public function isMobileUnique($mobile)
    {
        $count = $this->countUsers(array('wholeVerifiedMobile' => $mobile));

        if ($count > 0) {
            return false;
        }

        return true;
    }

    public function changeMobile($id, $mobile)
    {
        if (empty($mobile)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $userGetByMobile = $this->getUserDao()->getByVerifiedMobile($mobile);
        if ($userGetByMobile && $userGetByMobile['id'] !== $user['id']) {
            $this->createNewException(UserException::MOBILE_EXISTED());
        }

        $fields = array(
            'verifiedMobile' => $mobile,
        );

        $this->getUserDao()->update($id, $fields);
        $this->updateUserProfile($id, array(
            'mobile' => $mobile,
        ));

        $this->dispatchEvent('user.change_mobile', new Event($user));

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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        return $this->verifyInSaltOut($password, $user['salt'], $user['password']);
    }

    public function verifyPayPassword($id, $payPassword)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        return $this->verifyInSaltOut($payPassword, $user['payPasswordSalt'], $user['payPassword']);
    }

    public function parseRegistration($registration)
    {
        $mode = $this->getRegisterMode();

        if ('email_or_mobile' == $mode) {
            if (!empty($registration['emailOrMobile'])) {
                if (SimpleValidator::email($registration['emailOrMobile'])) {
                    $registration['email'] = $registration['emailOrMobile'];
                    $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_email';
                } elseif (SimpleValidator::mobile($registration['emailOrMobile'])) {
                    $registration['mobile'] = $registration['emailOrMobile'];
                    $registration['verifiedMobile'] = $registration['emailOrMobile'];
                    $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_mobile';
                } else {
                    $this->createNewException(UserException::MOBILE_OR_EMAIL_INVALID());
                }
            } else {
                $this->createNewException(UserException::MOBILE_OR_EMAIL_INVALID());
            }
        } elseif ('mobile' == $mode) {
            if (!empty($registration['mobile'])) {
                if (SimpleValidator::mobile($registration['mobile'])) {
                    $registration['verifiedMobile'] = $registration['mobile'];
                    $registration['type'] = isset($registration['type']) ? $registration['type'] : 'web_mobile';
                } else {
                    $this->createNewException(UserException::MOBILE_INVALID());
                }
            } else {
                $this->createNewException(UserException::MOBILE_INVALID());
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

        return !empty($authSetting['register_mode']) && (('email_or_mobile' == $authSetting['register_mode']) || ('mobile' == $authSetting['register_mode']));
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
            $this->createNewException(UserException::NICKNAME_INVALID());
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

    public function getUserByUUID($uuid)
    {
        return $this->getUserDao()->getByUUID($uuid);
    }

    /**
     * @registration type属性使用了原先的 $type 参数, 不填，则为default （原先的接口参数为 $registration, $type)
     *
     * @param $registerTypes 数组，可以是多个类型的组合
     *   类型范围  email, mobile, binder(第三方登录)
     */
    public function register($registration, $registerTypes = array('email'))
    {
        $register = $this->biz['user.register']->createRegister($registerTypes);

        list($user, $inviteUser) = $register->register($registration);

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

    public function updateUserProfile($id, $fields, $strict = true)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
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
            $this->createNewException(UserException::GENDER_INVALID());
        }

        if (!empty($fields['birthday']) && !SimpleValidator::date($fields['birthday'])) {
            $this->createNewException(UserException::BIRTHDAY_INVALID());
        }

        if (!empty($fields['mobile']) && !SimpleValidator::mobile($fields['mobile'])) {
            $this->createNewException(UserException::MOBILE_INVALID());
        }

        if (!empty($fields['qq']) && !SimpleValidator::qq($fields['qq'])) {
            $this->createNewException(UserException::QQ_INVALID());
        }

        if (!empty($fields['weixin']) && !SimpleValidator::weixin($fields['weixin'])) {
            $this->createNewException(UserException::WEIXIN_INVALID());
        }

        if (!empty($fields['about'])) {
            $currentUser = $this->biz['user'];
            $trusted = $currentUser->isAdmin();
            $fields['about'] = $this->purifyHtml($fields['about'], $trusted);
        }

        if (!empty($fields['site']) && !SimpleValidator::site($fields['site'])) {
            $this->createNewException(UserException::SITE_INVALID());
        }
        if (!empty($fields['weibo']) && !SimpleValidator::site($fields['weibo'])) {
            $this->createNewException(UserException::WEIBO_INVALID());
        }
        if (!empty($fields['blog']) && !SimpleValidator::site($fields['blog'])) {
            $this->createNewException(UserException::BLOG_INVALID());
        }

        $dateFields = array('dateField1', 'dateField2', 'dateField3', 'dateField4', 'dateField5');
        foreach ($dateFields as $dateField) {
            if (empty($fields[$dateField])) {
                $fields[$dateField] = null;
            }

            if (!empty($fields[$dateField]) && !SimpleValidator::date($fields[$dateField])) {
                $this->createNewException(UserException::DATEFIELD_INVALID());
            }
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

        if ($strict) {
            $fields = array_filter($fields, function ($value) {
                if (0 === $value) {
                    return true;
                }

                return !empty($value);
            });
        }

        $userProfile = $this->getProfileDao()->update($id, $fields);
        $this->dispatchEvent('profile.update', new Event(array('user' => $user, 'fields' => $fields)));

        return $userProfile;
    }

    public function changeUserRoles($id, array $roles)
    {
        if (empty($roles)) {
            $this->createNewException(UserException::ROLES_INVALID());
        }

        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (!in_array('ROLE_USER', $roles)) {
            $this->createNewException(UserException::ROLES_INVALID());
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
            $this->createNewException(UserException::ROLES_INVALID());
        }

        $roles = array_merge($roles, $hiddenRoles);

        $user = $this->getUserDao()->update($id, array('roles' => $roles));

        $this->dispatchEvent('user.role.change', new Event(UserSerialize::unserialize($user)));

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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        return $this->getUserBindDao()->findByToId($userId);
    }

    protected function typeInOAuthClient($type)
    {
        $types = array_keys(OAuthClientFactory::clients());
        $types = array_merge($types, array('discuz', 'phpwind', 'marketing', 'wechat_app'));

        return in_array($type, $types);
    }

    public function unBindUserByTypeAndToId($type, $toId)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (!$this->typeInOAuthClient($type)) {
            $this->createNewException(UserException::CLIENT_TYPE_INVALID());
        }

        $bind = $this->getUserBindByTypeAndUserId($type, $toId);
        if ($bind) {
            $convertedType = $this->convertOAuthType($type);
            $this->getUserBindDao()->deleteByTypeAndToId($convertedType, $toId);
            $currentUser = $this->getCurrentUser();
            $this->dispatchEvent('user.unbind', new Event($user, array('bind' => $bind, 'bindType' => $type, 'convertedType' => $convertedType)));
            $this->getLogService()->info('user', 'unbind', sprintf('用户名%s解绑成功，操作用户为%s', $user['nickname'], $currentUser['nickname']));
        }

        return $bind;
    }

    public function getUserBindByTypeAndFromId($type, $fromId)
    {
        $type = $this->convertOAuthType($type);

        return $this->getUserBindDao()->getByTypeAndFromId($type, $fromId);
    }

    public function findUserBindByTypeAndFromIds($type, $fromIds)
    {
        $type = $this->convertOAuthType($type);

        return $this->getUserBindDao()->findByTypeAndFromIds($type, $fromIds);
    }

    public function findUserBindByTypeAndToIds($type, $toIds)
    {
        $type = $this->convertOAuthType($type);

        return $this->getUserBindDao()->findByTypeAndToIds($type, $toIds);
    }

    public function getUserBindByToken($token)
    {
        return $this->getUserBindDao()->getByToken($token);
    }

    public function getUserBindByTypeAndUserId($type, $toId)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (!$this->typeInOAuthClient($type)) {
            $this->createNewException(UserException::CLIENT_TYPE_INVALID());
        }

        $type = $this->convertOAuthType($type);

        return $this->getUserBindDao()->getByToIdAndType($type, $toId);
    }

    public function findUserBindByTypeAndUserId($type, $toId)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $type = $this->convertOAuthType($type);

        return $this->getUserBindDao()->findByToIdAndType($type, $toId);
    }

    public function bindUser($type, $fromId, $toId, $token)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (!$this->typeInOAuthClient($type)) {
            $this->createNewException(UserException::CLIENT_TYPE_INVALID());
        }

        $convertedType = $this->convertOAuthType($type);

        $bind = $this->getUserBindDao()->create(array(
            'type' => $convertedType,
            'fromId' => $fromId,
            'toId' => $toId,
            'token' => empty($token['token']) ? '' : $token['token'],
            'createdTime' => time(),
            'expiredTime' => empty($token['expiredTime']) ? 0 : $token['expiredTime'],
        ));

        $this->dispatchEvent('user.bind', new Event($user, array('bind' => $bind, 'bindType' => $type, 'convertedType' => $convertedType, 'token' => $token)));
    }

    public function markLoginInfo($type = null)
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
        if ('system' == $user['type']) {
            return false;
        }

        $this->refreshLoginSecurityFields($user['id'], $this->getCurrentUser()->currentIp);

        if (in_array($type, array('weixinweb', 'qq', 'weibo', 'app'))) {
            $this->getLogService()->info('mobile', 'login_success', "通过{$type}登录");
        } else {
            $this->getLogService()->info('user', 'login_success', '登录成功');
        }
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

    public function refreshLoginSecurityFields($userId, $ip)
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
            $this->createNewException(UserException::NOTFOUND_USER());
        }
        $currentUser = $this->getCurrentUser();
        if ($id === $currentUser['id']) {
            $this->createNewException(UserException::LOCK_SELF_DENIED());
        }
        if (in_array('ROLE_SUPER_ADMIN', $user['roles']) && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
            $this->createNewException(UserException::LOCK_DENIED());
        }
        $this->getUserDao()->update($user['id'], array('locked' => 1));
        $this->dispatchEvent('user.lock', new Event($user));

        return true;
    }

    public function unlockUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $this->getUserDao()->update($user['id'], array('locked' => 0));

        $this->dispatchEvent('user.unlock', new Event($user));

        return true;
    }

    public function promoteUser($id, $number)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $user = $this->getUserDao()->update($user['id'], array('promoted' => 1, 'promotedSeq' => $number, 'promotedTime' => time()));
        $this->getLogService()->info('user', 'recommend', "推荐用户{$user['nickname']}(#{$user['id']})");

        return $user;
    }

    public function cancelPromoteUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
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
            $this->createNewException(CommonException::ERROR_PARAMETER());
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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (empty($toUser)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if ($fromId == $toId) {
            $this->createNewException(UserException::FOLLOW_SELF());
        }

        $blacklist = $this->getBlacklistService()->getBlacklistByUserIdAndBlackId($toId, $fromId);

        if (!empty($blacklist)) {
            $this->createNewException(UserException::FOLLOW_BLACK());
        }

        $friend = $this->getFriendDao()->getByFromIdAndToId($fromId, $toId);

        if (!empty($friend)) {
            $this->createNewException(UserException::DUPLICATE_FOLLOW());
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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (empty($toUser)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $friend = $this->getFriendDao()->getByFromIdAndToId($fromId, $toId);

        if (empty($friend)) {
            $this->createNewException(UserException::UNFOLLOW_ERROR());
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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (empty($toUser)) {
            $this->createNewException(UserException::NOTFOUND_USER());
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
            $this->createNewException(UserException::NOTFOUND_USER());
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
            $this->createNewException(UserException::NOTFOUND_USER());
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
            $this->createNewException(UserException::NOTFOUND_USER());
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
            $this->createNewException(UserException::NOTFOUND_USER());
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

    public function findUnlockedUserMobilesByUserIds($userIds)
    {
        if (empty($userIds)) {
            return array();
        }

        $conditions = array(
            'locked' => 0,
            'userIds' => $userIds,
        );

        $conditions['hasVerifiedMobile'] = true;
        $count = $this->countUsers($conditions);
        $users = $this->searchUsers($conditions, array('createdTime' => 'ASC'), 0, $count);
        $mobiles = ArrayToolkit::column($users, 'verifiedMobile');

        return $mobiles;
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

    public function updateUserNewMessageNum($id, $num)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return;
        }
        $newMessageNum = $user['newMessageNum'] - 1;
        if ($newMessageNum >= 0 && $num > 0) {
            $this->getUserDao()->update($id, array('newMessageNum' => $newMessageNum));
            $user->__set('newMessageNum', $newMessageNum);
        }
    }

    public function makeUUID()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    public function generateUUID()
    {
        $uuid = $this->makeUUID();
        $user = $this->getUserByUUID($uuid);

        if (empty($user)) {
            return $uuid;
        } else {
            return $this->generateUUID();
        }
    }

    /**
     * @param $clientIp 客户端ip
     * @param $updateCount 默认为false，为true时，表示 在能发送短信的情况下， 查询后会变为必须填图形验证码
     *
     * @return
     *  1. captchaRequired  必须填图形验证码
     *  2. captchaIgnored  不需要图形验证码
     *  3. smsUnsendable 不能发送短信
     */
    public function getSmsRegisterCaptchaStatus($clientIp, $updateCount = false)
    {
        $registerSetting = $this->getSettingService()->get('auth', array());
        if (!empty($registerSetting['register_mode']) &&
            in_array($registerSetting['register_mode'], array('mobile', 'email_or_mobile'))) {
            $registerProtective = empty($registerSetting['register_protective']) ?
            'none' : $registerSetting['register_protective'];
            if (in_array($registerProtective, array('middle', 'low'))) {
                $factory = $this->biz->offsetGet('ratelimiter.factory');
                $rateLimiter = $factory('sms_registration_captcha_code', 1, 3600);
                $used = $updateCount ? 1 : 0;
                $leftTriedCount = $rateLimiter->check($clientIp, $used);
                if ($leftTriedCount <= 0) {
                    return 'captchaRequired';
                } else {
                    return 'captchaIgnored';
                }
            } elseif ('high' == $registerProtective) {
                return 'captchaRequired';
            } else {
                return 'captchaIgnored';
            }
        }

        return 'smsUnsendable';
    }

    public function updateSmsRegisterCaptchaStatus($clientIp)
    {
        return $this->getSmsRegisterCaptchaStatus($clientIp, true);
    }

    public function initPassword($id, $newPassword)
    {
        $this->beginTransaction();

        try {
            $fields = array(
                'passwordInit' => 1,
            );

            $this->getAuthService()->changePassword($id, null, $newPassword);
            $this->getUserDao()->update($id, $fields);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $this->getUserDao()->update($id, $fields);
    }

    public function setFaceRegistered($id)
    {
        return $this->getUserDao()->update($id, array('faceRegistered' => 1));
    }

    protected function _prepareApprovalConditions($conditions)
    {
        if (!empty($conditions['keywordType']) && 'truename' == $conditions['keywordType']) {
            $conditions['truename'] = trim($conditions['keyword']);
        }

        if (!empty($conditions['keywordType']) && 'idcard' == $conditions['keywordType']) {
            $conditions['idcard'] = trim($conditions['keyword']);
        }

        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        return $conditions;
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
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
        return $this->createService('Card:CardService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
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
        return $this->createService('Role:RoleService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    public function getKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @param $type
     *
     * @return string
     */
    private function convertOAuthType($type)
    {
        if ('weixinweb' == $type || 'weixinmob' == $type) {
            $type = 'weixin';
        }

        return $type;
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
        if (!empty($user['roles'][1]) && 'ROLE_USER' == $user['roles'][1]) {
            $temp = $user['roles'][1];
            $user['roles'][1] = $user['roles'][0];
            $user['roles'][0] = $temp;
        }

        //交换学员角色跟roles数组第0个的位置;

        return $user;
    }
}
