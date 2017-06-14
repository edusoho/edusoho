<?php

namespace Topxia\MobileBundleV2\Processor;

interface UserProcessor
{
    public function getVersion();

    public function login();

    public function regist();

    public function loginWithToken();

    public function getUserInfo();

    public function logout();

    public function getUserNotification();

    public function getUserMessages();

    public function getMessageList();

    public function sendMessage();

    public function getUserCoin();

    public function getFollowings();

    public function getFollowers();

    public function follow();

    public function unfollow();

    public function searchUserIsFollowed();

    public function getConversationIdByFromIdAndToId();

    /**
     *获取用户个人主页的问答、讨论、笔记、考试的总数.
     */
    public function getUserNum();

    public function smsSend();

    public function getCourseTeachers();

    public function updateUserProfile();

    public function uploadAvatar();
}
