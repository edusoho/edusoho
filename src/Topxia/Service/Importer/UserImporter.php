<?php


namespace Topxia\Service\Importer;


class UserImporter extends Importer
{
    public function import($postData)
    {
        $importerData = $postData['importerData'];
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
//email,nickname,verifiedmobile只有一个能修改
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
            'status' => "success",
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
                        'title'      => "购买班级《{$classroom['title']}》(管理员添加)",
                        'targetType' => 'classroom',
                        'targetId'   => $classroom['id'],
                        'amount'     => '0.00', //暂时默认为0
                        'payment'    => 'none',
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

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course.CourseMemberService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserImporterService()
    {
        return $this->getServiceKernel()->createService('UserImporter:UserImporter.UserImporterService');
    }
}