<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class InstallController extends BaseController
{

    public function createDataAction(Request $request)
    {

        if ($request->getMethod() == 'POST') {

            $formData = $request->request->all();

            $registration = array();
            $registration['email'] = $formData['super_manager_email'];
            $registration['password'] = $registration['confirmPassword'] = $formData['super_manager_pd'] ;
            $registration['nickname'] = $formData['super_manager'] ;
            $auth = $this->getSettingService()->get('auth', array());
            $user = $this->getUserService()->register($registration);
            $this->authenticateUser($user);
            $this->get('session')->set('registed_email', $user['email']);

            $this->getUserService()->changeUserRoles($user['id'], array('ROLE_SUPER_ADMIN', 'ROLE_TEACHER'));
            $this->sendWelcomeMessage($user);

            $parameters = array(
                'parameters' => array(
                    'database_driver' => 'pdo_mysql', 
                    'database_host' => $formData['dbhost'],
                    'database_port' => null,
                    'database_name' => $formData['dbname'],
                    'database_user' => $formData['dbuser'],
                    'database_password' => $formData['dbpw'],
                    'mailer_transport' => 'smtp',
                    'mailer_host' => '127.0.0.1',
                    'mailer_user' => $formData['super_manager'],
                    'mailer_password' => $formData['super_manager_pd'],
                    'locale' => 'en',
                    'secret' => 'ThisTokenIsNotSoSecretChangeIt')
            );
            $dumper = new Dumper();
            $yaml = $dumper->dump($parameters, 2);
            file_put_contents('/var/www/edusoho/app/config/parameters.yml', $yaml);

            var_dump($formData);
            exit();
  
        }
        
        return $this->render("TopxiaWebBundle:Install:create-super-admin.html.twig"); 
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}

