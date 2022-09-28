<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ConvertIpToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\LogService;
use Symfony\Component\HttpFoundation\Request;

class StaffLoginRecordController extends LoginRecordCommonController
{
    public function indexAction(Request $request)
    {
        $indexTwigUrl = 'admin-v2/user/login-record/staff-login-record/index.html.twig';
        $isStudent = 1;

        return $this->index($request, $isStudent, $indexTwigUrl);
    }

    public function showUserLoginRecordAction(Request $request, $id)
    {
        $showUserLoginRecordTwigUrl = 'admin-v2/user/login-record/login-record-details.html.twig';

        return $this->showUserLoginRecord($request, $id, $showUserLoginRecordTwigUrl);
    }
}
