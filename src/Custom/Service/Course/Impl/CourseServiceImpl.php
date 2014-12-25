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

	public function updateCourse($id, $fields)
	{
		$course = $this->getCourseDao()->getCourse($id);
		if (empty($course)) {
			throw $this->createServiceException('课程不存在，更新失败！');
		}
		if (isset($fields['freeStartTime'])) {
    		$fields['freeStartTime'] = strtotime($fields['freeStartTime']);
    	}
    	if (isset($fields['freeEndTime'])) {
    		$fields['freeEndTime'] = strtotime($fields['freeEndTime']);
    	}
    

		$fields = $this->_filterCourseFields($fields);


		$this->getLogService()->info('course', 'update', "更新课程《{$course['title']}》(#{$course['id']})的信息", $fields);

		$fields = CourseSerialize::serialize($fields);

		return CourseSerialize::unserialize(
			$this->getCourseDao()->updateCourse($id, $fields)
		);
	}

	public function favoriteCourse($courseId)
	{
		$user = $this->getCurrentUser();
		if (empty($user['id'])) {
			throw $this->createAccessDeniedException();
		}

		$course = $this->getCourse($courseId);
		if($course['status']!='published'){
			throw $this->createServiceException('不能收藏未发布课程');
		}

		if (empty($course)) {
			throw $this->createServiceException("该课程不存在,收藏失败!");
		}

		$favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id']);
		if($favorite){
			throw $this->createServiceException("该收藏已经存在，请不要重复收藏!");
		}

		$this->getFavoriteDao()->addFavorite(array(
			'courseId'=>$course['id'],
			'userId'=>$user['id'], 
			'createdTime'=>time()
		));
		//添加动态
		$this->getStatusService()->publishStatus(array(
			'type' => 'favorite_course',
			'objectType' => 'course',
			'objectId' => $courseId,
			'properties' => array(
				'course' => $this->simplifyCousrse($course),
			)
		));
		return true;
	}

	public function findCourseChaptersByType($courseId,$type)
	{
		$chapters=$this->getChapterDao()->findChaptersByCourseId($courseId);
		$chapters=ArrayToolkit::group($chapters,'type');
		if(!isset($chapters[$type])){
			return array();
		}

		$items=$chapters[$type];
		uasort($items, function($item1, $item2){
			return $item1['seq'] > $item2['seq'];
		});
		return $items;
	}

	private function simplifyCousrse($course)
	{
		return array(
			'id' => $course['id'],
			'title' => $course['title'],
			'picture' => $course['middlePicture'],
			'type' => $course['type'],
			'rating' => $course['rating'],
			'about' => StringToolkit::plain($course['about'], 100),
			'price' => $course['price'],
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
			'complexity'=>'',
			'originalPrice'=>'',
			'isFree'=>'none',
			'discount'=>null,
			'columns'=>''

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
		if (!empty($fields['columns'])) {
			$fields['columns'] = explode(',', $fields['columns']);
			$fields['columns'] = $this->getColumnService()->findColumnsByNames($fields['columns']);
			array_walk($fields['columns'], function(&$item, $key) {
				$item = (int) $item['id'];
			});
		}
		return $fields;
	}

	public function updateChapter($courseId, $chapterId, $fields)
	{
		$chapter = $this->getChapter($courseId, $chapterId);
		if (empty($chapter)) {
			throw $this->createServiceException("章节#{$chapterId}不存在！");
		}
		$fields = ArrayToolkit::parts($fields, array('title','description'));
		return $this->getChapterDao()->updateChapter($chapterId, $fields);
	}

    private function getCourseDao ()
    {
        return $this->createDao('Course.CourseDao');
    }
    private function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }
    private function getFavoriteDao ()
    {
        return $this->createDao('Course.FavoriteDao');
    }
    private function getStatusService()
    {
        return $this->createService('User.StatusService');
    }
    private function getColumnService()
    {
        return $this->createService('Custom:Taxonomy.ColumnService');
    }

    private function getChapterDao()
    {
        return $this->createDao('Course.CourseChapterDao');
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
    	if (isset($course['columns'])) {
    		if (is_array($course['columns']) and !empty($course['columns'])) {
    			$course['columns'] = '|' . implode('|', $course['columns']) . '|';
    		} else {
    			$course['columns'] = null;
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
		$course['columns'] = empty($course['columns']) ? array() : explode('|', trim($course['columns'], '|'));

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