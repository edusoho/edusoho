<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Symfony\Component\HttpFoundation\Request;

class CertificateRecordController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $certificate = $this->getCertificateService()->get($id);
        if (empty($certificate)) {
            $this->createNewException(CertificateException::NOTFOUND_CERTIFICATE());
        }

        $conditions = $request->query->all();
        $conditions['certificateId'] = $id;
        $conditions = $this->prepareSearchConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getRecordService()->count($conditions),
            20
        );

        $records = $this->getRecordService()->search(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($records, 'userId'));
        $strategy = $this->getCertificateStrategy($certificate['targetType']);

        return $this->render('admin-v2/operating/certificate-record/index.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
            'users' => ArrayToolkit::index($users, 'id'),
            'targets' => $strategy->findTargetsByIds(ArrayToolkit::column($records, 'targetId')),
            'certificate' => $certificate,
        ]);
    }

    public function detailAction(Request $request, $id)
    {
        $record = $this->getRecordService()->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD());
        }

        $strategy = $this->getCertificateStrategy($record['targetType']);

        return $this->render('admin-v2/operating/certificate-record/detail-modal.html.twig', [
            'record' => $record,
            'user' => $this->getUserService()->getUserAndProfile($record['userId']),
            'target' => $strategy->getTarget($record['targetId']),
        ]);
    }

    public function cancelAction(Request $request, $id)
    {
        $record = $this->getRecordService()->cancelRecord($id);

        return $this->createJsonResponse(true);
    }

    public function grantAction(Request $request, $id)
    {
        $record = $this->getRecordService()->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD());
        }

        if ('cancelled' != $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_CANCEL_RECORD());
        }

        $strategy = $this->getCertificateStrategy($record['targetType']);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $data['issueTime'] = strtotime($data['issueTime']);
            $this->getRecordService()->grantRecord($id, $data);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/operating/certificate-record/grant-modal.html.twig', [
            'record' => $record,
            'user' => $this->getUserService()->getUserAndProfile($record['userId']),
            'target' => $strategy->getTarget($record['targetId']),
        ]);
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['status'])) {
            if ('all' == $conditions['status']) {
                unset($conditions['status']);
            } elseif ('valid' == $conditions['status']) {
                $conditions['excludeIds'] = $this->getExpiredRecordIds($conditions['certificateId']);
            } elseif ('expired' == $conditions['status']) {
                $conditions['ids'] = $this->getExpiredRecordIds($conditions['certificateId']) ?: [-1];
                unset($conditions['status']);
            }
        }
        if (!empty($conditions['keywordType']) && !empty($conditions['keyword'])) {
            if ('certificateCode' == $conditions['keywordType']) {
                $conditions['certificateCode'] = $conditions['keyword'];
            }
            if (in_array($conditions['keywordType'], ['nickname', 'verifiedMobile', 'email'])) {
                $users = $this->getUserService()->searchUsers([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
                $conditions['userIds'] = $users ? ArrayToolkit::column($users, 'id') : [-1];
            }
            if ('truename' == $conditions['keywordType']) {
                $users = $this->getUserService()->searchUserProfiles([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
                $conditions['userIds'] = $users ? ArrayToolkit::column($users, 'id') : [-1];
            }
            if ('batch' == $conditions['keywordType']) {
                $certificate = $this->getCertificateService()->get($conditions['certificateId']);
                $resource = $this->getCertificateStrategy($certificate['targetType'])->findTargetsByTargetTitle($conditions['keyword']);
                $conditions['targetIds'] = $resource ? ArrayToolkit::column($resource, 'id') : [-1];
            }
        }
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        $conditions['statusNotEqual'] = 'none';

        return $conditions;
    }

    protected function getExpiredRecordIds($certificateId)
    {
        $expiredRecords = $this->getRecordService()->findExpiredRecords($certificateId);

        return array_column($expiredRecords, 'id');
    }

    protected function getCertificateStrategy($type)
    {
        return $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($type);
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return $this->createService('Certificate:RecordService');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }
}
