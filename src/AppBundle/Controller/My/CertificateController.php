<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Certificate\Service\RecordService;
use Symfony\Component\HttpFoundation\Request;

class CertificateController extends BaseController
{
    public function indexAction(Request $request)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        $conditions = $this->getConditions($request);

        $paginator = new Paginator(
            $request,
            $this->getCertificateRecordService()->count($conditions),
            10
        );

        $certificateRecords = $this->getCertificateRecordService()->search(
            $conditions,
            ['issueTime' => 'DESC', 'createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('certificate/my/index.html.twig', [
            'paginator' => $paginator,
            'certificates' => $this->getCertificateService()->findByIds(ArrayToolkit::column($certificateRecords, 'certificateId')),
            'certificateRecordGroups' => $this->wrapperCertificateRecords($certificateRecords),
            'startdate' => $request->query->get('startdate', date('Y/01/01')),
            'enddate' => $request->query->get('enddate', date('Y/m/d')),
        ]);
    }

    public function unclaimedAction(Request $request)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        $paginator = new Paginator(
            $request,
            100,
            15
        );

        return $this->render('certificate/my/unclaimed.html.twig', [
            'paginator' => $paginator,
        ]);
    }

    protected function wrapperCertificateRecords(array $certificateRecords)
    {
        $wrapperCertificateRecords = [];
        foreach ($certificateRecords as $certificateRecord) {
            $issueYear = date('Y', $certificateRecord['issueTime']);
            if (!isset($wrapperCertificateRecords[$issueYear])) {
                $wrapperCertificateRecords[$issueYear] = ['issueYear' => $issueYear, 'certificateRecords' => []];
            }
            $wrapperCertificateRecords[$issueYear]['certificateRecords'][] = $certificateRecord;
        }

        return array_values($wrapperCertificateRecords);
    }

    protected function getConditions($request)
    {
        $startdate = $request->query->get('startdate', date('Y/01/01'));
        $enddate = $request->query->get('enddate', date('Y/m/d'));

        $conditions = [
            'userId' => $this->getCurrentUser()['id'],
            'statusIn' => ['valid', 'expired'],
            'issueTimeEgt' => strtotime($startdate),
            'issueTimeElt' => strtotime($enddate) + 86400 - 1,
        ];

        if ('1' === $request->query->get('valid')) {
            $conditions['statusIn'] = ['valid'];
        }

        if ($request->query->get('q')) {
            $certificates = $this->getCertificateService()->search(['nameLike' => $request->query->get('q')], [], 0, PHP_INT_MAX, ['id']);
            $conditions['certificateIds'] = empty($certificates) ? [-1] : ArrayToolkit::column($certificates, 'id');
        }

        return $conditions;
    }

    /**
     * @return RecordService
     */
    public function getCertificateService()
    {
        return $this->getBiz()->service('Certificate:CertificateService');
    }

    /**
     * @return RecordService
     */
    public function getCertificateRecordService()
    {
        return $this->getBiz()->service('Certificate:RecordService');
    }
}
