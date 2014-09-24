<?php

namespace Topxia\MobileBundleV2\Service;

class BaseService {
    public $formData;
    public $controller;
    public $request;
    protected $delegator;
    private function __construct($controller) {
        $this->controller = $controller;
        $this->request = $controller->request;
        $this->formData = $controller->formData;
    }
    public static function getInstance($class, $controller) {
        $instance = new $class($controller);
        $serviceDelegator = new serviceDelegator($instance);
        $instance->setDelegator($serviceDelegator);
        return $serviceDelegator;
    }
    protected function getParam($name, $default = null) {
        $result = $this->request->request->get($name, $default);
        return $result;
    }

    protected function setParam($name, $value)
    {
        $this->request->request->set($name, $value);
    }

    public function setDelegator($serviceDelegator) {
        $this->delegator = $serviceDelegator;
    }
    public function getDelegator() {
        return $this->delegator;
    }
    public function after() {
    }
    public function before() {
    }

    protected function getContainer()
    {
        return $this->controller->getContainer();
    }

    protected function getFileService()
    {
        return $this->controller->getService('Content.FileService');
    }

    protected function getCourseService()
    {
        return $this->controller->getService('Course.CourseService');
    }

    protected function getTestpaperService()
    {
        return $this->controller->getService('Testpaper.TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->controller->getService('Question.QuestionService');
    }

    public function createErrorResponse($name, $message) {
        $error = array(
            'error' => array(
                'name' => $name,
                'message' => $message
            )
        );
        return $error;
    }
    protected function previewAsMember($member, $courseId, $user) {
        if (empty($member)) {
            return null;
        }
        if ($this->controller->get('security.context')->isGranted('ROLE_ADMIN')) {
            return array(
                'id' => 0,
                'courseId' => $courseId,
                'userId' => $user['id'],
                'levelId' => 0,
                'learnedNum' => 0,
                'isLearned' => 0,
                'seq' => 0,
                'isVisible' => 0,
                'role' => 'teacher',
                'locked' => 0,
                'createdTime' => time() ,
                'deadline' => 0
            );
        }
        $userIsStudent = $this->controller->getCourseService()->isCourseStudent($courseId, $user['id']);
        $userIsTeacher = $this->controller->getCourseService()->isCourseTeacher($courseId, $user['id']);
        $member['createdTime'] = date('c', $member['createdTime']);
        $member['role'] = $userIsTeacher ? "teacher" : $userIsStudent ? "student" : null;
        return $member;
    }
    public function array2Map($learnCourses) {
        $mapCourses = array();
        if (empty($learnCourses)) {
            return $mapCourses;
        }
        foreach ($learnCourses as $key => $learnCourse) {
            $mapCourses[$learnCourse['id']] = $learnCourse;
        }
        return $mapCourses;
    }
    protected function getSiteInfo($request) {
        $site = $this->controller->getSettingService()->get('site', array());
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        if (!empty($mobile['logo'])) {
            $logo = $request->getSchemeAndHttpHost() . '/' . $mobile['logo'];
        } else {
            $logo = '';
        }
        $splashs = array();
        for ($i = 1; $i < 5; $i++) {
            if (!empty($mobile['splash' . $i])) {
                $splashs[] = $request->getSchemeAndHttpHost() . '/' . $mobile['splash' . $i];
            }
        }
        return array(
            'name' => $site['name'],
            'url' => $request->getSchemeAndHttpHost() . '/mapi_v2',
            'host' => $request->getSchemeAndHttpHost() ,
            'logo' => $logo,
            'splashs' => $splashs,
            'apiVersionRange' => array(
                "min" => "2.0.0",
                "max" => "2.0.0"
            ) ,
        );
    }
}

