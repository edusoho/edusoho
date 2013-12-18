<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Service\Common\ServiceKernel;

abstract class CourseBaseDataTag extends BaseDataTag implements DataTag  
{

	protected function getTeachers(array $ids)
    {   
        $teachers = $this->getUserService()->findUsersByIds($ids);
        foreach ($teachers as $key => &$teacherValues) {
          $teacherValues['password']= null;
          $teacherValues['salt']= null;
        }
        
        return $teachers;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    protected function checkUserId(array $arguments)
    {
        if (empty($arguments['userId'])) {
            throw new \InvalidArgumentException("userId参数缺失");            
        }
    }

    protected function checkCount(array $arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException("count参数缺失");
        }
        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException("count参数超出最大取值范围");
        }
    }

    protected function checkCourseId(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            throw new \InvalidArgumentException("courseId参数缺失");
        }
    }

    protected function checkCourseArguments(array $arguments)
    {
        if (empty($arguments['courseId'])){
            $conditions = array();
        } else {
            $conditions = array('courseId' => $arguments['courseId']);
        }
        return $conditions;
    }

    protected function checkThreadId(array $arguments)
    {
        if (empty($arguments['threadId'])) {
            throw new \InvalidArgumentException("threadId参数缺失");
        }
    }

    protected function checkReviewId(array $arguments)
    {
        if (empty($arguments['reviewId'])) {
            throw new \InvalidArgumentException("reviewId参数缺失");
        }
    }

    protected function checkCategoryId(array $arguments)
    {
        if (empty($arguments['categoryId'])) {
            throw new \InvalidArgumentException("categoryId参数缺失");
        }
    }

    protected function checkGroupId(array $arguments)
    {
        if (empty($arguments['group'])) {
            throw new \InvalidArgumentException("group参数缺失");
        }
    }
 	protected function foreachCourses($courses)
    {

	    foreach ($courses as $key => &$courseValues) {
            $courseValues['password'] = null;
            $courseValues['salt'] = null;
	        $courseValues['teachers'] = $this->getTeachers($courseValues['teacherIds']);
            
	        if ($courseValues['categoryId'] != '0') {
	            $courseValues['category'] = $this->getCategoryService()->getCategory($courseValues['categoryId']);
	        }

	    }
		return $courses;
	}

    protected function foreachReviews($courseReviews)
    {
        foreach ($courseReviews as $key => &$ReviewValues) {
            $ReviewValues['reviewer'] = $this->getUserService()->getUser($ReviewValues['userId']);
            $ReviewValues['reviewer']['password'] = NULL;
            $ReviewValues['reviewer']['salt'] = NULL;
            $ReviewValues['course'] = $this->getCourseService()->getCourse($ReviewValues['courseId']);

        }
        return $courseReviews;
    }

    protected function foreachUsers($users)
    {
        foreach ($users as $key => &$uservalues) {
            $uservalues['password'] = NULL;
            $uservalues['salt'] = NULL;
        }
        return $users;
    }

}