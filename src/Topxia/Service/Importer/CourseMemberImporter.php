<?php


namespace Topxia\Service\Importer;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;

class CourseMemberImporter extends Importer
{
    protected $necessaryFields  = array('nickname' => '用户名', 'verifiedMobile' => '手机', 'email' => '邮箱');
    protected $objWorksheet;
    protected $rowTotal         = 0;
    protected $colTotal         = 0;
    protected $excelFields      = array();
    protected $passValidateUser = array();

    protected $type = 'course-member';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $courseId   = $request->request->get('courseId');
        $price      = $request->request->get('price');
        $remark     = $request->request->get('remark');
        $course     = $this->getCourseService()->getCourse($courseId);
        $orderData  = array(
            'amount' => $price,
            'remark' => $remark
        );
        return $this->excelDataImporting($course, $importData, $orderData);
    }

    protected function excelDataImporting($targetObject, $userData, $orderData)
    {
        $existsUserCount = 0;
        $successCount    = 0;

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

            $isCourseStudent = $this->getCourseService()->isCourseStudent($targetObject['id'], $user['id']);
            $isCourseTeacher = $this->getCourseService()->isCourseTeacher($targetObject['id'], $user['id']);

            if ($isCourseStudent || $isCourseTeacher) {
                $existsUserCount++;
            } else {
                $currentUser = $this->getUserService()->getCurrentUser();

                $order = $this->getOrderService()->createOrder(array(
                    'userId'     => $user['id'],
                    'title'      => "购买课程《{$targetObject['title']}》(管理员添加)",
                    'targetType' => 'course',
                    'targetId'   => $targetObject['id'],
                    'amount'     => empty($orderData['amount']) ? 0 : $orderData['amount'],
                    'payment'    => 'outside',
                    'snPrefix'   => 'C',
                    'note'       => empty($orderData['remark']) ? '通过批量导入添加' : $orderData['remark']
                ));

                $this->getOrderService()->payOrder(array(
                    'sn'       => $order['sn'],
                    'status'   => 'success',
                    'amount'   => $order['amount'],
                    'paidTime' => time()
                ));

                $info = array(
                    'orderId' => $order['id']
                );

                if ($this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info)) {
                    $successCount++;
                };

                $member = $this->getCourseService()->getCourseMember($targetObject['id'], $user['id']);

                $message = array(
                    'courseId'    => $targetObject['id'],
                    'courseTitle' => $targetObject['title'],
                    'userId'      => $currentUser['id'],
                    'userName'    => $currentUser['nickname'],
                    'type'        => 'create');

                $this->getNotificationService()->notify($member['userId'], 'course-student', $message);

                $this->getLogService()->info('course', 'add_student', "课程《{$targetObject['title']}》(#{$targetObject['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：通过批量导入添加");
            }
        }

        return array('existsUserCount' => $existsUserCount, 'successCount' => $successCount);
    }

    public function check(Request $request)
    {
        $file     = $request->files->get('excel');
        $courseId = $request->request->get('courseId');
        $price    = $request->request->get('price');
        $remark   = $request->request->get('remark');
        $danger   = $this->validateExcelFile($file);
        if (!empty($danger)) {
            return $danger;
        }

        $repeatInfo = $this->checkRepeatData();
        if (!empty($repeatInfo)) {
            return $this->createErrorResponse($repeatInfo);
        }

        $importData = $this->getUserData();

        if (empty($importData['errorInfo'])) {
            $passedRepeatInfo = $this->checkPassedRepeatData();
            if ($passedRepeatInfo) {
                return $this->createErrorResponse($passedRepeatInfo);
            }
        } else {
            return $this->createErrorResponse($importData['errorInfo']);
        }

        return $this->createSuccessResponse(
            $importData['allUserData'],
            $importData['checkInfo'],
            array(
                'courseId' => $courseId,
                'price'    => $price,
                'remark'   => $remark
            ));
    }

    protected function checkPassedRepeatData()
    {
        $passedUsers = $this->passValidateUser;
        $ids         = array();
        $repeatRow   = array();
        $repeatIds   = array();

        foreach ($passedUsers as $key => $passedUser) {
            if (in_array($passedUser['id'], $ids) && !in_array($passedUser['id'], $repeatIds)) {
                $repeatIds[] = $passedUser['id'];
            } else {
                $ids[] = $passedUser['id'];
            }
        }

        foreach ($passedUsers as $key => $passedUser) {
            if (in_array($passedUser['id'], $repeatIds)) {
                $repeatRow[$passedUser['id']][] = $passedUser['row'];
            }
        }

        $repeatRowInfo = '';
        $repeatArray   = array();

        if (!empty($repeatRow)) {
            $repeatRowInfo .= "字段对应用户数据重复</br>";

            foreach ($repeatRow as $row) {
                $repeatRowInfo .= "重复行：</br>";

                foreach ($row as $value) {
                    $repeatRowInfo .= "第" . $value . "行 ";
                }

                $repeatRowInfo .= "</br>";

                $repeatArray[] = $repeatRowInfo;
                $repeatRowInfo = '';
            }
        }

        return $repeatArray;
    }

    protected function getUserData()
    {
        $userCount   = 0;
        $fieldSort   = $this->getFieldSort();
        $validate    = array();
        $allUserData = array();

        for ($row = 3; $row <= $this->rowTotal; $row++) {
            for ($col = 0; $col < $this->colTotal; $col++) {
                $infoData          = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData . "";
            }

            foreach ($fieldSort as $sort) {
                $userData[$sort['fieldName']] = $columnsData[$sort['num']];
                $fieldCol[$sort['fieldName']] = $sort['num'] + 1;
            }

            $emptyData = array_count_values($userData);

            if (isset($emptyData[""]) && count($userData) == $emptyData[""]) {
                $checkInfo[] = "第" . $row . "行为空行，已跳过";
                continue;
            }

            $info = $this->validExcelFieldValue($userData, $row, $fieldCol);
            empty($info) ? '' : $errorInfo[] = $info;

            $userCount     = $userCount + 1;
            $allUserData[] = $userData;

            if (empty($errorInfo)) {
                if (!empty($userData['nickname'])) {
                    $user = $this->getUserService()->getUserByNickname($userData['nickname']);
                } elseif (!empty($userData['email'])) {
                    $user = $this->getUserService()->getUserByEmail($userData['email']);
                } elseif (!empty($userData['verifiedMobile'])) {
                    $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
                }
                $validate[] = array_merge($user, array('row' => $row));
            }

            unset($userData);
        }

        $this->passValidateUser = $validate;

        $data['errorInfo']   = empty($errorInfo) ? array() : $errorInfo;
        $data['checkInfo']   = empty($checkInfo) ? array() : $checkInfo;
        $data['userCount']   = $userCount;
        $data['allUserData'] = empty($this->passValidateUser) ? array() : $this->passValidateUser;

        return $data;
    }

    protected function validateExcelFile($file)
    {
        if (!is_object($file)) {
            return $this->createDangerResponse('请选择上传的文件');
        }

        if (FileToolkit::validateFileExtension($file, 'xls xlsx')) {
            return $this->createDangerResponse('Excel格式不正确！');
        }

        $this->excelAnalyse($file);

        if ($this->rowTotal > 1000) {
            return $this->createDangerResponse('Excel超过1000行数据!');
        }

        if (!$this->checkNecessaryFields($this->excelFields)) {
            return $this->createDangerResponse('缺少必要的字段');
        }
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

        if (!$user) {
            $errorInfo = "第 " . $row . "行的信息有误，用户数据不存在，请检查。";
        }
        return $errorInfo;
    }

    protected function checkRepeatData()
    {
        $errorInfo   = array();
        $checkFields = array_keys($this->necessaryFields);
        $fieldSort   = $this->getFieldSort();

        foreach ($checkFields as $checkField) {
            $nicknameData = array();

            foreach ($fieldSort as $key => $value) {
                if ($value['fieldName'] == $checkField) {
                    $nickNameCol = $value['num'];
                }
            }

            for ($row = 3; $row <= $this->rowTotal; $row++) {
                $nickNameColData = $this->objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();

                $nicknameData[] = $nickNameColData . "";
            }

            $info = $this->arrayRepeat($nicknameData, $nickNameCol);

            empty($info) ? '' : $errorInfo[] = $info;
        }

        return $errorInfo;
    }

    protected function arrayRepeat($array, $nickNameCol)
    {
        $repeatArrayCount = array_count_values($array);
        $repeatRow        = "";

        foreach ($repeatArrayCount as $key => $value) {
            if ($value > 1 && !empty($key)) {
                $repeatRow .= '第' . ($nickNameCol + 1) . "列重复:<br>";

                for ($i = 1; $i <= $value; $i++) {
                    $row = array_search($key, $array) + 3;

                    $repeatRow .= "第" . $row . "行" . "    " . $key . "<br>";

                    unset($array[$row - 3]);
                }
            }
        }

        return $repeatRow;
    }

    protected function getFieldSort()
    {
        $fieldSort       = array();
        $necessaryFields = $this->necessaryFields;
        $excelFields     = $this->excelFields;

        foreach ($excelFields as $key => $value) {
            if (in_array($value, $necessaryFields)) {
                foreach ($necessaryFields as $fieldKey => $fieldValue) {
                    if ($value == $fieldValue) {
                        $fieldSort[$fieldKey] = array("num" => $key, "fieldName" => $fieldKey);
                        break;
                    }
                }
            }
        }

        return $fieldSort;
    }

    protected function excelAnalyse($file)
    {
        $objPHPExcel        = \PHPExcel_IOFactory::load($file);
        $objWorksheet       = $objPHPExcel->getActiveSheet();
        $highestRow         = $objWorksheet->getHighestRow();
        $highestColumn      = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelFields        = array();

        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $fieldTitle = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
            empty($fieldTitle) ? '' : $excelFields[$col] = $this->trim($fieldTitle);
        }

        $rowAndCol = array('rowLength' => $highestRow, 'colLength' => $highestColumnIndex);

        $this->objWorksheet = $objWorksheet;
        $this->rowTotal     = $highestRow;
        $this->colTotal     = $highestColumnIndex;
        $this->excelFields  = $excelFields;

        return array($objWorksheet, $rowAndCol, $excelFields);
    }

    protected function checkNecessaryFields($excelFields)
    {
        return ArrayToolkit::some($this->necessaryFields, function ($fields) use ($excelFields) {
            return in_array($fields, array_values($excelFields));
        });
    }

    public function getTemplate(Request $request)
    {
        $courseId = $request->query->get('courseId');
        $course   = $this->getCourseService()->getCourse($courseId);
        return $this->render('TopxiaWebBundle:CourseStudentManage:import.html.twig', array(
            'course'       => $course,
            'importerType' => $this->type
        ));
    }

    public function tryImport(Request $request)
    {
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $courseId = $request->request->get('courseId');
        }

        $this->getCourseService()->tryManageCourse($courseId);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }
}