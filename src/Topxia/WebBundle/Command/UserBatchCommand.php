<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\NickName;

class UserBatchCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:user-batch' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $nna = $this->getNickNameArray();

        foreach ($nna as $nickname) {

            try{

                if($this->getUserService()->getUserByNickname($nickname)){
                    continue;
                }

                $mail = $this->generateChars();  

                $userData['email'] = $mail."@osforce.org";
                $userData['nickname'] = $nickname;
                $userData['password'] =  $mail."123";
                $userData['createdIp'] = '127.0.0.1';
               
                $user = $this->getAuthService()->register($userData);

                $this->getLogService()->info('user', 'add', "管理员添加新用户 {$user['nickname']} ({$user['id']})");
            

            }catch(\Exception $e){
                
            }

           
            
        }
      

    }

  
    private function getNickNameArray( ) {
        
       $nna = NickName::getNickNameArray();
       return $nna;

    }

    private function generateChars( $length = 7 ) {  
        // 密码字符集，可任意添加你需要的字符  
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password ="";  
        for ( $i = 0; $i < $length; $i++ )  
        {
        // 这里提供两种字符获取方式  
        // 第一种是使用 substr 截取$chars中的任意一位字符；  
        // 第二种是取字符数组 $chars 的任意元素  
        // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);  
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
        }  
        return $password;  
    }   

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

     protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' =>  '127.0.0.1',
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }




}