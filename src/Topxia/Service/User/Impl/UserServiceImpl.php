<?php
namespace Topxia\Service\User\Impl;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Common\SimpleValidator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\User\UserService;
use Topxia\Service\User\CurrentUser;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

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
        if (empty($email)) {
            return null;
        }
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

    public function searchUsers(array $conditions, array $oderBy, $start, $limit)
    {
        $users = $this->getUserDao()->searchUsers($conditions, $oderBy, $start, $limit);
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

    public function changeNickname ($userId, $nickname)
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
        $this->getLogService()->info('user', 'nickname_change', "修改用户名{$user['nickname']}为{$nickname}成功");
        $this->getUserDao()->updateUser($userId, array('nickname' => $nickname));
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

    public function changeAvatar($userId, $filePath, array $options)
    {
        $user = $this->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，头像更新失败！');
        }


        $pathinfo = pathinfo($filePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $largeImage = $rawImage->copy();
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(200, 200));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 90));
        $largeFileRecord = $this->getFileService()->uploadFile('user', new File($largeFilePath));

        $largeImage->resize(new Box(120, 120));
        $mediumFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_medium.{$pathinfo['extension']}";
        $largeImage->save($mediumFilePath, array('quality' => 90));
        $mediumFileRecord = $this->getFileService()->uploadFile('user', new File($mediumFilePath));

        $largeImage->resize(new Box(48, 48));
        $smallFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_small.{$pathinfo['extension']}";
        $largeImage->save($smallFilePath, array('quality' => 90));
        $smallFileRecord = $this->getFileService()->uploadFile('user', new File($smallFilePath));
        @unlink($filePath);

        $oldAvatars = array(
            'smallAvatar' => $user['smallAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $user['smallAvatar']) : null,
            'mediumAvatar' => $user['mediumAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $user['mediumAvatar']) : null,
            'largeAvatar' => $user['largeAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $user['largeAvatar']) : null
        );

        array_map(function($oldAvatar){
            if (!empty($oldAvatar)) {
                @unlink($oldAvatar);
            }
        }, $oldAvatars);

        return  $this->getUserDao()->updateUser($userId, array(
            'smallAvatar' => $smallFileRecord['uri'],
            'mediumAvatar' => $mediumFileRecord['uri'],
            'largeAvatar' => $largeFileRecord['uri'],
        ));
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

        $this->getLogService()->info('user', 'password-changed', "用户{$user['email']}(ID:{$user['id']})重置密码成功");

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
        $user['createdIp'] = empty($registration['createdIp']) ? '' : $registration['createdIp'];
        $user['createdTime'] = time();

        if(in_array($type, array('default', 'phpwind', 'discuz'))) {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup'] = 1;
        } else {
            $user['salt'] = '';
            $user['password'] = '';
            $user['setup'] = 0;
        }
        $user = UserSerialize::unserialize(
            $this->getUserDao()->addUser(UserSerialize::serialize($user))
        );

        if (isset($registration['mobile']) &&$registration['mobile']!=""&& !SimpleValidator::mobile($registration['mobile'])) {
            throw $this->createServiceException('mobile error!');
        }

        if (isset($registration['idcard']) &&$registration['idcard']!=""&& !SimpleValidator::idcard($registration['idcard'])) {
            throw $this->createServiceException('idcard error!');
        }

        if (isset($registration['truename']) &&$registration['truename']!=""&& !SimpleValidator::truename($registration['truename'])) {
            throw $this->createServiceException('truename error!');
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
        for($i=1;$i<=5;$i++){
            $profile['intField'.$i] = empty($registration['intField'.$i]) ? null : $registration['intField'.$i];
            $profile['dateField'.$i] = empty($registration['dateField'.$i]) ? null : $registration['dateField'.$i];
            $profile['floatField'.$i] = empty($registration['floatField'.$i]) ? null : $registration['floatField'.$i];
        }
        for($i=1;$i<=10;$i++){
            $profile['varcharField'.$i] = empty($registration['varcharField'.$i]) ? "" : $registration['varcharField'.$i];
            $profile['textField'.$i] = empty($registration['textField'.$i]) ? "" : $registration['textField'.$i];
        }

        $this->getProfileDao()->addProfile($profile);
        if ($type != 'default') {
            $this->bindUser($type, $registration['token']['userId'], $user['id'], $registration['token']);
        }

        $this->getDispatcher()->dispatch('user.service.registered', new ServiceEvent($user));

        return $user;
    }

    public function importUpdateEmail($users)
    {

        $this->getUserDao()->getConnection()->beginTransaction();
        try{

            for($i=0;$i<count($users);$i++){
                $member = $this->getUserDao()->findUserByEmail($users[$i]["email"]);
                $member=UserSerialize::unserialize($member);
                $this->changePassword($member["id"],$users[$i]["password"]);
                $this->updateUserProfile($member["id"],$users[$i]);              
            }

             $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
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

        $this->getUserDao()->updateUser($userId, array('setup' => 1));

        return $this->getUser($userId);
    }

    public function updateUserProfile($id, $fields)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，更新用户失败。');
        }

        $fields = ArrayToolkit::filter($fields, array(
            'truename' => '',
            'gender' => 'secret',
            'iam' => '',
            'idcard'=>'',
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
            'intField1'=>null,
            'intField2'=>null,
            'intField3'=>null,
            'intField4'=>null,
            'intField5'=>null,
            'dateField1'=>null,
            'dateField2'=>null,
            'dateField3'=>null,
            'dateField4'=>null,
            'dateField5'=>null,
            'floatField1'=>null,
            'floatField2'=>null,
            'floatField3'=>null,
            'floatField4'=>null,
            'floatField5'=>null,
            'textField1'=>"",
            'textField2'=>"",
            'textField3'=>"",
            'textField4'=>"",
            'textField5'=>"",
            'textField6'=>"",
            'textField7'=>"",
            'textField8'=>"",
            'textField9'=>"",
            'textField10'=>"",
            'varcharField1'=>"",
            'varcharField2'=>"",
            'varcharField3'=>"",
            'varcharField4'=>"",
            'varcharField5'=>"",
            'varcharField6'=>"",
            'varcharField7'=>"",
            'varcharField8'=>"",
            'varcharField9'=>"",
            'varcharField10'=>"",
        ));

        if (empty($fields)) {
            return $this->getProfileDao()->getProfile($id);
        }

        if (isset($fields['title'])) {
            $this->getUserDao()->updateUser($id, array('title' => $fields['title']));
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

        if(!empty($fields['about'])) $fields['about'] = $this->purifyHtml($fields['about']);

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

        if (!in_array('ROLE_USER', $roles)) {
            throw $this->createServiceException('用户角色必须包含ROLE_USER');
        }

        $allowedRoles = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN','ROLE_TEACHER');

        $notAllowedRoles = array_diff($roles, $allowedRoles);
        if (!empty($notAllowedRoles)) {
            throw $this->createServiceException('用户角色不正确，设置用户角色失败。');
        }
        $this->getUserDao()->updateUser($id, UserSerialize::serialize(array('roles' => $roles)));

        $this->getLogService()->info('user', 'change_role', "设置用户{$user['nickname']}(#{$user['id']})的角色为：" . implode(',', $roles));
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

    public function searchTokenCount($conditions)
    {
        return $this->getUserTokenDao()->searchTokenCount($conditions);
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

        $result = in_array($type, array('qq','renren','weibo', 'discuz', 'phpwind'), true);
        if(!$result) {
            throw $this->createServiceException('获取第三方登录信息失败,当前只支持weibo,qq,renren');
        }

        return $this->getUserBindDao()->getBindByToIdAndType($type, $toId);
    }

    public function bindUser($type, $fromId, $toId, $token)
    {
        $user = $this->getUserDao()->getUser($toId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，第三方绑定失败');
        }
        $result = in_array($type, array('qq','renren','weibo', 'discuz', 'phpwind'), true);
        if(!$result) {
            throw $this->createServiceException('第三方绑定失败,当前只支持weibo,qq,renren, discuz, phpwind');
        }
        return $this->getUserBindDao()->addBind(array(
            'type' => $type,
            'fromId' => $fromId,
            'toId'=>$toId,
            'token'=> empty($token['token']) ? '' : $token['token'],
            'createdTime'=>time(),
            'expiredTime'=>empty($token['expiredTime']) ? 0 : $token['expiredTime'],
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

        $this->getLogService()->info('user', 'unlock', "解禁用户{$user['nickname']}(#{$user['id']})");

        return true;
    }

    public function promoteUser($id)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，推荐失败！');
        }
        $this->getUserDao()->updateUser($user['id'], array('promoted' => 1, 'promotedTime' => time()));

        $this->getLogService()->info('user', 'recommend', "推荐用户{$user['nickname']}(#{$user['id']})");
    }

    public function cancelPromoteUser($id)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，取消推荐失败！');
        }
        $this->getUserDao()->updateUser($user['id'], array('promoted' => 0, 'promotedTime' => 0));

        $this->getLogService()->info('user', 'cancel_recommend', "取消推荐用户{$user['nickname']}(#{$user['id']})");
    }

    public function findLatestPromotedTeacher($start, $limit)
    {
        return $this->searchUsers(array('roles' => 'ROLE_TEACHER', 'promoted' => 1), array('promotedTime', 'DESC'),  $start, $limit);
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
        $ids = ArrayToolkit::column($friends, 'toId');
        return $this->findUsersByIds($ids);
    }

    public function findUserFollowingCount($userId)
    {
        return $this->getFriendDao()->findFriendCountByFromId($userId);
    }

    public function findUserFollowers($userId, $start, $limit)
    {
        $friends = $this->getFriendDao()->findFriendsByToId($userId, $start, $limit);
        $ids = ArrayToolkit::column($friends, 'fromId');
        return $this->findUsersByIds($ids);
    }

    public function findUserFollowerCount($userId)
    {
        return $this->getFriendDao()->findFriendCountByToId($userId);
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

        $faceImgPath = 'userFaceImg'.$userId.time().'.'. $faceImg->getClientOriginalExtension();
        $backImgPath = 'userbackImg'.$userId.time().'.'. $backImg->getClientOriginalExtension();
        $faceImg = $faceImg->move($directory, $faceImgPath);
        $backImg = $backImg->move($directory, $backImgPath);
        
        $approval['userId'] = $user['id'];
        $approval['faceImg'] = $faceImg->getPathname();
        $approval['backImg'] = $backImg->getPathname();
        $approval['status'] = 'approving';
        $approval['createdTime'] = time();

        $this->getUserDao()->updateUser($userId, array(
            'approvalStatus' => 'approving',
            'approvalTime' => time()
        ));

        $this->getUserApprovalDao()->addApproval($approval);

        return true;
    }

    public function passApproval($userId, $note = null)
    {
        $user = $this->getUserDao()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户#{$userId}不存在！");
        }

        $this->getUserDao()->updateUser($user['id'], array(
            'approvalStatus' => 'approved',
            'approvalTime' => time()
        ));

        $lastestApproval = $this->getUserApprovalDao()->getLastestApprovalByUserIdAndStatus($user['id'],'approving');

        $this->getProfileDao()->updateProfile($userId, array(
            'truename'=>$lastestApproval['truename'],
            'idcard'=> $lastestApproval['idcard'])
        );
        
        $currentUser = $this->getCurrentUser();
        $this->getLogService()->info('user', 'approved', "用户{$user['nickname']}实名认证成功，操作人:{$currentUser['nickname']} !" );
        $message = '您的个人实名认证，审核已经通过！' . ($note ? "({$note})" : '');
        $this->getNotificationService()->notify($user['id'], 'default', $message);
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
            'approvalTime' => time(),
        ));

        $currentUser = $this->getCurrentUser();
        $this->getUserApprovalDao()->addApproval(
            array(
            'userId'=> $user['id'],
            'note'=> $note,
            'status' => 'approve_fail',
            'operatorId' => $currentUser['id'])
        );

        $this->getLogService()->info('user', 'approval_fail', "用户{$user['nickname']}实名认证失败，操作人:{$currentUser['nickname']} !" );
    
        $message = '您的个人实名认证，审核未通过！' . ($note ? "({$note})" : '');
        $this->getNotificationService()->notify($user['id'], 'default', $message);
        return true;
    }
    
    public function dropFieldData($fieldName)
    {
        $this->getProfileDao()->dropFieldData($fieldName);
    }

    public function getUserCountByApprovalStatus($approvalStatus)
    {
        return $this->getUserDao()->searchUserCount(array('approvalStatus' => $approvalStatus));
    }

    public function getUsersByApprovalStatus($approvalStatus, $start, $limit)
    {
        return $this->getUserDao()->searchUsers(array('approvalStatus' => $approvalStatus), 
            array('approvalTime', 'DESC'), $start, $limit);
    }

    private function getUserApprovalDao()
    {
        return $this->createDao("User.UserApprovalDao");
    }

    public function rememberLoginSessionId ($id, $sessionId)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，检测关注状态失败！');
        }

        return $this->getUserDao()->updateUser($id, array(
            'loginSessionId' => $sessionId
        ));
    }

    public function analysisRegisterDataByTime($startTime,$endTime)
    {
        return $this->getUserDao()->analysisRegisterDataByTime($startTime,$endTime);
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

    private function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
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