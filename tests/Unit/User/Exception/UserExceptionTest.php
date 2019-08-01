<?php

namespace Tests\Unit\User\Exception;

use Biz\BaseTestCase;
use Biz\User\UserException;

class UserExceptionTest extends BaseTestCase
{
    public function testUnLogin()
    {
        $exception = UserException::UN_LOGIN();

        $this->assertEquals('exception.user.unlogin', $exception->getMessage());
    }

    public function testLimitLogin()
    {
        $exception = UserException::LIMIT_LOGIN();

        $this->assertEquals('exception.user.login_limit', $exception->getMessage());
    }

    public function testForbiddenRegister()
    {
        $exception = UserException::FORBIDDEN_REGISTER();

        $this->assertEquals('exception.user.register_error', $exception->getMessage());
    }

    public function testNotFoundUser()
    {
        $exception = UserException::NOTFOUND_USER();

        $this->assertEquals('exception.user.not_found', $exception->getMessage());
    }

    public function testErrorResetPasswordEmail()
    {
        $exception = UserException::ERROR_RESET_PASSWORD_EMAIL();

        $this->assertEquals('exception.user.reset_password_email_send', $exception->getMessage());
    }

    public function testDiscuzUserResetPassword()
    {
        $exception = UserException::FORBIDDEN_DISCUZ_USER_RESET_PASSWORD();

        $this->assertEquals('exception.user.discuz_user_reset_password', $exception->getMessage());
    }

    public function testErrorMobileRegistered()
    {
        $exception = UserException::ERROR_MOBILE_REGISTERED();

        $this->assertEquals('exception.user.mobile_registered', $exception->getMessage());
    }

    public function testRegisterLimit()
    {
        $exception = UserException::FORBIDDEN_REGISTER_LIMIT();

        $this->assertEquals('exception.user.register_limit', $exception->getMessage());
    }

    public function testForbiddenSendMeassage()
    {
        $exception = UserException::FORBIDDEN_SEND_MESSAGE();

        $this->assertEquals('exception.user.message_forbidden', $exception->getMessage());
    }

    public function testErrorUpdateNickname()
    {
        $exception = UserException::UPDATE_NICKNAME_ERROR();

        $this->assertEquals('exception.user.update_nickname_error', $exception->getMessage());
    }

    public function testInvalidNickname()
    {
        $exception = UserException::NICKNAME_INVALID();

        $this->assertEquals('exception.user.nickname_invalid', $exception->getMessage());
    }

    public function testExistNickname()
    {
        $exception = UserException::NICKNAME_EXISTED();

        $this->assertEquals('exception.user.nickname_existed', $exception->getMessage());
    }

    public function testPermissionDenied()
    {
        $exception = UserException::PERMISSION_DENIED();

        $this->assertEquals('exception.user.permission_denied', $exception->getMessage());
    }

    public function testLockedUser()
    {
        $exception = UserException::LOCKED_USER();

        $this->assertEquals('exception.user.lock', $exception->getMessage());
    }

    public function testErrorPassword()
    {
        $exception = UserException::PASSWORD_ERROR();

        $this->assertEquals('exception.user.password_error', $exception->getMessage());
    }

    public function testNotfoundToken()
    {
        $exception = UserException::NOTFOUND_TOKEN();

        $this->assertEquals('exception.user.token_not_found', $exception->getMessage());
    }

    public function testNoUserProvider()
    {
        $exception = UserException::NO_USER_PROVIDER();

        $this->assertEquals('exception.user.no_user_provider', $exception->getMessage());
    }

    public function testInvalidEmail()
    {
        $exception = UserException::EMAIL_INVALID();

        $this->assertEquals('exception.user.email_invalid', $exception->getMessage());
    }

    public function testExistEmail()
    {
        $exception = UserException::EMAIL_EXISTED();

        $this->assertEquals('exception.user.email_existed', $exception->getMessage());
    }

    public function testInvalidMobile()
    {
        $exception = UserException::MOBILE_INVALID();

        $this->assertEquals('exception.user.mobile_invalid', $exception->getMessage());
    }

    public function testExistMobile()
    {
        $exception = UserException::MOBILE_EXISTED();

        $this->assertEquals('exception.user.mobile_existed', $exception->getMessage());
    }

    public function testInvalidPassword()
    {
        $exception = UserException::PASSWORD_INVALID();

        $this->assertEquals('exception.user.password_invalid', $exception->getMessage());
    }

    public function testInvalidMobileOrEmail()
    {
        $exception = UserException::MOBILE_OR_EMAIL_INVALID();

        $this->assertEquals('exception.user.mobile_or_email_invalid', $exception->getMessage());
    }

    public function testInvalidGender()
    {
        $exception = UserException::GENDER_INVALID();

        $this->assertEquals('exception.user.gender_invalid', $exception->getMessage());
    }

    public function testInvalidBirthday()
    {
        $exception = UserException::BIRTHDAY_INVALID();

        $this->assertEquals('exception.user.birthday_invalid', $exception->getMessage());
    }

    public function testInvalidQQ()
    {
        $exception = UserException::QQ_INVALID();

        $this->assertEquals('exception.user.qq_invalid', $exception->getMessage());
    }

    public function testInvalidSite()
    {
        $exception = UserException::SITE_INVALID();

        $this->assertEquals('exception.user.site_invalid', $exception->getMessage());
    }

    public function testInvalidWeibo()
    {
        $exception = UserException::WEIBO_INVALID();

        $this->assertEquals('exception.user.weibo_invalid', $exception->getMessage());
    }

    public function testInvalidBlog()
    {
        $exception = UserException::BLOG_INVALID();

        $this->assertEquals('exception.user.blog_invalid', $exception->getMessage());
    }

    public function testInvalidClientType()
    {
        $exception = UserException::CLIENT_TYPE_INVALID();

        $this->assertEquals('exception.user.client_type_invalid', $exception->getMessage());
    }

    public function testFollowSelf()
    {
        $exception = UserException::FOLLOW_SELF();

        $this->assertEquals('exception.user.follow_self', $exception->getMessage());
    }

    public function testFollowBlack()
    {
        $exception = UserException::FOLLOW_BLACK();

        $this->assertEquals('exception.user.follow_black', $exception->getMessage());
    }

    public function testDuplicateFollow()
    {
        $exception = UserException::DUPLICATE_FOLLOW();

        $this->assertEquals('exception.user.duplicate_follow', $exception->getMessage());
    }

    public function testInvalidRoles()
    {
        $exception = UserException::ROLES_INVALID();

        $this->assertEquals('exception.user.roles_invalid', $exception->getMessage());
    }

    public function testErrorUnfollow()
    {
        $exception = UserException::UNFOLLOW_ERROR();

        $this->assertEquals('exception.user.unfollow_error', $exception->getMessage());
    }

    public function testUserNotMatchAuth()
    {
        $exception = UserException::USER_NOT_MATCH_AUTH();

        $this->assertEquals('exception.user.not_match_auth', $exception->getMessage());
    }

    public function testInvalidIdcard()
    {
        $exception = UserException::IDCARD_INVALID();

        $this->assertEquals('exception.user.idcard_invalid', $exception->getMessage());
    }

    public function testInvalidTruename()
    {
        $exception = UserException::TRUENAME_INVALID();

        $this->assertEquals('exception.user.truename_invalid', $exception->getMessage());
    }

    public function testLockDenied()
    {
        $exception = UserException::LOCK_DENIED();

        $this->assertEquals('exception.user.lock_denied', $exception->getMessage());
    }

    public function testNotMatchBindEmail()
    {
        $exception = UserException::NOT_MATCH_BIND_EMAIL();

        $this->assertEquals('exception.user.not_match_bind_email', $exception->getMessage());
    }

    public function testLockSeftDenied()
    {
        $exception = UserException::LOCK_SELF_DENIED();

        $this->assertEquals('exception.user.lock_self_denied', $exception->getMessage());
    }

    public function testInvalidDatefield()
    {
        $exception = UserException::DATEFIELD_INVALID();

        $this->assertEquals('exception.user.datefield_invalid', $exception->getMessage());
    }

    public function testInvalidWeixin()
    {
        $exception = UserException::WEIXIN_INVALID();

        $this->assertEquals('exception.user.weixin_invalid', $exception->getMessage());
    }
}
