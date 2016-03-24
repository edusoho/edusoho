<?php

namespace Mooc\WebBundle\Controller;

use PHPExcel_Cell;
use PHPExcel_IOFactory;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Mooc\Common\SimpleValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseScoreBatchController extends BaseController
{
    public function importAction($id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        return $this->render('MoocWebBundle:CourseScoreBatch:import.html.twig', array(
            'course' => $course
        ));
    }

    public function excelDataImportAction($id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        if ('published' != $course['status']) {
            throw $this->createNotFoundException("未发布课程不能导入学员成绩!");
        }

        return $this->render('MoocWebBundle:CourseScoreBatch:import.step3.html.twig', array(
            'course' => $course
        ));
    }

    public function importExcelDataAction(Request $request, $id)
    {
        $course            = $this->getCourseService()->tryManageCourse($id);
        $courseScoreSeting = $this->getCourseScoreService()->getScoreSettingByCourseId($id);
        $usersData         = $request->request->get("data");
        $usersData         = json_decode($usersData, true);

        $count = array('successCount' => 0, 'updateUserCount' => 0);

        foreach ($usersData as $key => $userData) {
            $user = $this->getUserService()->getUserByStaffNo($userData['staffNo']);

            $userScore = $this->getCourseScoreService()->getUserScoreByUserIdAndCourseId($user['id'], $course['id']);

            if ($userScore) {
                //update
                $user = $this->getCourseScoreService()->updateUserCourseScore($userScore['id'], $userData);

                if (!empty($userScore['importOtherScore'])) {
                    if ($user) {
                        $count['updateUserCount']++;
                    }
                } else {
                    if ($user) {
                        $count['successCount']++;
                    }
                }
            } else {
                //insert
                $userData['userId']   = $user['id'];
                $userData['courseId'] = $course['id'];
                $user                 = $this->getCourseScoreService()->addUserCourseScore($userData);

                if ($user) {
                    $count['successCount']++;
                }
            }
        }

        return $this->render('MoocWebBundle:CourseScoreBatch:import.finish.html.twig', array(
            'course' => $course,
            'count'  => $count
        ));
    }

    public function ValidateExcelInfoAction(Request $request, $courseId)
    {
        $course                     = $this->getCourseService()->tryManageCourse($courseId);
        $data                       = array();
        $data['excel_example']      = 'bundles/moocweb/example/user_score_import_example.xlsx';
        $data['excel_validate_url'] = 'course_manage_student_score_import';
        $data['excel_import_url']   = 'course_manage_student_score_to_base';

        if ($request->getMethod() == 'POST') {
            $checkType   = $request->request->get("rule");
            $file        = $request->files->get('excel');
            $errorInfo   = array();
            $checkInfo   = array();
            $userCount   = 0;
            $allUserData = array();

            if (!is_object($file)) {
                $this->setFlashMessage('danger', '请选择上传的文件');

                return $this->render('MoocWebBundle:CourseScoreBatch:import.step1.html.twig', array(
                    'course' => $course,
                    'data'   => $data
                ));
            }

            if (FileToolkit::validateFileExtension($file, 'xls xlsx')) {
                $this->setFlashMessage('danger', 'Excel格式不正确！');

                return $this->render('MoocWebBundle:CourseScoreBatch:import.step1.html.twig', array(
                    'course' => $course,
                    'data'   => $data
                ));
            }

            $objPHPExcel  = PHPExcel_IOFactory::load($file);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow   = $objWorksheet->getHighestRow();

            $highestColumn      = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

            if ($highestRow > 1000) {
                $this->setFlashMessage('danger', 'Excel超过1000行数据!');

                return $this->render('MoocWebBundle:CourseScoreBatch:import.step1.html.twig', array(
                    'course' => $course,
                    'data'   => $data
                ));
            }

            $fieldArray = $this->getFieldArray();

            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $fieldTitle = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
                $strs[$col] = $fieldTitle."";
            }

            $excelField = $strs;

            if (!$this->checkNecessaryFields($excelField)) {
                $this->setFlashMessage('danger', '缺少必要的字段');

                return $this->render('MoocWebBundle:CourseScoreBatch:import.step1.html.twig', array(
                    'course' => $course,
                    'data'   => $data
                ));
            }

            $fieldSort = $this->getFieldSort($excelField, $fieldArray);
            unset($fieldArray, $excelField);

            $repeatInfo = $this->checkRepeatData($row = 3, $fieldSort, $highestRow, $objWorksheet);

            if ($repeatInfo) {
                $errorInfo[] = $repeatInfo;
                return $this->render('MoocWebBundle:CourseScoreBatch:import.step2.html.twig', array(
                    "errorInfo" => $errorInfo,
                    'data'      => $data,
                    'course'    => $course
                ));
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

                $emptyData = array_count_values($userData);

                if (isset($emptyData[""]) && count($userData) == $emptyData[""]) {
                    $checkInfo[] = "第".$row."行为空行，已跳过";
                    continue;
                }

//字段信息校验

                if ($this->validFields($userData, $row, $fieldCol, $course['id'])) {
                    $errorInfo = array_merge($errorInfo, $this->validFields($userData, $row, $fieldCol, $course['id']));
                    continue;
                }

                $user      = $this->getUserService()->getUserByStaffNo($userData['staffNo']);
                $userScore = $this->getCourseScoreService()->getUserScoreByUserIdAndCourseId($user['id'], $course['id']);

                if (!empty($userData['truename']) && $userScore) {
                    if ($userScore['importOtherScore']) {
                        $checkInfo[] = "第".$row."行的用户评分已存在，将会更新";
                    }

                    $userCount     = $userCount + 1;
                    $allUserData[] = $userData;
                    continue;
                }

                $userCount = $userCount + 1;

                $allUserData[] = $userData;
                unset($userData);
            }

            $allUserData = json_encode($allUserData);

            return $this->render('MoocWebBundle:CourseScoreBatch:import.step2.html.twig', array(
                'userCount'   => $userCount,
                'errorInfo'   => $errorInfo,
                'checkInfo'   => $checkInfo,
                'allUserData' => $allUserData,
                'checkType'   => $checkType,
                'course'      => $course,
                'data'        => $data
            ));
        }

        return $this->render('MoocWebBundle:CourseScoreBatch:import.step1.html.twig', array(
            'course' => $course,
            'data'   => $data
        ));
    }

    private function validFields($userData, $row, $fieldCol, $courseId)
    {
        $errorInfo = array();

        if (!isset($userData['truename']) || empty($userData['truename'])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["truename"]." 列 的数据存在问题，请检查。";
        }

        if (!isset($userData['staffNo']) || empty($userData['staffNo'])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["staffNo"]." 列 的数据存在问题，请检查。";
        } elseif (isset($userData['staffNo']) && "" != $userData['staffNo'] && !SimpleValidator::staffNo($userData['staffNo'])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["staffNo"]." 列 的数据存在问题，请检查。";
        }

        if (!isset($userData['importOtherScore']) || empty($userData['importOtherScore'])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["importOtherScore"]." 列 的数据存在问题，请检查。";
        }

        $user = $this->getUserService()->getUserByStaffNo($userData['staffNo']);
        $user = $this->getUserService()->getUserProfile($user['id']);

        if (empty($user)) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["staffNo"]." 列 的数据存在问题，该学号对应的学员不存在。";
        } else {
            if ($userData['truename'] != $user['truename']) {
                $errorInfo[] = "第 ".$row."行".$fieldCol["importOtherScore"]." 列 的数据存在问题，该学号对应的学员用户名不匹配。";
            }
        }

        //校验是否是课程学员
        $courseMember = $this->getCourseService()->getCourseMember($courseId, $user['id']);

        if (empty($courseMember)) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["truename"]." 列 的数据存在问题，该用户不是课程学员。";
        }

        return $errorInfo;
    }

    private function checkRepeatData($row, $fieldSort, $highestRow, $objWorksheet)
    {
        $errorInfo      = array();
        $allStaffNoData = array();

        foreach ($fieldSort as $key => $value) {
            if ("staffNo" == $value["fieldName"]) {
                $staffNoCol = $value["num"];
            }
        }

        for ($row; $row <= $highestRow; $row++) {
            $staffNoColData = $objWorksheet->getCellByColumnAndRow($staffNoCol, $row)->getValue();

            if ($staffNoColData."" == "") {
                continue;
            }

            $allStaffNoData[] = $staffNoColData."";
        }

        $errorInfo = $this->arrayRepeat($allStaffNoData);

        return $errorInfo;
    }

    private function arrayRepeat($array)
    {
        $repeatArray      = array();
        $repeatArrayCount = array_count_values($array);
        $repeatRow        = "";

        foreach ($repeatArrayCount as $key => $value) {
            if ($value > 1) {
                $repeatRow .= "重复:<br>";

                for ($i = 1; $i <= $value; $i++) {
                    $row = array_search($key, $array) + 3;
                    $repeatRow .= "第".$row."行"."    ".$key."<br>";
                    unset($array[$row - 3]);
                }
            }
        }

        return $repeatRow;
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

    private function checkNecessaryFields($data)
    {
        $data = implode("", $data);
        $data = $this->trim($data);

        $trueNameArray = explode("学员姓名", $data);

        if (count($trueNameArray) <= 1) {
            return false;
        }

        $staffNoArray = explode("学号", $data);

        if (count($staffNoArray) <= 1) {
            return false;
        }

        $scoreArray = explode("其他", $data);

        if (count($scoreArray) <= 1) {
            return false;
        }

        return true;
    }

    private function trim($data)
    {
        $data = trim($data);
        $data = str_replace(" ", "", $data);
        $data = str_replace('\n', '', $data);
        $data = str_replace('\r', '', $data);
        $data = str_replace('\t', '', $data);

        return $data;
    }

    private function getFieldArray()
    {
        $userFieldArray = array();

        $fieldArray = array(
            "truename"         => '学员姓名',
            "staffNo"          => '学号',
            "importOtherScore" => '其他'
        );

        return $fieldArray;
    }

    //批量导出
    public function exportCsvAction(Request $request, $id)
    {
        $courseSetting = $this->getSettingService()->get('course', array());

        if (isset($courseSetting['teacher_export_student']) && "1" == $courseSetting['teacher_export_student']) {
            $course = $this->getCourseService()->tryManageCourse($id);
        } else {
            $course = $this->getCourseService()->tryAdminCourse($id);
        }

        $parameters             = $request->query->all();
        $str                    = "学员姓名,学号,院系/专业,总成绩,考试成绩,作业成绩,其他,学分";
        $conditions             = array();
        $conditions['courseId'] = $course['id'];

//学号筛选

        if (empty($parameters) || (isset($parameters) && empty($parameters['staffNo']) && empty($parameters['organizationId']))) {
            $users = $this->getCourseScoreService()->findAllMemberScore($course['id']);
        } else {
            $organizationIds = $this->getOrganizationService()->findOrganizationChildrenIds($parameters['organizationId']);
            $fields          = array(
                'staffNo'         => $parameters['staffNo'],
                'organizationIds' => $organizationIds,
                'courseId'        => $course['id']
            );
            $users = $this->getCourseScoreService()->findUsersScoreBySqlJoinUser($fields);
        }

        $userIds = array();

        if (!empty($users)) {
            $userIds = ArrayToolkit::column($users, 'userId');
            $users   = ArrayToolkit::index($users, 'userId');
        }

        $conditions['userIds'] = $userIds;

        //后续获取学员真实姓名
        $usersProfile = $this->getUserService()->findUserProfilesByIds($conditions['userIds']);
        $usersProfile = ArrayToolkit::index($usersProfile, 'id');

        //后续获取学员成绩
        $usersScore = $this->getCourseScoreService()->findUserScoreByIdsAndCourseId($conditions['userIds'], $course['id']);
        $usersScore = ArrayToolkit::index($usersScore, 'userId');

        //院系/专业
        $roganizations = $this->getOrganizationService()->findAllOrganizations();
        $roganizations = ArrayToolkit::index($roganizations, 'id');

        //课程评分设置
        $courseScoreSeting = $this->getCourseScoreService()->getScoreSettingByCourseId($course['id']);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            1000
        );
        $totalPage = $paginator->getLastPage();

        for ($i = 1; $i <= $totalPage; $i++) {
            if (!empty($userIds)) {
                $users = $this->getUserService()->searchUsers($conditions, array('staffNo', 'ASC'), ($i - 1) * 1000, 1000);
            } else {
                $users = array();
            }

            $str .= "\r\n";
            $studentsScore = array();

            foreach ($users as $user) {
                $member = "";
                $member .= $usersProfile[$user['id']]['truename'].","; //学生姓名
                $member .= $user['staffNo'].","; //学生学号
                $roganizationId = $user['organizationId'];
                $member .= $roganizationId ? $roganizations[$roganizationId]['name'] : '不归属任何院系'; //院系/专业
                $member .= ",";
                $member .= $usersScore[$user['id']]['totalScore'].","; //总成绩
                $member .= $usersScore[$user['id']]['examScore'].","; //考试成绩
                $member .= $usersScore[$user['id']]['homeworkScore'].","; //作业成绩
                $member .= $usersScore[$user['id']]['otherScore'].","; //其他成绩
                $member .= $courseScoreSeting['standardScore'] && $usersScore[$user['id']]['totalScore'] > $courseScoreSeting['standardScore'] ? $courseScoreSeting['credit'] : 0.0;
                $studentsScore[] = $member;
            };
        }

        $str .= implode("\r\n", $studentsScore);
        $str = chr(239).chr(187).chr(191).$str;

        $filename = sprintf("course-%s-students-score-(%s).csv", $course['id'], date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCourseScoreService()
    {
        return $this->getServiceKernel()->createService('Mooc:Course.CourseScoreService');
    }

    protected function getOrganizationService()
    {
        return $this->getServiceKernel()->createService('Mooc:Organization.OrganizationService');
    }
}
