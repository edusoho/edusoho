<?php

namespace Biz\Importer;

use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class ClassroomMemberImporter extends Importer
{
    protected $type = 'classroom-member';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $classroomId = $request->request->get('classroomId');
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $price = $request->request->get('price');
        $remark = $request->request->get('remark', '通过批量导入添加');
        $orderData = array(
            'amount' => $price,
            'remark' => $remark,
        );

        return $this->excelDataImporting($classroom, $importData, $orderData);
    }

    protected function excelDataImporting($targetObject, $userData, $orderData)
    {
        $existsUserCount = 0;
        $successCount = 0;

        foreach ($userData as $key => $user) {
            if (!empty($user['nickname'])) {
                $user = $this->getUserService()->getUserByNickname($user['nickname']);
            } else {
                if (!empty($user['email'])) {
                    $user = $this->getUserService()->getUserByEmail($user['email']);
                } else {
                    $user = $this->getUserService()->getUserByVerifiedMobile($user['verifiedMobile']);
                }
            }

            $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($targetObject['id'], $user['id']);

            $isClassroomTeacher = $this->getClassroomService()->isClassroomTeacher($targetObject['id'], $user['id']);

            if ($isClassroomStudent || $isClassroomTeacher) {
                ++$existsUserCount;
            } else {
                $info = array(
                    'price' => $orderData['amount'],
                    'remark' => empty($orderData['remark']) ? '通过批量导入添加' : $orderData['remark'],
                    'isNotify' => 1,
                );
                $this->getClassroomService()->becomeStudentWithOrder($targetObject['id'], $user['id'], $info);

                ++$successCount;
            }
        }

        return array('existsUserCount' => $existsUserCount, 'successCount' => $successCount);
    }

    public function getTemplate(Request $request)
    {
        $classroomId = $request->query->get('classroomId');
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        return $this->render('classroom-manage/import.html.twig', array(
            'classroom' => $classroom,
            'importerType' => $this->type,
        ));
    }

    public function tryImport(Request $request)
    {
        $classroomId = $request->query->get('classroomId');

        if (empty($classroomId)) {
            $classroomId = $request->request->get('classroomId');
        }

        $this->getClassroomService()->tryManageClassroom($classroomId);
    }

    public function check(Request $request)
    {
        $file = $request->files->get('excel');
        $danger = $this->validateExcelFile($file);
        if (!empty($danger)) {
            return $danger;
        }

        $classroomId = $request->request->get('classroomId');
        $classroomCoursesCount = $this->getClassroomService()->countCoursesByClassroomId($classroomId);
        $chunkNum = $this->calculateChunkNum($classroomCoursesCount);

        $importData = $this->getUserData();

        if (!empty($importData['errorInfo'])) {
            return $this->createErrorResponse($importData['errorInfo']);
        }

        return $this->createSuccessResponse(
            $importData['allUserData'],
            $importData['checkInfo'],
            array_merge($request->request->all(), array('chunkNum' => $chunkNum))
        );
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
