<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class CertificateAuditController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $certificate = $this->getCertificateService()->get($id);
        if (empty($certificate)) {
            $this->createNewException(CertificateException::NOTFOUND_CERTIFICATE());
        }

        $conditions = $this->searchConditions($request);
        $conditions['certificateId'] = $id;

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

        $userIds = $records ? ArrayToolkit::column($records, 'userId') : [-1];
        $users = $this->getUserService()->findUsersByIds($userIds);
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        foreach ($userProfiles as $key => $userProfile) {
            $users[$key]['truename'] = $userProfile['truename'];
        }

        $reviewers = $this->getUserService()->findUsersByIds($records ? ArrayToolkit::column($records, 'auditUserId') : [-1]);

        return $this->render('admin-v2/operating/certificate-audit/index.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
            'users' => ArrayToolkit::index($users, 'id'),
            'reviewers' => ArrayToolkit::index($reviewers, 'id'),
            'targets' => $this->searchTargetTitle($records),
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

        return $this->render('admin-v2/operating/certificate-audit/audit-detail-modal.html.twig', [
            'record' => $record,
            'user' => $this->getUserService()->getUserAndProfile($record['userId']),
            'target' => $strategy->getTarget($record['targetId']),
        ]);
    }

    public function submitAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $auditType = $request->get('status');
            $rejectReason = $request->get('rejectReason');

            $record = $this->getRecordService()->get($id);
            if (empty($record)) {
                $this->createNewException(CertificateException::NOTFOUND_RECORD);
            }

            $auditUser = $this->getUser();
            $auditUserId = $auditUser['id'];

            switch ($auditType) {
                case 'valid':
                    $this->getRecordService()->passCertificateRecord($id, $auditUserId);
                    break;
                case 'reject':
                    $this->getRecordService()->rejectCertificateRecord($id, $auditUserId, $rejectReason);
                    break;
                case 'none':
                    $this->getRecordService()->resetCertificateRecord($id);
                    break;
                default:
                    $this->createNewException(CertificateException::FORBIDDEN_AUDIT_RECORD);
            }
        }

        return $this->createJsonResponse(true);
    }

    protected function searchTargetTitle($records)
    {
        $courseRecords = [];
        $classroomRecords = [];
        $targets = [];

        foreach ($records as $key => $record) {
            if ('course' == $record['targetType']) {
                $courseRecords[$key] = $record;
            }
            if ('classroom' == $record['targetType']) {
                $classroomRecords[$key] = $record;
            }
        }

        $courses = $this->getCertificateStrategy('course')->findTargetsByIds(ArrayToolkit::column($records, 'targetId'));
        $classrooms = $this->getCertificateStrategy('classroom')->findTargetsByIds(ArrayToolkit::column($records, 'targetId'));

        foreach ($records as $key => $record) {
            if ('course' == $record['targetType']) {
                $targets[$record['id']] = empty($courses[$record['targetId']]) ? null : $courses[$record['targetId']];
            }
            if ('classroom' == $record['targetType']) {
                $targets[$record['id']] = empty($classrooms[$record['targetId']]) ? null : $classrooms[$record['targetId']];
            }
        }

        return $targets;
    }

    protected function searchConditions(Request $request)
    {
        $conditions['keywordType'] = $request->get('keywordType');
        $conditions['keyword'] = $request->get('keyword');

        if (!empty($conditions['keyword']) && !empty($conditions['keywordType'])) {
            if (in_array($conditions['keywordType'], ['nickname', 'verifiedMobile', 'email'])) {
                $users = $this->getUserService()->searchUsers([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
                $conditions['userIds'] = $users ? ArrayToolkit::column($users, 'id') : [-1];
            }
            if ('truename' == $conditions['keywordType']) {
                $users = $this->getUserService()->searchUserProfiles([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
                $conditions['userIds'] = $users ? ArrayToolkit::column($users, 'id') : [-1];
            }
        } else {
            return [];
        }

        return $conditions;
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return $this->createService('Certificate:RecordService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getCertificateStrategy($type)
    {
        return $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($type);
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }
}
