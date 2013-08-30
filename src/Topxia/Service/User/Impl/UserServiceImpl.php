<?php
namespace Topxia\Service\User\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Topxia\Common\SimpleValidator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\User\UserService;
use Topxia\Service\User\CurrentUser;


class UserServiceImpl extends BaseService implements UserService
{

    public function getUser($id)
    {
        $user = $this->getUserDao()->getUser($id);
        if(!$user){
            return null;
        } else {
            return UserSerialize::unserialize($user);
        }
    }

    public function getUserProfile($id)
    {
        return $this->getProfileDao()->getProfile($id);
    }

    public function getUserByNickname($nickname){
        $user = $this->getUserDao()->findUserByNickname($nickname);
        if(!$user){
            return null;
        } else {
            return UserSerialize::unserialize($user);
        }
    }

    public function getUserByEmail($email)
    {
        $user = $this->getUserDao()->findUserByEmail($email);
        if(!$user){
            return null;
        } else {
            return UserSerialize::unserialize($user);
        }
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
        return  ArrayToolkit::index($userProfiles, 'id');
    }

    public function searchUsers(array $conditions, $start, $limit)
    {
        $users = $this->getUserDao()->searchUsers($conditions, $start, $limit);
        return UserSerialize::unserializes($users);
    }

    public function searchUserCount(array $conditions)
    {
        return $this->getUserDao()->searchUserCount($conditions);
    }

    public function setEmailVerified($userId)
    {
        $this->getUserDao()->updateUser($userId, array('emailVerified' => 1));
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
        $this->getUserDao()->updateUser($userId, array('email' => $email));
    }

    public function changeAvatar($userId, UploadedFile $file)
    {
        $user = $this->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，头像更新失败！');
        }

        $file = $this->getFileService()->uploadFile('user', $file);

        $smallAvatarFile = $this->getFileService()->thumbnailFile($file, array(
            'mode' => 'outbound',
            'width' => 48,
            'height' => 48,
        ));

        $mediumAvatarFile = $this->getFileService()->thumbnailFile($file, array(
            'mode' => 'outbound',
            'width' => 100,
            'height' => 100,
        ));

        $largeAvatarFile = $this->getFileService()->thumbnailFile($file, array(
            'mode' => 'inset',
            'width' => 220,
            'height' => 220,
        ));

        $this->getUserDao()->updateUser($userId, array(
            'smallAvatar' => $smallAvatarFile['uri'],
            'mediumAvatar' => $mediumAvatarFile['uri'],
            'largeAvatar' => $largeAvatarFile['uri'],
        ));
        return true;
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

    public function changePassword($id, $password)
    {
        $user = $this->getUser($id);
        if (empty($user) or empty($password)) {
            throw $this->createServiceException('参数不正确，更改密码失败。');
        }

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $fields = array(
            'salt' => $salt,
            'password' => $this->getPasswordEncoder()->encodePassword($password, $salt),
        );

        $this->getUserDao()->updateUser($id, $fields);

        return true;
    }

    public function verifyPassword($id, $password)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            throw $this->createServiceException('参数不正确，校验密码失败。');
        }

        $encoder = $this->getPasswordEncoder();
        $passwordHash = $encoder->encodePassword($password, $user['salt']);
        return $user['password'] == $passwordHash;
    }

    public function register($registration, $type = 'default')
    {
        if (!SimpleValidator::email($registration['email'])) {
            throw $this->createServiceException('email error!');
        }
        
        if (!SimpleValidator::nickname($registration['nickname'])) {
            throw $this->createServiceException('nickname error!');
        }

        if (!$this->isEmailAvaliable($registration['email'])) {
            throw $this->createServiceException('Email已存在');
        }

        if (!$this->isNicknameAvaliable($registration['nickname'])) {
            throw $this->createServiceException('昵称已存在');
        }

        $user = array();
        $user['email'] = $registration['email'];
        $user['nickname'] = $registration['nickname'];
        $user['roles'] =  array('ROLE_USER');
        $user['type'] = $type;
        $user['createdTime'] = time();

        if($type == 'default') {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
        } else {
            $user['salt'] = '';
            $user['password'] = ''; 
        }
        $user = UserSerialize::unserialize(
            $this->getUserDao()->addUser(UserSerialize::serialize($user))
        );
        $this->getProfileDao()->addProfile(array('id' => $user['id']));
        if ($type != 'default') {
            $this->bindUser($type, $registration['token']['userId'], $user['id'], $registration['token']);
        }
        return $user;
    }

    public function increaseCoin ($userId, $coin, $action = null, $note = null) 
    {
        $user = $this->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，增加金币失败！');
        }

        $coin = (int) $coin;
        if ($coin <= 0) {
            throw $this->createServiceException('金币值不正确，增加金币失败！');
        }

        $this->getUserDao()->waveCoin($user['id'], $coin);

        $log = array(
            'userId' => $user['id'],
            'type' => 'coin',
            'number' => $coin,
            'action' => $action ? : '',
            'note' => $note ? : '',
            'createdTime' => time(),
        );

        $this->getUserFortuneLogDao()->addLog($log);

        return true;
    }

    public function decreaseCoin ($userId, $coin, $action = null, $note = null) 
    {
        $user = $this->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，扣除金币失败！');
        }

        $coin = (int) $coin;
        if ($coin <= 0) {
            throw $this->createServiceException('金币值不正确，扣除金币失败！');
        }

        if ($user['coin'] - $coin < 0) {
            throw $this->createServiceException('金币不足，扣除金币失败！');
        }

        $this->getUserDao()->waveCoin($user['id'], -$coin);

        $log = array(
            'userId' => $user['id'],
            'type' => 'coin',
            'number' => -$coin,
            'action' => $action ? : '',
            'note' => $note ? : '',
            'createdTime' => time(),
        );

        $this->getUserFortuneLogDao()->addLog($log);

        return true;
    }

    public function updateUserProfile($id, $fields)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，更新用户失败。');
        }

        $availableFields = array(
            'truename', 'gender', 'birthday', 'city', 'mobile', 'qq', 'company', 'job', 'signature', 'title',  'about', 'weibo', 'weixin', 'site'
        );

        $fields = ArrayToolkit::parts($fields, $availableFields);

        if (array_key_exists('title', $fields)) {
            $this->getUserDao()->updateUser($id, array('title' => $fields['title']));
        }
        unset($fields['title']);

        if (!empty($fields['gender']) && !in_array($fields['gender'], array('male', 'female'))) {
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

        if(isset($fields['about'])){
            $fields['about'] = $this->purifyHtml($fields['about']);
        }
        
        return $this->getProfileDao()->updateProfile($id, $fields);
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

        $allowedRoles = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN','ROLE_TEACHER');

        $notAllowedRoles = array_diff($roles, $allowedRoles);
        if (!empty($notAllowedRoles)) {
            throw $this->createServiceException('用户角色不正确，设置用户角色失败。');
        }
        $this->getUserDao()->updateUser($id, UserSerialize::serialize(array('roles' => $roles)));
    } 

    public function makeToken($type, $userId = null, $expiredTime = null, $data = null)
    {
        $token = array();
        $token['type'] = $type;
        $token['userId'] = $userId ? (int)$userId : 0;
        $token['token'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $token['data'] = serialize($data);
        $token['expiredTime'] = $expiredTime ? (int) $expiredTime : 0;
        $token['createdTime'] = time();
        $token = $this->getUserTokenDao()->addToken($token);
        return $token['token'];
    }

    public function getToken($type, $token)
    {
        $token = $this->getUserTokenDao()->findTokenByToken($token);
        if (empty($token) || $token['type'] != $type) {
            return null;
        }
        if ($token['expiredTime'] > 0 && $token['expiredTime'] < time()) {
            return null;
        }
        $token['data'] = unserialize($token['data']);
        return $token;
    }

    public function deleteToken($type, $token)
    {
        $token = $this->getUserTokenDao()->findTokenByToken($token);
        if (empty($token) || $token['type'] != $type) {
            return false;
        }
        $this->getUserTokenDao()->deleteToken($token['id']);
        return true;
    }

    public function findBindsByUserId($userId)
    {
        $user = $this->getUserDao()->getUser($userId);
        if (empty($user)){
            throw $this->createServiceException('获取用户绑定信息失败，当前用户不存在');
        }
        return $this->getUserBindDao()->findBindsByToId($userId);
    }

    public function unBindUserByTypeAndToId ($type, $toId) 
    {
        $user = $this->getUserDao()->getUser($toId);
        if(empty($user)) {
            throw $this->createServiceException('解除第三方绑定失败，该用户不存在');
        }

        $result = in_array($type, array('qq','renren','weibo'),true);
        if(!$result) {
            throw $this->createServiceException('解除第三方绑定失败,当前只支持weibo,qq,renren');
        }
        $bind = $this->getUserBindByTypeAndUserId($type, $toId);
        if($bind){
          $bind = $this->getUserBindDao()->deleteBind($bind['id']);
        }
        return $bind;
    }

    public function getUserBindByTypeAndFromId($type, $fromId)
    {
        return $this->getUserBindDao()->getBindByTypeAndFromId($type, $fromId);
    }

    public function getUserBindByTypeAndUserId($type, $toId)
    {
        $user = $this->getUserDao()->getUser($toId);
        if(empty($user)) {
            throw $this->createServiceException('获取用户绑定信息失败，该用户不存在');
        }

        $result = in_array($type, array('qq','renren','weibo'),true);
        if(!$result) {
            throw $this->createServiceException('获取第三方登陆信息失败,当前只支持weibo,qq,renren');
        }

        return $this->getUserBindDao()->getBindByToIdAndType($type, $toId);
    }

    public function bindUser($type, $fromId, $toId, $token)
    {
        $user = $this->getUserDao()->getUser($toId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，第三方绑定失败');
        }
        $result = in_array($type, array('qq','renren','weibo'),true);
        if(!$result) {
            throw $this->createServiceException('第三方绑定失败,当前只支持weibo,qq,renren');
        }
        return $this->getUserBindDao()->addBind(array(
            'type' => $type,
            'fromId' => $fromId,
            'toId'=>$toId,
            'token'=>$token['token'],
            'createdTime'=>time(),
            'expiredTime'=>$token['expiredTime']
            ));
    }
    
    public function markLoginInfo()
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            return ;
        }

        $this->getUserDao()->updateUser($user['id'], array(
            'loginIp' => $user['currentIp'],
            'loginTime' => time(),
        ));

        $this->getLogService()->info('user', 'login_success', '登录成功');
    }

    public function lockUser($id)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，封禁失败！');
        }
        $this->getUserDao()->updateUser($user['id'], array('locked' => 1));

        return true;
    }

    public function unlockUser($id)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，解禁失败！');
        }
        $this->getUserDao()->updateUser($user['id'], array('locked' => 0));

        return true;
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

    public function findUserFollowing($userId)
    {
        return $this->getFriendDao()->findFriendsByFromId($userId);
    }

   public function findUserFollowers($userId)
   {
        return $this->getFriendDao()->findFriendsByToId($userId);
   }

    public function follow($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser = $this->getUser($toId);
        if(empty($fromUser) || empty($toUser)) {
            throw $this->createServiceException('用户不存在，关注失败！');
        }
        if($fromId == $toId) {
            throw $this->createServiceException('不能关注自己！');
        }
        $friend = $this->getFriendDao()->getFriendByFromIdAndToId($fromId, $toId);
        if(!empty($friend)) {
            throw $this->createServiceException('不允许重复关注!');
        }
        return $this->getFriendDao()->addFriend(array(
            "fromId"=>$fromId,
            "toId"=>$toId,
            "createdTime"=>time()));
    }

    public function unFollow($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser = $this->getUser($toId);
        if(empty($fromUser) || empty($toUser)) {
            throw $this->createServiceException('用户不存在，取消关注失败！');
        }
        $friend = $this->getFriendDao()->getFriendByFromIdAndToId($fromId, $toId);
        if(empty($friend)) {
            throw $this->createServiceException('不存在此关注关系，取消关注失败！');
        }
        return $this->getFriendDao()->deleteFriend($friend['id']);
    }

    public function isFollowed($fromId, $toId)
    {
        $fromUser = $this->getUser($fromId);
        $toUser = $this->getUser($toId);
        if(empty($fromUser)) {
            throw $this->createServiceException('用户不存在，检测关注状态失败！');
        }
        
        if(empty($toUser)) {
            throw $this->createServiceException('被关注者不存在，检测关注状态失败！');
        }

        $friend = $this->getFriendDao()->getFriendByFromIdAndToId($fromId, $toId);
        if(empty($friend)) {
            return false;
        } else {
            return true;
        }
    }

    private function getFriendDao()
    {
        return $this->createDao("User.FriendDao");
    }

    private function getUserDao()
    {
        return $this->createDao('User.UserDao');
    }

    private function getProfileDao()
    {
        return $this->createDao('User.UserProfileDao');
    }

    private function getUserBindDao()
    {
        return $this->createDao('User.UserBindDao');
    }

    private function getUserTokenDao()
    {
        return $this->createDao('User.TokenDao');
    }

    private function getUserFortuneLogDao()
    {
        return $this->createDao('User.UserFortuneLogDao');
    }

    private function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');        
    }

    private function getPasswordEncoder()
    {
        return $this->getContainer()->get('security.encoder_factory')->getEncoder(new CurrentUser());
    }

}

class UserSerialize
{
    public static function serialize(array $user)
    {
        $user['roles'] = empty($user['roles']) ? '' :  '|' . implode('|', $user['roles']) . '|';
        return $user;
    }

    public static function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }
        $user['roles'] = empty($user['roles']) ? array() : explode('|', trim($user['roles'], '|')) ;
        return $user;
    }

    public static function unserializes(array $users)
    {
        return array_map(function($user) {
            return UserSerialize::unserialize($user);
        }, $users);
    }

}