<?php
namespace Topxia\Service\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UserService
{
    
    public function getUser($id, $lock = false);

    public function getUserByNickname($nickname);

    public function getUserByVerifiedMobile($mobile);
    
    public function getUserByEmail($email);

    public function findUsersByIds(array $ids);

    public function findUserProfilesByIds(array $ids);

    public function searchUsers(array $conditions, array $orderBy, $start, $limit);

    public function searchUserCount(array $conditions);

    public function setEmailVerified($userId);

    public function changeNickname($userId, $nickname);

    public function changeEmail($userId, $email);

    public function changeAvatar($userId, $filePath, array $options);

    public function isNicknameAvaliable($nickname);

    public function isEmailAvaliable($email);

    public function hasAdminRoles($userId);

    public function rememberLoginSessionId($id, $sessionId);

    public function changePayPassword($userId, $newPayPassword);

    public function verifyPayPassword($id, $payPassword);

    public function getUserSecureQuestionsByUserId($userId);

    public function addUserSecureQuestionsWithUnHashedAnswers($userId,$fieldsWithQuestionTypesAndUnHashedAnswers);

    public function verifyInSaltOut($in,$salt,$out);

    public function isMobileUnique($mobile);

    public function changeMobile($id, $mobile);
    
    /**
     * 变更密码
     * 
     * @param  [integer]    $id       用户ID
     * @param  [string]     $password 新密码
     */
    public function changePassword($id, $password);

    /**
     * 校验密码是否正确
     * 
     * @param  [integer]    $id       用户ID
     * @param  [string]     $password 密码
     * 
     * @return [boolean] 密码正确，返回true；错误，返回false。
     */
    public function verifyPassword($id, $password);

    /**
     * 用户注册
     *
     * 当type为default时，表示用户从自身网站注册。
     * 当type为weibo、qq、renren时，表示用户从第三方网站连接，允许注册信息没有密码。
     * 
     * @param  [type] $registration 用户注册信息
     * @param  string $type         注册类型
     * @return array 用户信息
     */
    public function register($registration, $type = 'default');

    public function setupAccount($userId);

    public function markLoginInfo();

    public function updateUserProfile($id, $fields);

    public function getUserProfile($id);

    public function changeUserRoles($id, array $roles);

    /**
     * @deprecated move to TokenService
     */
    public function makeToken($type, $userId = null, $expiredTime = null, $data = null);

    /**
     * @deprecated move to TokenService
     */
    public function getToken($type, $token);

    /**
     * @deprecated move to TokenService
     */
    public function searchTokenCount($conditions);

    /**
     * @deprecated move to TokenService
     */
    public function deleteToken($type, $token);

    public function lockUser($id);
    
    public function unlockUser($id);


    public function promoteUser($id);

    public function cancelPromoteUser($id);

    public function findLatestPromotedTeacher($start, $limit);

    /**
     * 更新用户的计数器
     * 
     * @param  integer $number 用户ID
     * @param  string $name   计数器名称
     * @param  integer $number 计数器增减的数量
     */
    public function waveUserCounter($userId, $name, $number);

    /**
     * 清零用户的计数器
     * 
     * @param  integer $number 用户ID
     * @param  string $name   计数器名称
     */
    public function clearUserCounter($userId, $name);

    /**
     * 
     * 绑定第三方登录的帐号到系统中的用户帐号
     * 
     */
    public function bindUser($type, $fromId, $toId, $token);

    public function getUserBindByTypeAndFromId($type, $fromId);

    public function getUserBindByTypeAndUserId($type, $toId);

    public function findBindsByUserId($userId);
    
    public function unBindUserByTypeAndToId($type, $toId);

    /**
     * 用户之间相互关注
     */
    
    public function follow($fromId, $toId);

    public function unFollow($fromId, $toId);

    public function isFollowed($fromId, $toId);

    public function findUserFollowing($userId, $start, $limit);
    
    public function findAllUserFollowing($userId);

    public function findUserFollowingCount($userId);

    public function findUserFollowers($userId, $start, $limit);

    public function findUserFollowerCount($userId);
    
    public function findAllUserFollower($userId);

    /**
     * 过滤得到用户关注中的用户ID列表
     *
     * 此方法用于给出一批用户ID($followingIds)，找出哪些用户ID，是已经被用户($userId)关注了的。
     * 
     * @param  integer $userId       关注者的用户ID
     * @param  array   $followingIds 被关注者的用户ID列表
     * @return array 用户关注中的用户ID列表。
     */
    public function filterFollowingIds($userId, array $followingIds);

    public function getLastestApprovalByUserIdAndStatus($userId, $status);
    
    public function applyUserApproval($userId, $approval, $faceImg, $backImg, $directory);

    public function findUserApprovalsByUserIds($userIds);

    public function passApproval($userId, $note = null);

    public function rejectApproval($userId, $note = null);

    public function analysisRegisterDataByTime($startTime,$endTime);

    public function analysisUserSumByTime($endTime);

    public function findUsersCountByLessThanCreatedTime($endTime);

    public function dropFieldData($fieldName);

    public function userLoginFail($user,$failAllowNum = 3, $temporaryMinutes = 20);

    public function isUserTemporaryLockedOrLocked($user);

    public function clearUserConsecutivePasswordErrorTimesAndLockDeadline($userId);

    /**
     * 解析文本中@(提)到的用户
     */
    public function parseAts($text);

}