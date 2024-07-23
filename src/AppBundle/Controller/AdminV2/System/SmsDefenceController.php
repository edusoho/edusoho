<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\SmsDefence\Service\SmsDefenceService;
use Symfony\Component\HttpFoundation\Request;

class SmsDefenceController extends BaseController
{
    public function smsBlackListAction(Request $request)
    {
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getSmsDefenceService()->countSmsBlackIpList($conditions),
            30
        );
        $smsBlackIpList = $this->getSmsDefenceService()->searchSmsBlackIpList(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render("admin-v2/system/sms-defence/sms-black-ip/sms-black-ip-show.html.twig", ['smsBlackIpList' => $smsBlackIpList, 'paginator' => $paginator]);
    }

    public function smsRequestLogListAction(Request $request)
    {
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getSmsDefenceService()->countSmsRequestLog($conditions),
            30
        );
        $smsRequestLogs = $this->getSmsDefenceService()->searchSmsRequestLog(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render("admin-v2/system/sms-defence/sms-request-log/sms-request-log-show.html.twig", ['smsRequestLogs' => $smsRequestLogs, 'paginator' => $paginator]);
    }

    public function smsRequestLogDetailAction(Request $request, $id)
    {
        $smsRequestLog = $this->getSmsDefenceService()->getSmsRequestLog($id);

        return $this->render("admin-v2/system/sms-defence/sms-request-log/show-modal.html.twig",['smsRequestLog' => $smsRequestLog]);
    }

    public function unlockAction(Request $request, $id)
    {
        $this->getSmsDefenceService()->unLockBlackIp($id);

        return $this->createJsonResponse(true);
    }

    /**
     * @return SmsDefenceService
     */
    protected function getSmsDefenceService()
    {
        return $this->createService('SmsDefence:SmsDefenceService');
    }
}