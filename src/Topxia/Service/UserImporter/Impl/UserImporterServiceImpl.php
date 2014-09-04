<?php
namespace Topxia\Service\UserImporter\Impl;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Topxia\Service\Common\BaseService;
use Topxia\Service\UserImporter\UserImporterService;

class UserImporterServiceImpl extends BaseService implements UserImporterService
{
    public function importUsers(array $users)
    {

        $this->getUserDao()->getConnection()->beginTransaction();
        try{

            for($i=0;$i<count($users);$i++){

                $userInfo = array();
                if ($users[$i]["gender"]=="男")$users[$i]["gender"]="male";
                if ($users[$i]["gender"]=="女")$users[$i]["gender"]="female";
                if ($users[$i]["gender"]=="")$users[$i]["gender"]="secret";

                $userInfo['email'] = $users[$i]['email'];
                $userInfo['truename'] = $users[$i]['truename'];
                $userInfo['nickname'] = $users[$i]['number'];
                $userInfo['number'] = $users[$i]['number'];
                $userInfo["roles"]=array('ROLE_USER');
                $userInfo['type'] = "default";
                $userInfo['createdIp'] = "";
                $userInfo['createdTime'] = time();
                $userInfo['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
                $userInfo['password'] = $this->getPasswordEncoder()->encodePassword($users[$i]['password'], $userInfo['salt']);
                $userInfo['setup'] = 1;

                $user = UserSerialize::unserialize(
                    $this->getUserDao()->addUser(UserSerialize::serialize($userInfo))
                );

                $profile = array();
                $profile['id'] = $user['id'];
                $profile['mobile'] = empty($users[$i]['mobile']) ? '' : $users[$i]['mobile'];
                $profile['idcard'] = empty($users[$i]['idcard']) ? '' : $users[$i]['idcard'];
                $profile['company'] = empty($users[$i]['company']) ? '' : $users[$i]['company'];
                $profile['job'] = empty($users[$i]['job']) ? '' : $users[$i]['job'];
                $profile['weixin'] = empty($users[$i]['weixin']) ? '' : $users[$i]['weixin'];
                $profile['weibo'] = empty($users[$i]['weibo']) ? '' : $users[$i]['weibo'];
                $profile['qq'] = empty($users[$i]['qq']) ? '' : $users[$i]['qq'];
                $profile['site'] = empty($users[$i]['site']) ? '' : $users[$i]['site'];
                $profile['gender'] = empty($users[$i]['gender']) ? 'secret' : $users[$i]['gender'];
                for($j=1;$j<=5;$j++){
                    $profile['intField'.$j] = empty($users[$i]['intField'.$j]) ? null : $users[$i]['intField'.$j];
                    $profile['dateField'.$j] = empty($users[$i]['dateField'.$j]) ? null : $users[$i]['dateField'.$j];
                    $profile['floatField'.$j] = empty($users[$i]['floatField'.$j]) ? null : $users[$i]['floatField'.$j];
                }
                for($j=1;$j<=10;$j++){
                    $profile['varcharField'.$j] = empty($users[$i]['varcharField'.$j]) ? "" : $users[$i]['varcharField'.$j];
                    $profile['textField'.$j] = empty($users[$i]['textField'.$j]) ? "" : $users[$i]['textField'.$j];
                }

                $this->getProfileDao()->addProfile($profile);
            
            }

             $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }

    }

    public function importUpdateNumber(array $users)
    {

        $this->getUserDao()->getConnection()->beginTransaction();
        try{

            for($i=0;$i<count($users);$i++){
                $member = $this->getUserDao()->getUserByNumber($users[$i]["number"]);
                $member=UserSerialize::unserialize($member);
                $this->getUserService()->changePassword($member["id"],$users[$i]["password"]);
                $this->getUserService()->updateUserProfile($member["id"],$users[$i]);              
            }

             $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }

    }

    public function importUpdateEmail(array $users)
    {

        $this->getUserDao()->getConnection()->beginTransaction();
        try{

            for($i=0;$i<count($users);$i++){
                $member = $this->getUserDao()->findUserByEmail($users[$i]["email"]);
                $member=UserSerialize::unserialize($member);
                $this->getUserService()->changePassword($member["id"],$users[$i]["password"]);
                $this->getUserService()->updateUserProfile($member["id"],$users[$i]);              
            }

            $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }

    }

    private function getUserDao()
    {
        return $this->createDao('User.UserDao');
    }

    private function getProfileDao()
    {
        return $this->createDao('User.UserProfileDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
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