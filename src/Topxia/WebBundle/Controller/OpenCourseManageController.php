<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $openCourse = $this->getOpenCourseService()->getCourse($id);

        return $this->forward('TopxiaWebBundle:OpenCourseManage:base', array('id' => $id));
    }

    public function baseAction(Request $request, $id)
    {
        //$course        = $this->getCourseService()->tryManageCourse($id);
        $course        = $this->getOpenCourseService()->getCourse($id);
        $courseSetting = $this->getSettingService()->get('course', array());

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $this->getOpenCourseService()->updateCourse($id, $data);
            $this->setFlashMessage('success', '课程基本信息已保存！');
            return $this->redirect($this->generateUrl('open_course_manage_base', array('id' => $id)));
        }

        $tags    = $this->getTagService()->findTagsByIds($course['tags']);
        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-base.html.twig', array(
            'course'  => $course,
            'tags'    => ArrayToolkit::column($tags, 'name'),
            'default' => $default
        ));
    }

    public function pictureAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getOpenCourseService()->getCourse($id);

        return $this->render('TopxiaWebBundle:CourseManage:picture.html.twig', array(
            'course' => $course
        ));
    }

    public function pictureCropAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getOpenCourseService()->getCourse($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $this->getCourseService()->changeCoursePicture($course['id'], $data["images"]);
            return $this->redirect($this->generateUrl('course_manage_picture', array('id' => $course['id'])));
        }

        $fileId                                      = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render('TopxiaWebBundle:CourseManage:picture-crop.html.twig', array(
            'course'      => $course,
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize
        ));
    }

    public function teachersAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getOpenCourseService()->getCourse($id);

        if ($request->getMethod() == 'POST') {
            $data        = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);

            $teachers = array();

            foreach ($data['ids'] as $teacherId) {
                $teachers[] = array(
                    'id'        => $teacherId,
                    'isVisible' => empty($data['visible_'.$teacherId]) ? 0 : 1
                );
            }

            $this->getCourseService()->setCourseTeachers($id, $teachers);

            $this->setFlashMessage('success', '教师设置成功！');

            return $this->redirect($this->generateUrl('open_course_manage_teachers', array('id' => $id)));
        }

        $teacherMembers = $this->getOpenCourseService()->searchMembers(array(
            'courseId'  => $id,
            'role'      => 'teacher',
            'isVisible' => 1
        ),
            array('seq', 'ASC'),
            0, 100
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($teacherMembers, 'userId'));

        $teachers = array();

        foreach ($teacherMembers as $member) {
            if (empty($users[$member['userId']])) {
                continue;
            }

            $teachers[] = array(
                'id'        => $member['userId'],
                'nickname'  => $users[$member['userId']]['nickname'],
                'avatar'    => $this->getWebExtension()->getFilePath($users[$member['userId']]['smallAvatar'], 'avatar.png'),
                'isVisible' => $member['isVisible'] ? true : false
            );
        }

        return $this->render('TopxiaWebBundle:CourseManage:teachers.html.twig', array(
            'course'   => $course,
            'teachers' => $teachers
        ));
    }

    public function liveOpenTimeSetAction(Request $request, $id)
    {
        //$liveCourse     = $this->getCourseService()->tryManageCourse($id);
        $liveCourse     = $this->getOpenCourseService()->getCourse($id);
        $openLiveLesson = $this->getOpenCourseService()->searchLessons(array('courseId' => $liveCourse['id']), array('startTime', 'DESC'), 0, 1);
        $liveLesson     = $openLiveLesson ? $openLiveLesson[0] : array();

        if ($request->getMethod() == 'POST') {
            $liveLessonFields = $request->request->all();

            $liveLesson['type']      = 'liveOpen';
            $liveLesson['courseId']  = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLessonFields['startTime']);
            $liveLesson['length']    = $liveLessonFields['timeLength'];
            $liveLesson['title']     = $liveCourse['title'];
            $liveLesson['status']    = 'published';

            if ($openLiveLesson) {
                $live       = $this->getLiveCourseService()->editLiveRoom($liveCourse, $liveLesson, $this->container);
                $liveLesson = $this->getOpenCourseService()->updateLesson($liveLesson['id'], $liveLesson);
            } else {
                $live = $this->getLiveCourseService()->createLiveRoom($liveCourse, $liveLesson, $this->container);

                $liveLesson['mediaId']      = $live['id'];
                $liveLesson['liveProvider'] = $live['provider'];

                $liveLesson = $this->getOpenCourseService()->createLesson($liveLesson);
            }
        }

        return $this->render('TopxiaWebBundle:OpenCourseManage:live-open-time-set.html.twig', array(
            'course'         => $liveCourse,
            'openLiveLesson' => $liveLesson
        ));
    }

    public function marketingAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getOpenCourseService()->getCourse($id);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-marketing.html.twig', array(
            'course' => $course
        ));
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getLiveCourseService()
    {
        return $this->getServiceKernel()->createService('Course.LiveCourseService');
    }
}
