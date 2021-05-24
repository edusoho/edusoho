<?php

namespace Biz\Importer;

use AppBundle\Common\SimpleValidator;
use Biz\Course\Service\MemberService;
use Symfony\Component\HttpFoundation\Request;

class CourseMemberImporter extends Importer
{
    protected $type = 'course-member';

    protected $necessaryFields = ['nickname' => '用户名', 'verifiedMobile' => '手机', 'email' => '邮箱', 'weixin' => '微信号'];

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

            if (!empty($data['weixin'])) {
                $this->getUserService()->updateUserProfile($user['id'], ['weixin' => $data['weixin']]);
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

    protected function getUserData()
    {
        $userCount = 0;
        $fieldSort = $this->getFieldSort();
        $validate = [];
        $allUserData = [];

        for ($row = 3; $row <= $this->rowTotal; ++$row) {
            for ($col = 0; $col < $this->colTotal; ++$col) {
                $infoData = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData.'';
            }

            foreach ($fieldSort as $sort) {
                $userData[$sort['fieldName']] = trim($columnsData[$sort['num']]);
                $fieldCol[$sort['fieldName']] = $sort['num'] + 1;
            }

            $emptyData = array_count_values($userData);

            if (isset($emptyData['']) && count($userData) == $emptyData['']) {
                $checkInfo[] = sprintf('第%s行为空行，已跳过', $row);
                continue;
            }

            $info = $this->validExcelFieldValue($userData, $row, $fieldCol);
            empty($info) ? '' : $errorInfo[] = $info;

            $userCount = $userCount + 1;

            $allUserData[] = $userData;

            if (empty($errorInfo)) {
                if (!empty($userData['nickname'])) {
                    $user = $this->getUserService()->getUserByNickname($userData['nickname']);
                } elseif (!empty($userData['email'])) {
                    $user = $this->getUserService()->getUserByEmail($userData['email']);
                } elseif (!empty($userData['verifiedMobile'])) {
                    $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
                }

                if (!empty($user)) {
                    $this->filterUser($user);
                }

                $validate[] = array_merge($user, ['row' => $row, 'weixin' => $userData['weixin']]);
            }

            unset($userData);
        }

        $this->passValidateUser = $validate;

        $data['errorInfo'] = empty($errorInfo) ? [] : $errorInfo;
        $data['checkInfo'] = empty($checkInfo) ? [] : $checkInfo;
        $data['userCount'] = $userCount;
        $data['allUserData'] = empty($this->passValidateUser) ? [] : $this->passValidateUser;

        return $data;
    }

    protected function validExcelFieldValue($userData, $row, $fieldCol)
    {
        $errorInfo = '';

        if (!empty($userData['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($userData['nickname']);
        } elseif (!empty($userData['email'])) {
            $user = $this->getUserService()->getUserByEmail($userData['email']);
        } elseif (!empty($userData['verifiedMobile'])) {
            $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
        }

        if (!empty($user) && !empty($userData['email']) && !in_array($userData['email'], $user)) {
            $user = null;
        }

        if (!empty($user) && !empty($userData['verifiedMobile']) && !in_array($userData['verifiedMobile'], $user)) {
            $user = null;
        }

        if (!empty($user) && !empty($userData['nickname']) && !in_array($userData['nickname'], $user)) {
            $user = null;
        }

        if (!SimpleValidator::weixin($userData['weixin'])) {
            $errorInfo = sprintf('第%s行的信息有误，微信号错误，请检查。', $row);
        }

        if (!$user) {
            $errorInfo = sprintf('第%s行的信息有误，用户数据不存在，请检查。', $row);
        }

        return $errorInfo;
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
