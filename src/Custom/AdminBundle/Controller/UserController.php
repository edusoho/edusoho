<?php
namespace Custom\AdminBundle\Controller;
use Topxia\WebBundle\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;


class UserController extends BaseController 
{

    public function indexAction (Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'roles'=>'',
            'keywordType'=>'',
            'keyword'=>''
        );

        if(empty($fields)){
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $app = $this->getAppService()->findInstallApp("UserImporter");
        $enabled = $this->getSettingService()->get('plugin_userImporter_enabled');
        
        $showUserExport = false;
        if(!empty($app) && array_key_exists('version', $app) && $enabled){
            $showUserExport = version_compare($app['version'], "1.0.2", ">=");
        }

        return $this->render('CustomAdminBundle:User:index.html.twig', array(
            'users' => $users ,
            'paginator' => $paginator,
            'showUserExport' => $showUserExport
        ));
    }

  
    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}