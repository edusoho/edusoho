<?php

namespace Biz\User\Service;

use Biz\System\Annotation\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UserService
{
    public function getUser($id, $lock = false);

    public function getUserAndProfile($id);

    public function initSystemUsers();

    public function getSimpleUser($id);

    public function getUserByNickname($nickname);

    public function getUserByType($type);

    public function getUserByUUID($uuid);

    public function updateUserUpdatedTime($id);

    //根据用户名/邮箱/手机号精确查找用户
    public function getUserByLoginField($keyword);

    public function getUserByVerifiedMobile($mobile);

    public function countUsersByMobileNotEmpty();

    public function countUserHasMobile($isVerified = false);

    public function findUsersHasMobile($start, $limit, $isVerified = false);

    public function findUnlockedUserMobilesByUserIds($userIds);

    public function getUserByEmail($email);

    public function findUsersByIds(array $id);

    public function findUserProfilesByIds(array $ids);

    public function searchUsers(array $conditions, array $orderBy, $start, $limit, $columns = array());

    public function countUsers(array $conditions);

    public function setEmailVerified($userId);

    public function changeUserOrg($userId, $orgCode);

    /**
     * [batchUpdateOrg 对单个或者多个用户更改组织机构].
     *
     * @param [String  |        Arrary] $userIds [用户Id]
     * @param [String]                  $orgCode [组织机构内部编码]
     */
    public function batchUpdateOrg($userIds, $orgCode);

    /**
     * @param $userId
     * @param $nickname
     *
     * @return mixed
     * @Log(module="user",action="nickname_change",funcName="getUser",param="userId")
     */
    public function changeNickname($userId, $nickname);

    /**
     * @param $userId
     * @param $email
     *
     * @return mixed
     * @Log(module="user",action="email-changed",funcName="getUser",param="userId")
     */
    public function changeEmail($userId, $email);

    /**
     * @param $userId
     * @param $data
     *
     * @return mixed
     * @Log(module="user",action="avatar-changed",funcName="getUser",param="userId")
     */
    public function changeAvatar($userId, $data);

    public function isNicknameAvaliable($nickname);

    public function isEmailAvaliable($email);

    public function isMobileAvaliable($mobile);

    public function hasAdminRoles($userId);

    public function rememberLoginSessionId($id, $sessionId);

    /**
     * @param $userId
     * @param $newPayPassword
     *
     * @return mixed
     * @Log(module="user",action="pay-password-changed",funcName="getUser",param="userId")
     */
    public function changePayPassword($userId, $newPayPassword);

    public function verifyPayPassword($id, $payPassword);

    public function verifyInSaltOut($in, $salt, $out);

    public function isMobileUnique($mobile);

    /**
     * @param $id
     * @param $mobile
     *
     * @return mixed
     * @Log(module="user",action="verifiedMobile-changed",funcName="getUser",param="id")
     */
    public function changeMobile($id, $mobile);

    /**
     * 变更密码
     *
     * @param [integer] $id       用户ID
     * @param [string]  $password 新密码
     * @Log(module="user",action="password-changed",funcName="getUser",param="id")
     */
    public function changePassword($id, $password);

    /**
     * 变更原始密码
     *
     * @param [integer] $id          用户ID
     * @param [string]  $rawPassword 新原始密码
     * @Log(module="user",action="raw-password-changed",funcName="getUser",param="id")
     */
    public function changeRawPassword($id, $rawPassword);

    /**
     * 校验密码是否正确.
     *
     * @param [integer] $id       用户ID
     * @param [string]  $password 密码
     *
     * @return [boolean] 密码正确，返回true；错误，返回false
     */
    public function verifyPassword($id, $password);

    /**
     * 用户注册.
     *
     * 当type为default时，表示用户从自身网站注册。
     * 当type为weibo、qq、renren时，表示用户从第三方网站连接，允许注册信息没有密码。
     *
     * @param [type] $registration 用户注册信息
     * @param string $type         注册类型
     *
     * @return array 用户信息
     */
    public function register($registration, $type = 'default');

    public function markLoginInfo($type = null);

    public function markLoginFailed($userId, $ip);

    public function refreshLoginSecurityFields($userId, $ip);

    public function checkLoginForbidden($userId, $ip);

    /**
     * @param $id
     * @param $fields
     *
     * @return mixed
     * @Log(module="user",action="update",funcName="getUserAndProfile",param="id")
     */
    public function updateUserProfile($id, $fields);

    public function getUserProfile($id);

    public function searchUserProfiles(array $conditions, array $orderBy, $start, $limit, $columns = array());

    public function searchUserProfileCount(array $conditions);

    public function searchApprovals(array $conditions, array $orderBy, $start, $limit);

    public function searchApprovalsCount(array $conditions);

    /**
     * @param $id
     * @param array $roles
     *
     * @return mixed
     * @Log(module="user",action="change_role",funcName="getUser",param="id")
     */
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

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="user",action="lock",funcName="getUser",param="id")
     */
    public function lockUser($id);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="user",action="unlock",funcName="getUser",param="id")
     */
    public function unlockUser($id);

    public function promoteUser($id, $number);

    public function cancelPromoteUser($id);

    public function findLatestPromotedTeacher($start, $limit);

    /**
     * 更新用户的计数器.
     *
     * @param int    $number 用户ID
     * @param string $name   计数器名称
     * @param int    $number 计数器增减的数量
     */
    public function waveUserCounter($userId, $name, $number);

    /**
     * 清零用户的计数器.
     *
     * @param int    $number 用户ID
     * @param string $name   计数器名称
     */
    public function clearUserCounter($userId, $name);

    /**
     * 绑定第三方登录的帐号到系统中的用户帐号.
     */
    public function bindUser($type, $fromId, $toId, $token);

    public function getUserBindByTypeAndFromId($type, $fromId);

    public function getUserBindByTypeAndUserId($type, $toId);

    public function findUserBindByTypeAndFromIds($type, $fromIds);

    public function findUserBindByTypeAndToIds($type, $toIds);

    public function findUserBindByTypeAndUserId($type, $toId);

    public function getUserBindByToken($token);

    public function findBindsByUserId($userId);

    public function unBindUserByTypeAndToId($type, $toId);

    /**
     * 用户之间相互关注.
     */
    public function follow($fromId, $toId);

    public function unFollow($fromId, $toId);

    public function isFollowed($fromId, $toId);

    public function findUserFollowing($userId, $start, $limit);

    public function findAllUserFollowing($userId);

    public function findUserFollowingCount($userId);

    public function findUserFollowers($userId, $start, $limit);

    public function findUserFollowerCount($userId);

    //当前用户关注的人们
    public function findAllUserFollower($userId);

    public function findFriends($userId, $start, $limit);

    public function findFriendCount($userId);

    /**
     * 过滤得到用户关注中的用户ID列表.
     *
     * 此方法用于给出一批用户ID($followingIds)，找出哪些用户ID，是已经被用户($userId)关注了的。
     *
     * @param int   $userId       关注者的用户ID
     * @param array $followingIds 被关注者的用户ID列表
     *
     * @return array 用户关注中的用户ID列表
     */
    public function filterFollowingIds($userId, array $followingIds);

    public function getLastestApprovalByUserIdAndStatus($userId, $status);

    public function applyUserApproval($userId, $approval, UploadedFile $faceImg, UploadedFile $backImg, $directory);

    public function findUserApprovalsByUserIds($userIds);

    public function passApproval($userId, $note = null);

    public function rejectApproval($userId, $note = null);

    public function analysisRegisterDataByTime($startTime, $endTime);

    public function countUsersByLessThanCreatedTime($endTime);

    public function dropFieldData($fieldName);

    /**
     * 解析文本中@(提)到的用户.
     */
    public function parseAts($text);

    /**
     *  邀请码相关.
     */
    public function getUserByInviteCode($inviteCode);

    public function findUserIdsByInviteCode($inviteCode);

    public function createInviteCode($userId);

    /**
     * 用户授权.
     */
    public function getUserPayAgreement($id);

    public function getUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth);

    public function getUserPayAgreementByUserId($userId);

    public function createUserPayAgreement($field);

    public function updateUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth, $fields);

    public function findUserPayAgreementsByUserId($userId);

    public function deleteUserPayAgreements($id);

    public function updateUserLocale($id, $locale);

    public function changeAvatarFromImgUrl($userId, $imgUrl);

    public function changeAvatarByFileId($userId, $fileId);

    public function generateNickname($registration, $maxLoop = 100);

    public function getUserIdsByKeyword($word);

    public function updateUserNewMessageNum($id, $num);

    public function makeUUID();

    public function generateUUID();

    public function getSmsCommonCaptchaStatus($clientIp, $recount = false);

    public function getSmsRegisterCaptchaStatus($clientIp, $updateCount = false);

    public function updateSmsRegisterCaptchaStatus($clientIp);

    /**
     * 用户首次登录修改密码.
     */
    public function initPassword($id, $newPassword);

    /**
     * 人脸识别采集状态修改
     */
    public function setFaceRegistered($id);
}
