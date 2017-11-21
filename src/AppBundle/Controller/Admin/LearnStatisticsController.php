<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\Org\Service\OrgService;
use Biz\Role\Service\RoleService;
use Biz\System\Service\LogService;
use Biz\System\Service\SessionService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserFieldService;
use Symfony\Component\HttpFoundation\Request;

class LearnStatisticsController extends BaseController
{
    public function showAction(Request $request)
    {
       return $this->render('admin/learn-statistices/show.html.twig');
    }  
}