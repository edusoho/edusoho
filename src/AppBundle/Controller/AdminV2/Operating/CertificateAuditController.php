<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\AuditService;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class CertificateAuditController extends BaseController
{
    public function indexAction(Request $request)
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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($certificates, 'userId'));
        $reviewers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($certificates, 'auditUserId'));

        return $this->render('admin-v2/operating/certificate-audit/index.html.twig', [
                'certificates' => $certificates,
                'paginator' => $paginator,
                'users' => ArrayToolkit::index($users, 'id'),
                'reviewers' => ArrayToolkit::index($reviewers, 'id'),
                'targets' => $this->searchTargetTitle($certificates),
            ]);
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

        $fields['auditTime'] = time();
        if ('reject' != $fields['status']) {
            unset($fields['rejectReason']);
        }
        if ('none' != $fields['status']) {
            $user = $this->getUser();
            $fields['auditUserId'] = $user['id'];
        }
        $this->getAuditService()->update($id, $fields);

        $record = $this->getRecordService()->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }
        if ('valid' == $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_AUDIT_RECORD);
        }

        if ($request->isMethod('POST')) {
            $this->getAuditService()->update($id, $fields);

            return $this->createJsonResponse(true);
        }

        return $this->render('', [
            'record' => $fields,
        ]);
    }

    protected function searchTargetTitle($certificates)
    {
        $courses = [];
        $classrooms = [];
        $targets = [];
        foreach ($certificates as $key => $certificate) {
            if ('course' == $certificate['targetType']) {
                $courses[$key] = $certificate['targetId'];
            } else {
                $courses[$key] = '';
            }
            if ('classroom' == $certificate['targetType']) {
                $classrooms[$key] = $certificate['targetId'];
            } else {
                $classrooms[$key] = '';
            }
        }

        $strategyCourses = $this->getCertificateStrategy('course')->findTargetsByIds($courses);
        $strategyClassrooms = $this->getCertificateStrategy('classroom')->findTargetsByIds($classrooms);

        foreach ($certificates as $key => $certificate) {
            foreach ($strategyCourses as $strategyCourse) {
                if ($strategyCourse['id'] == $certificate['targetId'] && 'course' == $certificate['targetType']) {
                    $targets[$key + 1] = $strategyCourse;
                }
            }
            foreach ($strategyClassrooms as $strateClassroom) {
                if ($strateClassroom['id'] == $certificate['targetId'] && 'classroom' == $certificate['targetType']) {
                    $targets[$key + 1] = $strateClassroom;
                }
            }
        }

        return $targets;
    }

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
