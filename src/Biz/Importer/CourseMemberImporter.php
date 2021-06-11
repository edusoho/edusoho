<?php

namespace Biz\Importer;

use Biz\Course\Service\MemberService;
use Symfony\Component\HttpFoundation\Request;

class CourseMemberImporter extends Importer
{
    protected $type = 'course-member';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $courseId = $request->request->get('courseId');
        $price = $request->request->get('price');
        $remark = $request->request->get('remark', '通过批量导入添加');
        $course = $this->getCourseService()->getCourse($courseId);
        $orderData = [
            'amount' => $price,
            'remark' => $remark,
        ];

        return $this->excelDataImporting($course, $importData, $orderData);
    }

    protected function excelDataImporting($course, $userData, $orderData)
    {
        $existsUserCount = 0;
        $successCount = 0;

        foreach ($userData as $key => $data) {
            if (!empty($data['nickname'])) {
                $user = $this->getUserService()->getUserByNickname($data['nickname']);
            } else {
                if (!empty($data['email'])) {
                    $user = $this->getUserService()->getUserByEmail($data['email']);
                } else {
                    $user = $this->getUserService()->getUserByVerifiedMobile($data['verifiedMobile']);
                }
            }

            $isCourseStudent = $this->getCourseMemberService()->isCourseStudent($course['id'], $user['id']);
            $isCourseTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']);

            if ($isCourseStudent || $isCourseTeacher) {
                ++$existsUserCount;
            } else {
                $data = [
                    'price' => $orderData['amount'],
                    'remark' => empty($orderData['remark']) ? '通过批量导入添加' : $orderData['remark'],
                    'source' => 'outside',
                ];
                $this->getCourseMemberService()->becomeStudentAndCreateOrder($user['id'], $course['id'], $data);

                ++$successCount;
            }
        }

        return ['existsUserCount' => $existsUserCount, 'successCount' => $successCount];
    }

    public function getTemplate(Request $request)
    {
        $courseId = $request->query->get('courseId');
        $course = $this->getCourseService()->getCourse($courseId);

        return $this->render(
            'course-manage/student/import.html.twig',
            [
                'course' => $course,
                'importerType' => $this->type,
            ]
        );
    }

    public function tryImport(Request $request)
    {
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $courseId = $request->request->get('courseId');
        }

        $this->getCourseService()->tryManageCourse($courseId);
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
