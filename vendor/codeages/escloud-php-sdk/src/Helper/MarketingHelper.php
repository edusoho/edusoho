<?php

namespace ESCloud\SDK\Helper;

class MarketingHelper
{
    public static function generateLoginForm($action, $user, $site, $sign)
    {
        $loginTitle = "<div align='center'> 正在登陆中......</div>";
        $submitJs = "<script type='text/javascript'>
                        window.onload = function(){
                            document.getElementById('login-form').submit()
                        };
                    </script>";
        $form = "<form class='form-horizontal' style='display:none;' id='login-form' method='post' action='{$action}'>
                    <input type='hidden' name='site[name]' class='form-control' value=\"{$site['name']}\">
                    <input type='hidden' name='site[logo]' class='form-control' value=\"{$site['logo']}\">
                    <input type='hidden' name='site[about]' class='form-control' value=\"{$site['about']}\">
                    <input type='hidden' name='site[wechat]' class='form-control' value=\"{$site['wechat']}\">
                    <input type='hidden' name='site[qq]' class='form-control' value=\"{$site['qq']}\">
                    <input type='hidden' name='site[telephone]' class='form-control' value=\"{$site['telephone']}\">
                    <input type='hidden' name='site[domain]' class='form-control' value=\"{$site['domain']}\">
                    <input type='hidden' name='user[user_source_id]' class='form-control' value=\"{$user['user_source_id']}\">
                    <input type='hidden' name='user[nickname]' class='form-control' value=\"{$user['nickname']}\">
                    <input type='hidden' name='user[avatar]' class='form-control' value=\"{$user['avatar']}\">
                    <input type='hidden' name='sign' class='form-control' value=\"{$sign}\">
                    <button type='submit' class='btn btn-primary'>提交</button>
                </form>";

        return $loginTitle.$form.$submitJs;
    }
}
