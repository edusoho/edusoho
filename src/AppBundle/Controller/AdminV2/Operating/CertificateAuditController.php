<?php


namespace AppBundle\Controller\AdminV2\Operating;


use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\AuditService;
use Biz\Certificate\Service\RecordService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Course\Service\CourseService;
use Biz\Classroom\Service\ClassroomService;

class CertificateAuditController extends BaseController
{
    public function indexAction(Request $request)
    {
        {
            $conditions = $request->query->all();

            $paginator = new Paginator(
                $request,
                $this->getAuditService()->count($conditions),
                20
            );

            $certificates = $this->getAuditService()->search(
                $conditions,
                ['createdTime' => 'desc'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

//            $targets = [];
//            foreach($certificates as $key => $certificate) {$targets[$key] = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($certificates, 'targetId'));
//                if ($certificate['targetType'] == 'course') {
//                    $targets[$key] = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($certificates, 'targetId'));
//                }
//                if ($certificate['targetType'] == 'classroom') {
//                    $targets[$key] = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($certificates, 'targetId'));
//                }
//            }

//            $strategy->findTargetsByIds(ArrayToolkit::column($records, 'targetId'));
            $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($certificates, 'userId'));
            $reviewers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($certificates, 'auditUserId'));

//            echo('<pre>');
//            print_r($targets);
//            echo('</pre>');
//            exit();
            return $this->render('admin-v2/operating/certificate-audit/index.html.twig', [
                'certificates' => $certificates,
                'paginator' => $paginator,
                'users' => ArrayToolkit::index($users, 'id'),
                'reviewers' => ArrayToolkit::index($reviewers, 'id'),
//                'targets' => $targets,
            ]);
        }
    }

    public function detailAction(Request $request, $id)
    {
        $record = $this->getAuditService()->get($id);
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

    public function auditAction(Request $request, $id)
    {
        $fields = $request->request->all();

        $this->getAuditService()->update($id, $fields);
        $record = $this->getRecordService()->get($id);

        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD());
        }

        if ($record['status'] == 'valid') {
            $this->createNewException(CertificateException::FORBIDDEN_AUDIT_RECORD);
        };


//        $strategy = $this->getCertificateStrategy($record['targetType']);

        if ($request->isMethod('POST')) {
            $this->getAuditService()->update($id, $fields);

            return $this->createJsonResponse(true);
        }

        return $this->render('', [
            'record' => $fields,
        ]);
    }

//    public function certificatePassAction(Request $request, $id)
//    {
//        $record = $this->getAuditService()->get($id);
//
//        if (empty($record)) {
//            $this->createNewException(CertificateException::NOTFOUND_RECORD);
//        }
//
//        if ('valid' == $record['status']) {
//            $this->createNewException(CertificateException::FORBIDDEN_PASS_RECORD);
//        }
//
//        $strategy = $this->getCertificateStrategy($record['targetType']);
//
//        if ($request->isMethod('POST')) {
//            $this->getAuditService()->passCertificate($id, $request->request->all());
//
//            return $this->createJsonResponse(true);
//        }
//
//        return $this->render('', [
//            'record' => $record,
//            'user' => $this->getUserService()->getUserAndProfile($record['userId']),
//            'target' => $strategy->getTarget($record['targetId']),
//        ]);
//    }

    public function certificateRejectAction(Request $request, $id)
    {
        $record = $this->getAuditService()->get($id);

        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }

        if ('reject' != $record['status'] || '' != $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_REJECT_RECORD);
        }

        $strategy = $this->getCertificateStrategy($record['targetType']);

        if ($request->isMethod('POST')) {
            $this->getAuditService()->rejectCertificate($id, $request->request->all());

            return $this->createJsonResponse(true);
        }

        return $this->render('', [
            'record' => $record,
            'user' => $this->getUserService()->getUserAndProfile($record['userId']),
            'target' => $strategy->getTarget($record['targetId']),
        ]);
    }

//    protected function prepareSearchConditions($conditions)
//    {
//        if (in_array($conditions['keywordType'], ['nickname', 'verifiedMobile', 'email'])) {
//            $users = $this->getUserService()->searchUsers([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
//            $conditions['userIds'] = $users ? ArrayToolkit::column($users, 'id') : [-1];
//        }
//        if ('truename' == $conditions['keywordType']) {
//            $users = $this->getUserService()->searchUserProfiles([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
//            $conditions['userIds'] = $users ? ArrayToolkit::column($users, 'id') : [-1];
//        }
//        unset($conditions['keywordType']);
//        unset($conditions['keyword']);
//
//        $conditions['statusNotEqual'] = 'none';
//
//        return $conditions;
//    }

    /**
     * @return AuditService
     */
    protected function getAuditService()
    {
        return $this->createService('Certificate:AuditService');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCertificateStrategy($type)
    {
        return $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($type);
    }
}