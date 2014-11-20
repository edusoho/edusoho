<?php
namespace Custom\Service\Course\Impl;
use Topxia\Service\Course\Impl\CourseServiceImpl as BaseCourseServiceImpl;
use Topxia\Service\Course\CourseService as CourseService;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Util\LiveClientFactory;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CourseServiceImpl extends BaseCourseServiceImpl implements CourseService
{
	// public function createCourse($course)
	// {
	// 	if (!ArrayToolkit::requireds($course, array('title'))) {
	// 		throw $this->createServiceException('缺少必要字段，创建课程失败！');
	// 	}

	// 	$course = ArrayToolkit::parts($course, array('title', 'type','about', 'categoryId', 'tags', 'price', 'startTime', 'endTime', 'locationId', 'address'));

	// 	$course['status'] = 'draft';
 //        		$course['about'] = !empty($course['about']) ? $this->getHtmlPurifier()->purify($course['about']) : '';
 //        		$course['tags'] = !empty($course['tags']) ? $course['tags'] : '';
	// 	$course['userId'] = $this->getCurrentUser()->id;
	// 	$course['createdTime'] = time();
	// 	$course['teacherIds'] = array($course['userId']);
	// 	$course = $this->getCourseDao()->addCourse(CourseSerialize::serialize($course));
		
	// 	$member = array(
	// 		'courseId' => $course['id'],
	// 		'userId' => $course['userId'],
	// 		'role' => 'teacher',
	// 		'createdTime' => time(),
	// 	);

	// 	$this->getMemberDao()->addMember($member);

	// 	$course = $this->getCourse($course['id']);

	// 	$this->getLogService()->info('course', 'create', "创建课程《{$course['title']}》(#{$course['id']})");

	// 	return $course;
	// }

	public function updateCourse($id, $fields)
	{
		$course = $this->getCourseDao()->getCourse($id);
		if (empty($course)) {
			throw $this->createServiceException('课程不存在，更新失败！');
		}
		$fields = $this->_filterCourseFields($fields);

		$this->getLogService()->info('course', 'update', "更新课程《{$course['title']}》(#{$course['id']})的信息", $fields);

		$fields = CourseSerialize::serialize($fields);

		return CourseSerialize::unserialize(
			$this->getCourseDao()->updateCourse($id, $fields)
		);
	}


	private function _filterCourseFields($fields)
	{
		$fields = ArrayToolkit::filter($fields, array(
			'title' => '',
			'subtitle' => '',
			'about' => '',
			'expiryDay' => 0,
			'showStudentNumType' => 'opened',
			'serializeMode' => 'none',
			'categoryId' => 0,
			'vipLevelId' => 0,
			'goals' => array(),
			'audiences' => array(),
			'tags' => '',
			'price' => 0.00,
			'startTime' => 0,
			'endTime'  => 0,
			'locationId' => 0,
			'address' => '',
			'maxStudentNum' => 0,
			'freeStartTime' => 0,
			'freeEndTime' => 0,
			'deadlineNotify' => 'none',
			'daysOfNotifyBeforeDeadline' => 0,
			'complexity'=>''
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


    private function getCourseDao ()
    {
        return $this->createDao('Course.CourseDao');
    }
       private function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }
}

class CourseSerialize
{
    public static function serialize(array &$course)
    {
    	if (isset($course['tags'])) {
    		if (is_array($course['tags']) and !empty($course['tags'])) {
    			$course['tags'] = '|' . implode('|', $course['tags']) . '|';
    		} else {
    			$course['tags'] = '';
    		}
    	}
    	
    	if (isset($course['goals'])) {
    		if (is_array($course['goals']) and !empty($course['goals'])) {
    			$course['goals'] = '|' . implode('|', $course['goals']) . '|';
    		} else {
    			$course['goals'] = '';
    		}
    	}

    	if (isset($course['audiences'])) {
    		if (is_array($course['audiences']) and !empty($course['audiences'])) {
    			$course['audiences'] = '|' . implode('|', $course['audiences']) . '|';
    		} else {
    			$course['audiences'] = '';
    		}
    	}

    	if (isset($course['teacherIds'])) {
    		if (is_array($course['teacherIds']) and !empty($course['teacherIds'])) {
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


class LessonSerialize
{
    public static function serialize(array $lesson)
    {
        return $lesson;
    }

    public static function unserialize(array $lesson = null)
    {
        return $lesson;
    }

    public static function unserializes(array $lessons)
    {
    	return array_map(function($lesson) {
    		return LessonSerialize::unserialize($lesson);
    	}, $lessons);
    }
}