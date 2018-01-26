<?php

/**
 * 如果 nickname = 'discuz-nickname', password = 'discuz-password', email = 'discuz-email'， 返回0
 * 否则 返回 nickname中指定的错误码，nickname格式为 'discuz-nickname(-{$number})'  {$number}为具体数字
 */
function uc_user_register($nickname, $password, $email)
{
    if ('discuz-nickname' == $nickname && 'discuz-password' == $password && $email = 'discuz-email') {
        return 0;
    } else {
        return explode(')', explode('discuz-nickname(', $nickname)[1])[0];
    }

    throw new \RuntimeException('MockedDiscuzClient parameters error');
}

function uc_user_synlogin($userId)
{
    return 333 == $userId;
}

function uc_user_synlogout()
{
    return true;
}

function uc_user_renameuser($userId, $nickname)
{
    return 333 == $userId && 'newNickname' == $nickname;
}

function uc_user_checkname($newUsername)
{
    if ('newUsername' == $newUsername) {
        return 1;
    }

    return 0;
}

function uc_user_checkemail($newEmail)
{
    if ('newEmail' == $newEmail) {
        return 1;
    }

    return 0;
}

function uc_get_user($userId, $param1)
{
    if (333 == $userId && 1 == $param1) {
        return array(1 => 123);
    }
    throw new \RuntimeException('error');
}

function uc_user_edit($userId, $param1, $newPassword, $newEmail, $param3)
{
    if (333 == $userId && 'newEmail@howzhi.com' == $newEmail) {
        return 1;
    } elseif (123 == $userId && 'newPassword' == $newPassword) {
        return 1;
    }

    return 0;
}
