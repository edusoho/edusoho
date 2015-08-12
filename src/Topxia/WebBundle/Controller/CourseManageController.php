<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\NumberToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Service\Util\LiveClientFactory;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CourseManageController extends BaseController
{
	public function indexAction(Request $request, $id)
	{      
        $course = $this->getCourseService()->tryManageCourse($id);
        if ($course['locked'] == '1') {
            return $this->redirect($this->generateUrl('course_manage_course_sync',array('id' => $id,'type'=>'base')));
        }
        return $this->forward('TopxiaWebBundle:CourseManage:base',  array('id' => $id));
	}

	public function baseAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
        $courseSetting = $this->getSettingService()->get('course', array());
	    if($request->getMethod() == 'POST'){
            $data = $request->request->all();
            $this->getCourseService()->updateCourse($id, $data);
            $this->setFlashMessage('success', '课程基本信息已保存！');
            return $this->redirect($this->generateUrl('course_manage_base',array('id' => $id))); 
        }

        $tags = $this->getTagService()->findTagsByIds($course['tags']);
        $default = $this->getSettingService()->get('default', array());

		return $this->render('TopxiaWebBundle:CourseManage:base.html.twig', array(
			'course' => $course,
            'tags' => ArrayToolkit::column($tags, 'name'),
            'default'=> $default
		));
	}

    public function nicknameCheckAction(Request $request, $courseId)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该用户还不存在！');
        } else {
            $user = $this->getUserService()->getUserByNickname($nickname);
            $isCourseStudent = $this->getCourseService()->isCourseStudent($courseId, $user['id']);
            if($isCourseStudent){
                $response = array('success' => false, 'message' => '该用户已是本课程的学员了！');
            } else {
                $response = array('success' => true, 'message' => '');
            }
            
            $isCourseTeacher = $this->getCourseService()->isCourseTeacher($courseId, $user['id']);
            if($isCourseTeacher){
                $response = array('success' => false, 'message' => '该用户是本课程的教师，不能添加!');
            }
        }
        return $this->createJsonResponse($response);
    }

	public function detailAction(Request $request, $id)
	{
        
		$course = $this->getCourseService()->tryManageCourse($id);

	    if($request->getMethod() == 'POST'){
            $detail = $request->request->all();
            $detail['goals'] = (empty($detail['goals']) || !is_array($detail['goals'])) ? array() : $detail['goals'];
            $detail['audiences'] = (empty($detail['audiences']) || !is_array($detail['audiences'])) ? array() : $detail['audiences'];

            $this->getCourseService()->updateCourse($id, $detail);
            $this->setFlashMessage('success', '课程详细信息已保存！');

            return $this->redirect($this->generateUrl('course_manage_detail',array('id' => $id))); 
        }

		return $this->render('TopxiaWebBundle:CourseManage:detail.html.twig', array(
			'course' => $course
		));
	}

	public function pictureAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

		return $this->render('TopxiaWebBundle:CourseManage:picture.html.twig', array(
			'course' => $course,
		));
	}

    public function pictureCropAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        if($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $this->getCourseService()->changeCoursePicture($course['id'], $data["images"]);
            return $this->redirect($this->generateUrl('course_manage_picture', array('id' => $course['id'])));
        }

        $fileId = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render('TopxiaWebBundle:CourseManage:picture-crop.html.twig', array(
            'course' => $course,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function priceAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $canModifyPrice = true;
        $teacherModifyPrice = $this->setting('course.teacher_modify_price', true);
        if (empty($teacherModifyPrice)) {
            if (!$this->getCurrentUser()->isAdmin()) {
                $canModifyPrice = false;
                goto response;
            }
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if (isset($fields['coinPrice'])) {
                $this->getCourseService()->setCoursePrice($course['id'], 'coin', $fields['coinPrice']);
                unset($fields['coinPrice']);
            }

            if (isset($fields['price'])) {
                $this->getCourseService()->setCoursePrice($course['id'], 'default', $fields['price']);
                unset($fields['price']);
            }

            if (!empty($fields)) {
                $course = $this->getCourseService()->updateCourse($id, $fields);
            } else {
                $course = $this->getCourseService()->getCourse($id);
            }

            $this->setFlashMessage('success', '课程价格已经修改成功!');
        }

        response:

        if ($this->isPluginInstalled("Vip") && $this->setting('vip.enabled')) {
            $levels = $this->getLevelService()->findEnabledLevels();
        } else {
            $levels = array();
        }

        if (($course['discountId'] > 0)&&($this->isPluginInstalled("Discount"))) {
            $discount = $this->getDiscountService()->getDiscount($course['discountId']);
        } else {
            $discount = null;
        }

        return $this->render('TopxiaWebBundle:CourseManage:price.html.twig', array(
            'course' => $course,
            'canModifyPrice' => $canModifyPrice,
            'levels' => $this->makeLevelChoices($levels),
            'discount' => $discount,
        ));
    }

    public function dataAction($id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $isLearnedNum=$this->getCourseService()->searchMemberCount(array('isLearned'=>1,'courseId'=>$id));

        $learnTime=$this->getCourseService()->searchLearnTime(array('courseId'=>$id));
        $learnTime=$course["studentNum"]==0 ? 0 : intval($learnTime/$course["studentNum"]);

        $noteCount=$this->getNoteService()->searchNoteCount(array('courseId'=>$id));

        $questionCount=$this->getThreadService()->searchThreadCount(array('courseId'=>$id,'type'=>'question'));

        $lessons=$this->getCourseService()->searchLessons(array('courseId'=>$id),array('createdTime', 'ASC'),0,1000);

        foreach ($lessons as $key => $value) {
            $lessonLearnedNum=$this->getCourseService()->findLearnsCountByLessonId($value['id']);

            $finishedNum=$this->getCourseService()->searchLearnCount(array('status'=>'finished','lessonId'=>$value['id']));
            
            $lessonLearnTime=$this->getCourseService()->searchLearnTime(array('lessonId'=>$value['id']));
            $lessonLearnTime=$lessonLearnedNum==0 ? 0 : intval($lessonLearnTime/$lessonLearnedNum);

            $lessonWatchTime=$this->getCourseService()->searchWatchTime(array('lessonId'=>$value['id']));
            $lessonWatchTime=$lessonWatchTime==0 ? 0 : intval($lessonWatchTime/$lessonLearnedNum);

            $lessons[$key]['LearnedNum']=$lessonLearnedNum;
            $lessons[$key]['length']=intval($lessons[$key]['length']/60);
            $lessons[$key]['finishedNum']=$finishedNum;
            $lessons[$key]['learnTime']=$lessonLearnTime;
            $lessons[$key]['watchTime']=$lessonWatchTime;

            if($value['type']=='testpaper'){
                $paperId=$value['mediaId'];
                $score=$this->getTestpaperService()->searchTestpapersScore(array('testId'=>$paperId));
                $paperNum=$this->getTestpaperService()->searchTestpaperResultsCount(array('testId'=>$paperId));
                
                $lessons[$key]['score']=$finishedNum==0 ? 0 : intval($score/$paperNum);
            }
        }

        return $this->render('TopxiaWebBundle:CourseManage:learning-data.html.twig', array(
            'course' => $course,
            'isLearnedNum'=>$isLearnedNum,
            'learnTime'=>$learnTime,
            'noteCount'=>$noteCount,
            'questionCount'=>$questionCount,
            'lessons'=>$lessons,
        ));
    }

    protected function makeLevelChoices($levels)
    {
        $choices = array();
        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }
        return $choices;
    }

    public function teachersAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        if($request->getMethod() == 'POST'){
        	
            $data = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);

            $teachers = array();
            foreach ($data['ids'] as $teacherId) {
            	$teachers[] = array(
            		'id' => $teacherId,
            		'isVisible' => empty($data['visible_' . $teacherId]) ? 0 : 1
        		);
            }

            $this->getCourseService()->setCourseTeachers($id, $teachers);

            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($id);
            if ($classroomIds) {
                $this->getClassroomService()->updateClassroomTeachers($classroomIds[0]);
            }

            $this->setFlashMessage('success', '教师设置成功！');

            return $this->redirect($this->generateUrl('course_manage_teachers',array('id' => $id))); 
        }

        $teacherMembers = $this->getCourseService()->findCourseTeachers($id);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($teacherMembers, 'userId'));

        $teachers = array();
        foreach ($teacherMembers as $member) {
        	if (empty($users[$member['userId']])) {
        		continue;
        	}
        	$teachers[] = array(
                'id' => $member['userId'],
        		'nickname' => $users[$member['userId']]['nickname'],
                'avatar'  => $this->getWebExtension()->getFilePath($users[$member['userId']]['smallAvatar'], 'avatar.png'),
        		'isVisible' => $member['isVisible'] ? true : false,
    		);
        }
        return $this->render('TopxiaWebBundle:CourseManage:teachers.html.twig', array(
            'course' => $course,
            'teachers' => $teachers
        ));
    }

    public function publishAction(Request $request, $id)
    {
    	$this->getCourseService()->publishCourse($id);
    	return $this->createJsonResponse(true);
    }

    public function teachersMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');
        $users = $this->getUserService()->searchUsers(array('nickname'=>$likeString, 'roles'=> 'ROLE_TEACHER'), array('createdTime', 'DESC'), 0, 10);

        $teachers = array();
        foreach ($users as $user) {
            $teachers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
                'isVisible' => 1
            );
        }

        return $this->createJsonResponse($teachers);
    }

    #课程同步
    public function courseSyncAction(Request $request,$id,$type)
    {
        $courseId = $id;
        $course = $this->getCourseService()->getCourse($courseId);
        $parentCourse = $this->getCourseService()->getCourse($course['parentId']);
        $type = $type;
        $title = '';
        $url = '';
        switch ($type) {
            case 'base':
                $title = '基本信息';
                $url= 'course_manage_base';
                break;
            case 'detail':
                $title = '详细信息';
                $url= 'course_manage_detail';
                break;
            case 'picture':
                $title = '课程图片';
                $url= 'course_manage_picture';
                break;
            case 'lesson':
                $title = '课时管理';
                $url= 'course_manage_lesson';
                break;
            case 'price':
                $title = '价格设置';
                $url= 'course_manage_price';
                break;
            case 'teachers':
                $title = '教师设置';
                $url= 'course_manage_teachers';
                break;
            case 'question':
                $title = '题目管理';
                $url= 'course_manage_question';
                break;
            case 'testpaper':
                $title = '试卷管理';
                $url= 'course_manage_testpaper';
                break;
            default:
                $title = '未知页面';
                $url= '';
                break;
        }

        $course = $this->getCourseService()->tryManageCourse($courseId);
        return $this->render('TopxiaWebBundle:CourseManage:courseSync.html.twig',array(
        'course'=>$course,
        'type'=>$type,
        'title'=>$title,
        'url'=>$url,
        'parentCourse' =>$parentCourse
        ));
    }

    public function courseSyncEditAction(Request $request)
    {
        $courseId = $request->query->get('courseId');
        $course = $this->getCourseService()->getCourse($courseId);
        $type = $request->query->get('type');
        $url = $request->query->get('url');
        if ($request->getMethod() == 'POST'){
            $courseId = $request->request->get('courseId');
            $url = $request->request->get('url');
            $course = $this->getCourseService()->getCourse($courseId);
            if($course['locked'] == 1) {
              $this->getCourseService()->updateCourse($courseId,array('locked'=>0));
              $this->getCourseService()->updateLessonByCourseId($courseId,array('parentId'=>0));
              $this->getCourseService()->updateChapterByCourseId($courseId,array('pId'=>0));
              $this->getMaterialService()->updateMaterialByCourseId($courseId,array('pId'=>0));
              $this->getQuestionService()->updateQuestionByTarget('course-'.$courseId,array('pId'=>0));
              $this->getTestpaperService()->updateTestpaperAndTestpaperItemByTarget($courseId,array('pId'=>0));
              if ($this->isPluginInstalled('Homework')) {
                $this->getHomeworkService()->updateHomeworkAndHomeworkItemByCourseId($courseId,array('pId'=>0));
                $this->getExerciseService()->updateExerciseByCourseId($courseId,array('pId'=>0));
              }
            }
          return $this->createJsonResponse($url);
        }
        return $this->render('TopxiaWebBundle:CourseManage:courseSyncEdit.html.twig',array(
        'course'=>$course,
        'type'=>$type,
        'url'=>$url
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
    
    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    } 

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
    protected function getDiscountService()
    {
        return $this->getServiceKernel()->createService('Discount:Discount.DiscountService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }
}