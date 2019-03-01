<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class UserException extends AbstractException
{
    const EXCEPTION_MODUAL = 01;

    const UN_LOGIN = 4040101;

    const LIMIT_LOGIN = 4030102;

    const FORBIDDEN_REGISTER = 4030103;

    const NOTFOUND_USER = 4040104;

    const ERROR_RESET_PASSWORD_EMAIL = 5000105;

    const FORBIDDEN_DISCUZ_USER_RESET_PASSWORD = 4030106;

    const ERROR_MOBILE_REGISTERED = 4030107;

    const FORBIDDEN_REGISTER_LIMIT = 4030108;

    const FORBIDDEN_SEND_MESSAGE = 4030110;

    const UPDATE_NICKNAME_ERROR = 5000111;

    const NICKNAME_INVALID = 5000112;

    const NICKNAME_EXISTED = 5000113;

    const PERMISSION_DENIED = 4030114;

    const LOCKED_USER = 4030115;

    const PASSWORD_ERROR = 5000116;

    const NOTFOUND_TOKEN = 4040117;

    const NO_USER_PROVIDER = 4040118;

    const EMAIL_INVALID = 5000119;

    const EMAIL_EXISTED = 5000120;

    const MOBILE_INVALID = 5000121;

    const MOBILE_EXISTED = 5000122;

    const PASSWORD_INVALID = 5000123;

    const MOBILE_OR_EMAIL_INVALID = 5000124;

    const GENDER_INVALID = 5000125;

    const BIRTHDAY_INVALID = 5000126;

    const QQ_INVALID = 5000127;

    const SITE_INVALID = 5000128;

    const WEIBO_INVALID = 5000129;

    const BLOG_INVALID = 5000130;

    const CLIENT_TYPE_INVALID = 5000131;

    const FOLLOW_SELF = 5000132;

    const FOLLOW_BLACK = 5000133;

    const DUPLICATE_FOLLOW = 5000134;

    const ROLES_INVALID = 5000135;

    const UNFOLLOW_ERROR = 5000136;

    const USER_NOT_MATCH_AUTH = 5000137;

    const IDCARD_INVALID = 5000138;

    const TRUENAME_INVALID = 5000139;

    const LOCK_DENIED = 4030140;

    const NOT_MATCH_BIND_EMAIL = 5000141;

    const LOCK_SELF_DENIED = 4030142;

    const DATEFIELD_INVALID = 5000143;

    const WEIXIN_INVALID = 5000144;

    public $messages = array(
        4040101 => 'exception.user.unlogin',
        4030102 => 'exception.user.login_limit',
        4030103 => 'exception.user.register_error',
        4040104 => 'exception.user.not_found',
        5000105 => 'exception.user.reset_password_email_send',
        4030106 => 'exception.user.discuz_user_reset_password',
        4030107 => 'exception.user.mobile_registered',
        4030108 => 'exception.user.register_limit',
        4030110 => 'exception.user.message_forbidden',
        5000111 => 'exception.user.update_nickname_error',
        5000112 => 'exception.user.nickname_invalid',
        5000113 => 'exception.user.nickname_existed',
        4030114 => 'exception.user.permission_denied',
        4030115 => 'exception.user.lock',
        5000116 => 'exception.user.password_error',
        4040117 => 'exception.user.token_not_found',
        4040118 => 'exception.user.no_user_provider',
        5000119 => 'exception.user.email_invalid',
        5000120 => 'exception.user.email_existed',
        5000121 => 'exception.user.mobile_invalid',
        5000122 => 'exception.user.mobile_existed',
        5000123 => 'exception.user.password_invalid',
        5000124 => 'exception.user.mobile_or_email_invalid',
        5000125 => 'exception.user.gender_invalid',
        5000126 => 'exception.user.birthday_invalid',
        5000127 => 'exception.user.qq_invalid',
        5000128 => 'exception.user.site_invalid',
        5000129 => 'exception.user.weibo_invalid',
        5000130 => 'exception.user.blog_invalid',
        5000131 => 'exception.user.client_type_invalid',
        5000132 => 'exception.user.follow_self',
        5000133 => 'exception.user.follow_black',
        5000134 => 'exception.user.duplicate_follow',
        5000135 => 'exception.user.roles_invalid',
        5000136 => 'exception.user.unfollow_error',
        5000137 => 'exception.user.not_match_auth',
        5000138 => 'exception.user.idcard_invalid',
        5000139 => 'exception.user.truename_invalid',
        4030140 => 'exception.user.lock_denied',
        5000141 => 'exception.user.not_match_bind_email',
        4030142 => 'exception.user.lock_self_denied',
        5000143 => 'exception.user.datefield_invalid',
        5000144 => 'exception.user.weixin_invalid',
    );
}
