<?php

namespace Custom\Service\Course\Impl;

use Topxia\Service\Course\Impl\CourseServiceImpl as BaseCourseServiceImpl;
use Custom\Service\Course\CourseService;
use Custom\Service\Course\Enum\LessonPermissions;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Common\ArrayToolkit;

// this is a sample

class CourseServiceImpl extends BaseCourseServiceImpl implements CourseService
{
    public function customUpdateCourse($id, $fields)
    {
        var_dump('custom updatacourse');
        $course = $this->getCourseDao()->getCourse($id);
        if (empty($course)) {
            throw $this->createServiceException('课程不存在，更新失败！');
        }

        $fields = $this->_customFilterCourseFields($fields);

        $this->getLogService()->info('course', 'update', "更新课程《{$course['title']}》(#{$course['id']})的信息", $fields);

        $fields = CourseSerialize::serialize($fields);

        $updatedCourse = $this->getCourseDao()->updateCourse($id, $fields);

        return CourseSerialize::unserialize($updatedCourse);
    }

    protected function _customFilterCourseFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title' => '',
            'subtitle' => '',
            'about' => '',
            'expiryDay' => 0,
            'serializeMode' => 'none',
            'categoryId' => 0,
            'vipLevelId' => 0,
            'goals' => array(),
            'audiences' => array(),
            'tags' => '',
            'startTime' => 0,
            'endTime'  => 0,
            'rootId' => 0,
            'locationId' => 0,
            'address' => '',
            'maxStudentNum' => 0,
            'watchLimit' => 0,
            'approval' => 0,
        ));

        if (!empty($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about'],true);
        }

        if (!empty($fields['tags'])) {
            $fields['tags'] = explode(',', $fields['tags']);
            $fields['tags'] = $this->getTagService()->findTagsByNames($fields['tags']);
            array_walk($fields['tags'], function(&$item, $key) {
                $item = (int) $item['id'];
            });
        }
        return $fields;
    }

	public function findOtherPeriods($courseId){
		$course=$this->getCourseDao()->getCourse($courseId);

		$courses = CourseSerialize::unserializes(
            $this->getCourseDao()->findOtherPeriods($course)
        );

        return ArrayToolkit::index($courses, 'periods');
	}
}


class CourseSerialize
{
    public static function serialize(array &$course)
    {
    	if (isset($course['tags'])) {
    		if (is_array($course['tags']) && !empty($course['tags'])) {
    			$course['tags'] = '|' . implode('|', $course['tags']) . '|';
    		} else {
    			$course['tags'] = '';
    		}
    	}
    	
    	if (isset($course['goals'])) {
    		if (is_array($course['goals']) && !empty($course['goals'])) {
    			$course['goals'] = '|' . implode('|', $course['goals']) . '|';
    		} else {
    			$course['goals'] = '';
    		}
    	}

    	if (isset($course['audiences'])) {
    		if (is_array($course['audiences']) && !empty($course['audiences'])) {
    			$course['audiences'] = '|' . implode('|', $course['audiences']) . '|';
    		} else {
    			$course['audiences'] = '';
    		}
    	}

    	if (isset($course['teacherIds'])) {
    		if (is_array($course['teacherIds']) && !empty($course['teacherIds'])) {
    			$course['teacherIds'] = '|' . implode('|', $course['teacherIds']) . '|';
    		} else {
    			$course['teacherIds'] = null;
    		}
    	}

        return $course;
    }


    public static function unserialize(array $course = null)
    {
        if (empty($course)) {
            return $course;
        }

        $course['tags'] = empty($course['tags']) ? array() : explode('|', trim($course['tags'], '|'));

        if(empty($course['goals'] )) {
            $course['goals'] = array();
        } else {
            $course['goals'] = explode('|', trim($course['goals'], '|'));
        }

        if(empty($course['audiences'] )) {
            $course['audiences'] = array();
        } else {
            $course['audiences'] = explode('|', trim($course['audiences'], '|'));
        }

        if(empty($course['teacherIds'] )) {
            $course['teacherIds'] = array();
        } else {
            $course['teacherIds'] = explode('|', trim($course['teacherIds'], '|'));
        }

        return $course;

    }

    public static function unserializes(array $courses)
    {
        return array_map(function($course) {
            return CourseSerialize::unserialize($course);
        }, $courses);
    }
}