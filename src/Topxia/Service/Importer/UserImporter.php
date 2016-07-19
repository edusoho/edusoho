<?php

namespace Topxia\Service\Importer;

use Topxia\Common\FileToolkit;
use Topxia\Common\SimpleValidator;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\AccessDeniedException;

class UserImporter extends Importer
{
    protected $type = 'user';

    public function import(Request $request)
    {
        $postData       = $request->request->all();
        $importerData   = $postData['importData'];
        $checkType      = $postData["checkType"];
        $userByEmail    = array();
        $userByNickname = array();
        $userByMobile   = array();
        $users          = array();

        if ($checkType == "ignore") {
            $this->getUserImporterService()->importUsers($importerData);
            $this->becomeStudent($importerData, 'add');
        }

        if ($checkType == "update") {
            foreach ($importerData as $key => $user) {
                if ($user["gender"] == "男") {
                    $user["gender"] = "male";
                }

                if ($user["gender"] == "女") {
                    $user["gender"] = "female";
                }

                if ($user["gender"] == "") {
                    $user["gender"] = "secret";
                }

                if ($this->getUserImporterService()->isEmailOrMobileRegisterMode()) {
                    if ($this->getUserService()->getUserByVerifiedMobile($user["mobile"])) {
                        //email, nickname, verifiedmobile只有一个能修改
                        $userByMobile[] = $user;
                    }
                } elseif ($this->getUserService()->getUserByNickname($user["nickname"])) {
                    $userByNickname[] = $user;
                } elseif ($this->getUserService()->getUserByEmail($user["email"])) {
                    $userByEmail[] = $user;
                } else {
                    $users[] = $user;
                }
            }

            $this->getUserImporterService()->importUpdateNickname($userByNickname);
            $this->getUserImporterService()->importUpdateEmail($userByEmail);
            $this->getUserImporterService()->importUpdateMobile($userByMobile);
            $this->getUserImporterService()->importUsers($users);

            $this->becomeStudent($importerData, 'update');
        }

        return array(
            'status'  => "success",
            'message' => "finished"
        );
    }

    private function becomeStudent($userData, $mode = 'add')
    {
        foreach ($userData as $key => $value) {
            if (!empty($value['nickname'])) {
                $user = $this->getUserService()->getUserByNickname($value['nickname']);
            } elseif (!empty($value['email'])) {
                $user = $this->getUserService()->getUserByEmail($value['email']);
            } elseif (!empty($value['mobile'])) {
                $user = $this->getUserService()->getUserByVerifiedMobile($value['mobile']);
            }

            if (!empty($value['classroomId'])) {
                $classroomIds = explode(",", $value['classroomId']);

                foreach ($classroomIds as $classroomId) {
                    //更新数据需要判断是否已经成为了学员
                    $classroom = $this->getClassroomService()->getClassroom($classroomId);

                    if ($mode = "update") {
                        $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($classroomId, $user['id']);

                        if ($isClassroomStudent) {
                            continue;
                        }
                    }

                    //添加同时生成订单
                    $order = $this->getOrderService()->createOrder(array(
                        'userId'     => $user['id'],
                        'title'      => $this->getKernel()->trans('购买班级《%title%》(管理员添加)', array('%title%' => $classroom['title'])),
                        'targetType' => 'classroom',
                        'targetId'   => $classroom['id'],
                        'amount'     => '0.00', //暂时默认为0
                        'payment'    => 'outside',
                        'snPrefix'   => 'CR'
                    ));

                    $this->getOrderService()->payOrder(array(
                        'sn'       => $order['sn'],
                        'status'   => 'success',
                        'amount'   => $order['amount'],
                        'paidTime' => time()
                    ));

                    $info = array(
                        'orderId' => $order['id'],
                        'note'    => ''
                    );
                    $this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info);
                }
            }

            if (!empty($value['courseId'])) {
                $courseIds = explode(",", $value['courseId']);

                foreach ($courseIds as $courseId) {
                    //更新数据需要判断是否已经成为了学员
                    $course = $this->getCourseService()->getCourse($courseId);

                    if ($mode = "update") {
                        $isCourseStudent = $this->getCourseService()->isCourseStudent($courseId, $user['id']);

                        if ($isCourseStudent) {
                            continue;
                        }
                    }

                    //添加同时生成订单
                    $data = array('price' => '0', 'isAdminAdded' => 1, 'remark' => '');
                    $this->getCourseMemberService()->becomeStudentAndCreateOrder($user["id"], $course["id"], $data); //该方法内部自带了创建订单过程
                }
            }
        }
    }

    public function check(Request $request)
    {
        $excelModel = $request->request->all();
        $excel      = $request->files->get('excel');
        $checkType  = $excelModel['rule'];
        if (!is_object($excel)) {
            return array(
                'status'  => self::DANGER_STATUS,
                'message' => $this->getKernel()->trans('请选择上传的文件')
            );
        }

        if (FileToolkit::validateFileExtension($excel, 'xls xlsx')) {
            return array(
                'status'  => self::DANGER_STATUS,
                'message' => $this->getKernel()->trans('Excel格式不正确')
            );
        }

        $objPHPExcel  = \PHPExcel_IOFactory::load($excel);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow   = $objWorksheet->getHighestRow();

        $highestColumn      = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        if ($highestRow > 1000) {
            return array(
                'status'  => self::DANGER_STATUS,
                'message' => $this->getKernel()->trans('Excel超过1000行数据')
            );
        }

        $fieldArray = $this->getFieldArray();

        $strs = '';
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $fieldTitle = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
            $strs[$col] = $fieldTitle."";
        }

        $excelField = $strs;
        if (!$this->checkNecessaryFields($excelField)) {
            return array(
                'status'  => self::DANGER_STATUS,
                'message' => $this->getKernel()->trans('缺少必要的字段')
            );
        }

        $errorInfo  = array();
        $checkInfo  = array();
        $userCount  = 0;
        $importData = array();
        //重复性校验
        $fieldSort = $this->getFieldSort($excelField, $fieldArray);
        unset($fieldArray, $excelField);
        $repeatInfo = $this->checkRepeatData($row = 3, $fieldSort, $highestRow, $objWorksheet);

        if ($repeatInfo) {
            $errorInfo[] = $repeatInfo;
            return array(
                'status'    => 'error',
                "errorInfo" => $errorInfo
            );
        }

        for ($row = 3; $row <= $highestRow; $row++) {
            $strs = array();

            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $infoData   = $objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $strs[$col] = $infoData."";
                unset($infoData);
            }

            foreach ($fieldSort as $sort) {
                $num = $sort['num'];
                $key = $sort['fieldName'];

                $userData[$key] = $strs[$num];
                $fieldCol[$key] = $num + 1;
            }

            unset($strs);
            if (!empty($userData['classroomId']) || $userData['classroomId'] == '0') {
                $classroomIds = explode(",", $userData['classroomId']);

                foreach ($classroomIds as $classroomId) {
                    //判断班级是否存在
                    $isClassroomExit = $this->getClassroomService()->getClassroom($classroomId);

                    if ($isClassroomExit && $isClassroomExit['status'] != 'published') {
                        $errorInfo[] = $this->getKernel()->trans('第%row%行班级号为%classroomId%的班级未发布，请检查。', array('%row%' => $row, '%classroomId%' => $classroomId));
                    } elseif (!$isClassroomExit) {
                        $errorInfo[] = $this->getKernel()->trans('第%row%行班级号为%classroomId%的班级不存在，请检查。', array('%row%' => $row, '%classroomId%' => $classroomId));
                    }
                }
            }
            if (!empty($userData['orgCode'])) {
                $org = $this->getOrgService()->getOrgByCode($userData['orgCode']);
                if (empty($org)) {
                    $errorInfo[] = $this->getKernel()->trans('第%row%行组织机编码为%orgCode%的组织机构不存在，请检查。', array('%row%' => $row, '%orgCode%' => $userData['orgCode']));
                }
            }

            if (!empty($userData['courseId']) || $userData['courseId'] == '0') {
                $courseIds = explode(",", $userData['courseId']);

                foreach ($courseIds as $courseId) {
                    //判断课程是否存在
                    $isCourseExit = $this->getCourseService()->getCourse($courseId);

                    if ($isCourseExit && $isCourseExit['status'] != 'published') {
                        $errorInfo[] = $this->getKernel()->trans('第%row%行课程号为的%courseId%的课程未发布，请检查。', array('%row%' => $row, '%courseId%' => $courseId));
                    } elseif ($isCourseExit && $isCourseExit['parentId'] != '0') {
                        $errorInfo[] = $this->getKernel()->trans('第%row%行课程号为的%courseId%的课程课程属于班级课程,无法加入', array('%row%' => $row, '%courseId%' => $courseId));
                    } elseif (!$isCourseExit) {
                        $errorInfo[] = $this->getKernel()->trans('第%row%行课程号为的%courseId%的课程不存在，请检查。', array('%row%' => $row, '%courseId%' => $courseId));
                    }
                }
            }

            $emptyData = array_count_values($userData);

            if (isset($emptyData[""]) && count($userData) == $emptyData[""]) {
                $checkInfo[] = $this->getKernel()->trans('第%row%行为空行，已跳过', array('%row%' => $row));
                continue;
            }

//字段正确性校验

            if ($this->validFields($userData, $row, $fieldCol)) {
                $errorInfo = array_merge($errorInfo, $this->validFields($userData, $row, $fieldCol));
                continue;
            }

            //导入数据校验
            $userData['createdIp'] = $request->getClientIp();

            if (!empty($userData['nickname']) && !$this->checkFieldWithThirdPartyAuth($userData['nickname'], 'nickname')) {
                if ($checkType == "ignore") {
                    $checkInfo[] = $this->getKernel()->trans('第%row%行的用户已存在，已略过', array('%row%' => $row));
                    continue;
                }

                if ($checkType == "update") {
                    $checkInfo[] = $this->getKernel()->trans('第%row%行的用户已存在，将会更新', array('%row%' => $row));
                }

                $userCount    = $userCount + 1;
                $importData[] = $userData;
                continue;
            }

            if (!empty($userData['email']) && !$this->checkFieldWithThirdPartyAuth($userData['email'], 'email')) {
                if ($checkType == "ignore") {
                    $checkInfo[] = $this->getKernel()->trans('第%row%行的用户已存在，已略过', array('%row%' => $row));
                    continue;
                };

                if ($checkType == "update") {
                    $checkInfo[] = $this->getKernel()->trans('第%row%行的用户已存在，将会更新', array('%row%' => $row));
                }

                $userCount    = $userCount + 1;
                $importData[] = $userData;
                continue;
            }

            if (!empty($userData['mobile']) && !$this->checkFieldWithThirdPartyAuth($userData['mobile'], 'mobile')) {
                if (!$this->getUserService()->isMobileAvaliable($userData['mobile'])) {
                    if ($checkType == "ignore") {
                        $checkInfo[] = $this->getKernel()->trans('第%row%行的用户已存在，已略过', array('%row%' => $row));
                        continue;
                    };

                    if ($checkType == "update") {
                        $checkInfo[] = $this->getKernel()->trans('第%row%行的用户已存在，将会更新', array('%row%' => $row));
                    }

                    $userCount    = $userCount + 1;
                    $importData[] = $userData;
                    continue;
                }
            }

            $userCount = $userCount + 1;

            $importData[] = $userData;
            unset($userData);
        }

        if (empty($errorInfo)) {
            return array(
                'status'     => 'success',
                'checkInfo'  => $checkInfo,
                'importData' => $importData,
                'checkType'  => $checkType
            );
        } else {
            return array(
                'status'    => 'error',
                "errorInfo" => $errorInfo
            );
        }
    }

    public function getTemplate(Request $request)
    {
        return $this->render("UserImporterBundle:UserImporter:userinfo.excel.html.twig", array(
            'importerType' => $this->type
        ));
    }

    public function tryImport(Request $request)
    {
        $user = $this->getServiceKernel()->getCurrentUser();
        if (!$user->isAdmin()) {
            throw new AccessDeniedException($this->getKernel()->trans('当前用户没有导入用户权限'));
        }
    }

    private function checkRepeatData($row, $fieldSort, $highestRow, $objWorksheet)
    {
        $errorInfo    = array();
        $emailData    = array();
        $nicknameData = array();
        $mobileData   = array();

        foreach ($fieldSort as $key => $value) {
            if ($value["fieldName"] == "nickname") {
                $nickNameCol = $value["num"];
            }

            if ($value["fieldName"] == "email") {
                $emailCol = $value["num"];
            }

            if ($value["fieldName"] == "mobile") {
                $mobileCol = $value["num"];
            }
        }

        for ($row; $row <= $highestRow; $row++) {
            $emailColData = $objWorksheet->getCellByColumnAndRow($emailCol, $row)->getValue();

            if ($emailColData."" == "") {
                continue;
            }

            $emailData[] = $emailColData."";
        }

        $errorInfo = $this->arrayRepeat($emailData);

        for ($row = 3; $row <= $highestRow; $row++) {
            $nickNameColData = $objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();

            if ($nickNameColData."" == "") {
                continue;
            }

            $nicknameData[] = $nickNameColData."";
        }

        $errorInfo .= $this->arrayRepeat($nicknameData);

        for ($row = 3; $row <= $highestRow; $row++) {
            $mobileColData = $objWorksheet->getCellByColumnAndRow($mobileCol, $row)->getValue();

            if ($mobileColData."" == "") {
                continue;
            }

            $mobileData[] = $mobileColData."";
        }

        $errorInfo .= $this->arrayRepeat($mobileData);
        return $errorInfo;
    }

    private function arrayRepeat($array)
    {
        $repeatArrayCount = array_count_values($array);
        $repeatRow        = "";

        foreach ($repeatArrayCount as $key => $repeatCount) {
            if ($repeatCount > 1) {
                $repeatRow .= $this->getKernel()->trans('重复:').'<br>';

                for ($i = 1; $i <= $repeatCount; $i++) {
                    $row = array_search($key, $array) + 3;
                    $repeatRow .= $this->getKernel()->trans('第%row%行    %key%', array('%row%' => $row, '%key%' => $key)).'<br>';
                    unset($array[$row - 3]);
                }
            }
        }

        return $repeatRow;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function validFields($userData, $row, $fieldCol)
    {
        $errorInfo = array();
        $auth      = $this->getSettingService()->get('auth');

//如果是手机注册模式

        switch ($auth['register_mode']) {
            case 'email_or_mobile':
                if (isset($userData['email']) && !empty($userData['email'])) {
                    if (!SimpleValidator::email($userData['email'])) {
                        $errorInfo[] = $this->getKernel()->trans('根据网校当前注册模式，第 %row%行%email% 列 的数据存在问题，请检查。', array(
                            '%row%' => $row,
                            '%email%' => $fieldCol['email']
                        ));
                    }
                } elseif (isset($userData['mobile']) && !empty($userData['mobile'])) {
                    if (!SimpleValidator::mobile($userData['mobile'])) {
                        $errorInfo[] = $errorInfo[] = $this->getKernel()->trans('根据网校当前注册模式，第 %row%行%mobile% 列 的数据存在问题，请检查。', array(
                            '%row%' => $row,
                            '%mobile%' => $fieldCol['mobile']
                        ));
                    }
                } else {
                    $errorInfo[] = $this->getKernel()->trans('根据网校当前注册模式，第 %row%行%email% 或者%mobile%列 的数据不能均为空，请检查。', array(
                        '%row%' => $row,
                        '%email%' => $fieldCol['email'],
                        '%mobile%' => $fieldCol['mobile']
                    ));
                }

                break;
            case 'email':
                if (isset($userData['email']) && !empty($userData['email'])) {
                    if (!SimpleValidator::email($userData['email'])) {
                        $errorInfo[] = $this->getKernel()->trans('根据网校当前注册模式，第 %row%行%email% 列 的数据存在问题，请检查。', array(
                            '%row%' => $row,
                            '%email%' => $fieldCol['email']
                        ));
                    }
                } else {
                    $errorInfo[] = $this->getKernel()->trans('根据网校当前注册模式，第 %row%行%email% 列 的数据不能为空，请检查。', array(
                        '%row%' => $row,
                        '%email%' => $fieldCol['email']
                    ));
                }

                break;
            case 'mobile':
                if (isset($userData['mobile']) && !empty($userData['mobile'])) {
                    if (!SimpleValidator::mobile($userData['mobile'])) {
                        $errorInfo[] = $this->getKernel()->trans('根据网校当前注册模式，第 %row%行%mobile% 列 的数据存在问题，请检查。', array(
                            '%row%' => $row,
                            '%mobile%' => $fieldCol['mobile']
                        ));                    }
                } else {
                    $errorInfo[] = $this->getKernel()->trans('根据网校当前注册模式，第 %row%行%mobile% 列 的数据不能为空，请检查。', array(
                        '%row%' => $row,
                        '%mobile%' => $fieldCol['mobile']
                    ));
                }

                break;
            default:

                if (isset($userData['email']) && !empty($userData['email'])) {
                    if (!SimpleValidator::email($userData['email'])) {
                        $errorInfo[] = $this->getKernel()->trans('第 %row%行%email% 列 的数据存在问题，请检查。', array(
                            '%row%' => $row,
                            '%email%' => $fieldCol['email']
                        ));
                    }
                } elseif (isset($userData['mobile']) && !empty($userData['mobile'])) {
                    if (!SimpleValidator::mobile($userData['mobile'])) {
                        $errorInfo[] = $this->getKernel()->trans('第 %row%行%mobile% 列 的数据存在问题，请检查。', array(
                            '%row%' => $row,
                            '%mobile%' => $fieldCol['mobile']
                        ));
                    }
                } else {
                    $errorInfo[] = $this->getKernel()->trans('第 %row%行%email% 或者%mobile%列 的数据不能均为空，请检查。', array(
                        '%row%' => $row,
                        '%email%' => $fieldCol['email'],
                        '%mobile%' => $fieldCol['mobile']
                    ));
                }

                break;
        }

        $array = array(
            array(
                'key'      => 'password',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'password')
            ),
            array(
                'key'      => 'nickname',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'nickname')
            ),
            array(
                'key'      => 'truename',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'truename')
            ),
            array(
                'key'      => 'idcard',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'idcard')
            ),
            array(
                'key'      => 'gender',
                'callback' => function ($data) use ($row, $fieldCol) {
                    if (!in_array($data, array("男", "女"))) {
                        return "第 ".$row."行".$fieldCol["gender"]." 列 的数据存在问题，请检查。";
                    }
                }
            ),
            array(
                'key'      => 'qq',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'qq')
            ),
            array(
                'key'      => 'classroomId',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'numbers')
            ),
            array(
                'key'      => 'courseId',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'numbers')
            ),
            array(
                'key'      => 'site',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'site')
            ),
            array(
                'key'      => 'weibo',
                'callback' => array('Topxia\\Common\\SimpleValidator', 'site')
            )
        );

        foreach ($array as $item) {
            if (!empty($userData[$item['key']])) {
                $key    = $item['key'];
                $method = $item['callback'];

                if (!is_array($method)) {
                    $error = call_user_func($method, $userData[$key]);
                } else {
                    $callback = function ($data) use ($row, $fieldCol, $method, $key) {
                        if (!forward_static_call_array($method, $data)) {
                            return "第 ".$row."行".$fieldCol[$key]." 列 的数据存在问题，请检查。";
                        } else {
                            return "";
                        }
                    };
                    $error = call_user_func($callback, array($userData[$key]));
                }

                if (!empty($error) && is_string($error)) {
                    $errorInfo[] = $error;
                }
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            if (isset($userData['intField'.$i]) && $userData['intField'.$i] != "" && !SimpleValidator::integer($userData['intField'.$i])) {
                $errorInfo[] = "第 ".$row."行".$fieldCol["intField".$i]." 列 的数据存在问题，请检查(必须为整数,最大到9位整数)。";
            }

            if (isset($userData['floatField'.$i]) && $userData['floatField'.$i] != "" && !SimpleValidator::float($userData['floatField'.$i])) {
                $errorInfo[] = "第 ".$row."行".$fieldCol["floatField".$i]." 列 的数据存在问题，请检查(只保留到两位小数)。";
            }

            if (isset($userData['dateField'.$i]) && $userData['dateField'.$i] != "" && !SimpleValidator::date($userData['dateField'.$i])) {
                $errorInfo[] = "第 ".$row."行".$fieldCol["dateField".$i]." 列 的数据存在问题，请检查(格式如XXXX-MM-DD)。";
            }
        }

        return $errorInfo;
    }

    private function checkFieldWithThirdPartyAuth($field, $type)
    {
        switch ($type) {
            case 'nickname':
                $defaultResult    = $this->getUserService()->isNicknameAvaliable($field);
                list($result, $_) = $this->getAuthService()->checkUsername($field);
                $authResult       = $this->validateResult($result);
                break;

            case 'email':
                $defaultResult    = $this->getUserService()->isEmailAvaliable($field);
                list($result, $_) = $this->getAuthService()->checkEmail($field);
                $authResult       = $this->validateResult($result);
                break;

            case 'mobile':
                $defaultResult    = $this->getUserService()->isMobileAvaliable($field);
                list($result, $_) = $this->getAuthService()->checkMobile($field);
                $authResult       = $this->validateResult($result);
                break;

            default:
                return false;
        }

        return $defaultResult && $authResult;
    }

    private function checkNecessaryFields($data)
    {
        $data = implode("", $data);
        $data = $this->trim($data);

        if ($this->getUserImporterService()->isEmailOrMobileRegisterMode()) {
            $mobile_array = explode("手机号", $data);
            $email_array  = explode("邮箱", $data);

            if (count($mobile_array) <= 1 && count($email_array) <= 1) {
                return false;
            }
        } else {
            $tmparray = explode("邮箱", $data);

            if (count($tmparray) <= 1) {
                return false;
            }
        }

        $tmparray = explode("密码", $data);

        if (count($tmparray) <= 1) {
            return false;
        }

        return true;
    }

    private function getFieldSort($excelField, $fieldArray)
    {
        $fieldSort = array();

        foreach ($excelField as $key => $value) {
            $value = $this->trim($value);

            if (in_array($value, $fieldArray)) {
                foreach ($fieldArray as $fieldKey => $fieldValue) {
                    if ($value == $fieldValue) {
                        $fieldSort[] = array("num" => $key, "fieldName" => $fieldKey);
                        break;
                    }
                }
            }
        }

        return $fieldSort;
    }

    private function getFieldArray()
    {
        $userFieldArray = array();

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        $fieldArray = array(
            "nickname"    => '用户名',
            "email"       => '邮箱',
            "password"    => '密码',
            "truename"    => '姓名',
            "gender"      => '性别',
            "idcard"      => '身份证号',
            "mobile"      => '手机号',
            "company"     => '公司',
            "job"         => '职业',
            "site"        => '个人主页',
            "weibo"       => '微博',
            "weixin"      => '微信',
            "qq"          => 'QQ',
            "classroomId" => '班级编号',
            "courseId"    => '课程编号',
            "orgCode"     => '组织机构编码'
        );

        foreach ($userFields as $userField) {
            $title = $userField['title'];

            $userFieldArray[$userField['fieldName']] = $title;
        }

        $fieldArray = array_merge($fieldArray, $userFieldArray);
        return $fieldArray;
    }

    protected function validateResult($result)
    {
        if ($result == 'success') {
            $response = true;
        } else {
            $response = false;
        }

        return $response;
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getUserImporterService()
    {
        return $this->getServiceKernel()->createService('UserImporter:UserImporter.UserImporterService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course.CourseMemberService');
    }

    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}
