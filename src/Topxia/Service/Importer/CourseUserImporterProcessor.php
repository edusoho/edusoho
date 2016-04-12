<?php
namespace Topxia\Service\Importer;

use PHPExcel_Cell;
use PHPExcel_IOFactory;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\ServiceKernel;

class CourseUserImporterProcessor implements ImporterProcessor
{
    protected $necessaryFields = array('nickname' => '用户名', 'verifiedMobile' => '手机', 'email' => '邮箱');
    protected $objWorksheet;
    protected $rowTotal         = 0;
    protected $colTotal         = 0;
    protected $excelFields      = array();
    protected $passValidateUser = array();
    protected $excelExample     = 'bundles/topxiaweb/example/coursemember_import_example.xls';
    protected $validateRouter   = 'course_manage_student_import';
    protected $importingRouter  = 'course_manage_student_to_base';

    public function validateExcelFile($file)
    {
        $errorMessage = '';

        if (!is_object($file)) {
            $errorMessage = $this->getKernel()->trans('请选择上传的文件');
            return $errorMessage;
        }

        if (FileToolkit::validateFileExtension($file, 'xls xlsx')) {
            $errorMessage = $this->getKernel()->trans('Excel格式不正确！');
            return $errorMessage;
        }

        $this->excelAnalyse($file);

        if ($this->rowTotal > 1000) {
            $message = $this->getKernel()->trans('Excel超过1000行数据!');
            return $errorMessage;
        }

        if (!$this->checkNecessaryFields($this->excelFields)) {
            $message = $this->getKernel()->trans('缺少必要的字段');
            return $errorMessage;
        }

        return $errorMessage;
    }

    public function excelAnalyse($file)
    {
        $objPHPExcel        = PHPExcel_IOFactory::load($file);
        $objWorksheet       = $objPHPExcel->getActiveSheet();
        $highestRow         = $objWorksheet->getHighestRow();
        $highestColumn      = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelFields        = array();

        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $fieldTitle                                  = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
            empty($fieldTitle) ? '' : $excelFields[$col] = $this->trim($fieldTitle);
        }

        $rowAndCol = array('rowLength' => $highestRow, 'colLength' => $highestColumnIndex);

        $this->objWorksheet = $objWorksheet;
        $this->rowTotal     = $highestRow;
        $this->colTotal     = $highestColumnIndex;
        $this->excelFields  = $excelFields;

        return array($objWorksheet, $rowAndCol, $excelFields);
    }

    public function getExcelFieldsValue()
    {
        $columnsData = array();

        for ($col = 0; $col < $this->colTotal; $col++) {
            $infoData          = $this->objWorksheet->getCellByColumnAndRow($col, 2)->getFormattedValue();
            $columnsData[$col] = $infoData."";
        }

        return $columnsData;
    }

    public function validExcelFieldValue($userData, $row, $fieldCol)
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
            $errorInfo = $this->getKernel()->trans("第%row%行的信息有误，用户数据不存在，请检查。",array('%row%'=>$row));
        }

        return $errorInfo;
    }

    public function checkRepeatData()
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

                $nicknameData[] = $nickNameColData."";
            }

            $info = $this->arrayRepeat($nicknameData, $nickNameCol);

            empty($info) ? '' : $errorInfo[] = $info;
        }

        return $errorInfo;
    }

    public function arrayRepeat($array, $nickNameCol)
    {
        $repeatArray      = array();
        $repeatArrayCount = array_count_values($array);
        $repeatRow        = "";

        foreach ($repeatArrayCount as $key => $value) {
            if ($value > 1 && !empty($key)) {
                $repeatRow .= $this->getKernel()->trans('第(%nickNameCol%)列重复:',array('%nickNameCol%'=>$nickNameCol + 1))."<br>";

                for ($i = 1; $i <= $value; $i++) {
                    $row = array_search($key, $array) + 3;

                    $repeatRow .= $this->getKernel()->trans('第%row%行%key%',array('%row%'=>$row,'%key%'=>$key))."<br>";

                    unset($array[$row - 3]);
                }
            }
        }

        return $repeatRow;
    }

    public function getFieldSort()
    {
        $fieldSort       = array();
        $necessaryFields = $this->getNecessaryFields();
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

    public function checkNecessaryFields($excelFields)
    {
        $necessaryFields = $this->getNecessaryFields();

        if ($necessaryFields = array_intersect($necessaryFields, array_values($excelFields))) {
            return true;
        }

        return false;
    }

    public function getUserData()
    {
        $userCount   = 0;
        $fieldSort   = $this->getFieldSort();
        $validate    = array();
        $allUserData = array();

        for ($row = 3; $row <= $this->rowTotal; $row++) {
            for ($col = 0; $col < $this->colTotal; $col++) {
                $infoData          = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData."";
            }

            foreach ($fieldSort as $sort) {
                $userData[$sort['fieldName']] = $columnsData[$sort['num']];
                $fieldCol[$sort['fieldName']] = $sort['num'] + 1;
            }

            $emptyData = array_count_values($userData);

            if (isset($emptyData[""]) && count($userData) == $emptyData[""]) {
                $checkInfo[] = $this->getKernel()->trans('第%row%行为空行，已跳过',array('%row%'=>$row));
                continue;
            }

            $info                            = $this->validExcelFieldValue($userData, $row, $fieldCol);
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
        $allUserData            = json_encode($allUserData);

        $data['errorInfo']   = empty($errorInfo) ? array() : $errorInfo;
        $data['checkInfo']   = empty($checkInfo) ? array() : $checkInfo;
        $data['userCount']   = $userCount;
        $data['allUserData'] = empty($allUserData) ? array() : $allUserData;

        return $data;
    }

    public function checkPassedRepeatData()
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
            $repeatRowInfo .= $this->getKernel()->trans('字段对应用户数据重复')."</br>";

            foreach ($repeatRow as $row) {
                $repeatRowInfo .= $this->getKernel()->trans('重复行：')."</br>";

                foreach ($row as $value) {
                    $repeatRowInfo .= $this->getKernel()->trans('第%value%行 ',array('%value%'=>$value));
                }

                $repeatRowInfo .= "</br>";

                $repeatArray[] = $repeatRowInfo;
                $repeatRowInfo = '';
            }
        }

        return $repeatArray;
    }

    public function tryManage($targetId)
    {
        return $this->getCourseService()->tryManageCourse($targetId);
    }

    public function getExcelExample()
    {
        return $this->excelExample;
    }

    public function getExcelInfoValidateUrl()
    {
        return $this->validateRouter;
    }

    public function getExcelInfoImportUrl()
    {
        return $this->importingRouter;
    }

    public function excelDataImporting($targetObject, $userData, $userUrl)
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
                    'title'      => $this->getKernel()->trans('购买课程《%title%》(管理员添加)',array('%title%'=>$targetObject['title'])),
                    'targetType' => 'course',
                    'targetId'   => $targetObject['id'],
                    'amount'     => 0,
                    'payment'    => 'none',
                    'snPrefix'   => 'CR'
                ));

                $this->getOrderService()->payOrder(array(
                    'sn'       => $order['sn'],
                    'status'   => 'success',
                    'amount'   => 0,
                    'paidTime' => time()
                ));

                $info = array(
                    'orderId' => $order['id'],
                    'note'    => $this->getKernel()->trans('通过批量导入添加')
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

                $this->getLogService()->info('course', 'add_student', $this->getKernel()->trans("课程《%title%》(#%objectId%)，添加学员%nickname%(#%id%)，备注：通过批量导入添加",array('%title%'=>$targetObject['title'],'%objectId%'=>$targetObject['id'],'%nickname%'=>$user['nickname'],'%id%'=>$user['id'])));
            }
        }

        return array('existsUserCount' => $existsUserCount, 'successCount' => $successCount);
    }

    protected function trim($data)
    {
        $data = trim($data);
        $data = str_replace(" ", "", $data);
        $data = str_replace('\n', '', $data);
        $data = str_replace('\r', '', $data);
        $data = str_replace('\t', '', $data);

        return $data;
    }
    public function getNecessaryFields()
    {
           $necessaryFields = array('nickname' => '用户名', 'verifiedMobile' => '手机', 'email' => '邮箱');
         return $this->getKernel()->transArray($necessaryFields);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System.LogService');
    }
        protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
