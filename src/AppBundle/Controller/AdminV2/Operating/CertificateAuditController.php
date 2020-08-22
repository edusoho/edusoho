<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\RecordService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class CertificateAuditController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->searchConditions($conditions);

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
            'certificates' => $records,
            'paginator' => $paginator,
            'users' => ArrayToolkit::index($users, 'id'),
            'reviewers' => ArrayToolkit::index($reviewers, 'id'),
            'targets' => $this->searchTargetTitle($records),
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
        $fields = $request->request->all();
        $auditType = $request->get('status');

        $record = $this->getRecordService()->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }

        if ('none' != $fields['status']) {
            $user = $this->getUser();
            $fields['auditUserId'] = $user['id'];
            $fields['auditTime'] = time();
        }

        if ('valid' == $auditType) {
            $this->getRecordService()->validCertificate($id, $fields);
        } elseif ('reject' == $auditType) {
            $this->getRecordService()->rejectCertificate($id, $fields);
        } elseif ('none' == $auditType) {
            $this->getRecordService()->toBeAuditCertificate($id, $fields);
        } else {
            $this->createNewException(CertificateException::FORBIDDEN_AUDIT_RECORD);
        }

        if ($request->isMethod('POST')) {
            return $this->createJsonResponse(true);
        }

        return $this->render('', [
            'record' => $fields,
        ]);
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
                $targets[$key + 1] = empty($courses[$record['targetId']]) ? null : $courses[$record['targetId']];
            }
            if ('classroom' == $record['targetType']) {
                $targets[$key + 1] = empty($classrooms[$record['targetId']]) ? null : $classrooms[$record['targetId']];
            }
        }

        return $targets;
    }

    protected function searchConditions($conditions)
    {
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
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

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
}
