<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExportHelp;
use AppBundle\Common\Paginator;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\File\Service\UploadFileService;
use Biz\Goods\Service\GoodsService;
use Biz\OpenCourse\Service\LiveCourseService;
use Biz\OpenCourse\Service\OpenCourseRecommendedService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenCourseManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        return $this->forward('AppBundle:OpenCourseManage:base', ['id' => $id]);
    }

    public function baseAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $canUpdateStartTime = true;

        $liveLesson = [];

        if ('liveOpen' === $course['type']) {
            $openLiveLesson = $this->getOpenCourseService()->searchLessons(
                ['courseId' => $course['id']],
                ['startTime' => 'DESC'],
                0,
                1
            );

            $liveLesson = $openLiveLesson ? $openLiveLesson[0] : [];
            if (!empty($liveLesson['startTime']) && time() > $liveLesson['startTime']) {
                $canUpdateStartTime = false;
            }
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();

            if ('liveOpen' === $course['type'] && isset($data['startTime']) && !empty($data['startTime'])) {
                $data['length'] = $data['timeLength'];
                unset($data['timeLength']);
                $data['startTime'] = strtotime($data['startTime']);

                if ($data['startTime'] < time()) {
                    return $this->createMessageResponse('error', '开始时间应晚于当前时间');
                }

                $data['authUrl'] = $this->generateUrl('live_auth', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $data['jumpUrl'] = $this->generateUrl('live_jump', ['id' => $course['id']], UrlGeneratorInterface::ABSOLUTE_URL);
            }
            $this->getOpenCourseService()->updateCourse($id, $data);

            return $this->createJsonResponse(true);
        }

        $tags = $this->getTagService()->findTagsByOwner(['ownerType' => 'openCourse', 'ownerId' => $id]);

        return $this->render(
            'open-course-manage/base-info.html.twig',
            [
                'course' => $course,
                'openLiveLesson' => $liveLesson,
                'tags' => ArrayToolkit::column($tags, 'name'),
                'default' => $this->getSettingService()->get('default', []),
                'canUpdateStartTime' => $canUpdateStartTime,
            ]
        );
    }

    public function saveCourseAction(Request $request)
    {
        $data = $request->request->all();

        $openCourse = $this->getOpenCourseService()->createCourse($data);

        return $this->redirectToRoute(
            'open_course_manage',
            [
                'id' => $openCourse['id'],
            ]
        );
    }

    public function pictureCropAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $course = $this->getOpenCourseService()->changeCoursePicture($course['id'], $data['images']);
            $cover = $this->getWebExtension()->getFpath($course['largePicture']);

            return $this->createJsonResponse(['image' => $cover]);
        }

        return $this->render('open-course-manage/picture-crop-modal.html.twig');
    }

    public function teachersAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            if (empty($data) || !isset($data['teachers'])) {
                return $this->redirect($this->generateUrl('open_course_manage_teachers', ['id' => $id]));
            }

            $teachers = json_decode($data['teachers'], true);

            $this->getOpenCourseService()->setCourseTeachers($id, $teachers);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('open_course_manage_teachers', ['id' => $id]));
        }

        $teacherMembers = $this->getOpenCourseService()->searchMembers(
            [
                'courseId' => $id,
                'role' => 'teacher',
                'isVisible' => 1,
            ],
            ['seq' => 'ASC'],
            0,
            100
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($teacherMembers, 'userId'));

        $teacherIds = [];

        foreach ($teacherMembers as $member) {
            if (empty($users[$member['userId']])) {
                continue;
            }

            $teacherIds[] = [
                'id' => $member['userId'],
                'nickname' => $users[$member['userId']]['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath(
                    $users[$member['userId']]['smallAvatar'],
                    'avatar.png'
                ),
                'isVisible' => $member['isVisible'] ? true : false,
            ];
        }

        return $this->render(
            'open-course-manage/teachers.html.twig',
            [
                'course' => $course,
                'teacherIds' => $teacherIds,
            ]
        );
    }

    public function teachersMatchAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $queryField = $request->query->get('q');
        $users = $this->getUserService()->searchUsers(
            ['nickname' => $queryField, 'roles' => '|ROLE_TEACHER|'],
            ['createdTime' => 'DESC'],
            0,
            10
        );

        $teachers = [];

        foreach ($users as $user) {
            $teachers[] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
                'isVisible' => 1,
            ];
        }

        return $this->createJsonResponse($teachers);
    }

    public function studentsAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $fields = $request->query->all();
        $fields['userType'] = isset($fields['userType']) ? $fields['userType'] : 'login';

        $condition = ['courseId' => $course['id'], 'role' => 'student'];

        if ('login' === $fields['userType']) {
            $condition['userIdGT'] = 0;
        } elseif ('unlogin' === $fields['userType']) {
            $condition['userId'] = 0;
        }

        if (isset($fields['isNotified']) && 1 == $fields['isNotified']) {
            $condition['isNotified'] = 1;
        }

        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $users = $this->getUserService()->searchUsers(
                ['nickname' => $fields['keyword']],
                ['createdTime' => 'DESC'],
                0,
                PHP_INT_MAX
            );
            $userIds = ArrayToolkit::column($users, 'id');
            $condition['userIds'] = $userIds ? $userIds : [-1];
        }

        $paginator = new Paginator(
            $request,
            $this->getOpenCourseService()->countMembers($condition),
            20
        );

        $students = $this->getOpenCourseService()->searchMembers(
            $condition,
            ['lastEnterTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        return $this->render(
            'open-course-manage/students.html.twig',
            [
                'course' => $course,
                'students' => $students,
                'users' => $users,
                'paginator' => $paginator,
            ]
        );
    }

    public function liveOpenTimeSetAction(Request $request, $id)
    {
        $liveCourse = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $openLiveLesson = $this->getOpenCourseService()->searchLessons(
            ['courseId' => $liveCourse['id']],
            ['startTime' => 'DESC'],
            0,
            1
        );
        $liveLesson = $openLiveLesson ? $openLiveLesson[0] : [];

        $canUpdateStartTime = true;

        if (!empty($liveLesson['startTime']) && time() > $liveLesson['startTime']) {
            $canUpdateStartTime = false;
        }

        if ('POST' === $request->getMethod()) {
            $liveLessonFields = $request->request->all();

            if (!isset($liveLessonFields['startTime']) || empty($liveLessonFields['startTime'])) {
                return $this->createMessageResponse('error', '请先设置直播时间。');
            }

            $liveLesson['type'] = 'liveOpen';
            $liveLesson['courseId'] = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLessonFields['startTime']);
            $liveLesson['length'] = $liveLessonFields['timeLength'];
            $liveLesson['title'] = $liveCourse['title'];
            if ($liveLesson['startTime'] < time()) {
                return $this->createMessageResponse('error', '开始时间应晚于当前时间');
            }

            $routes = [
                'authUrl' => $this->generateUrl('live_auth', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'jumpUrl' => $this->generateUrl('live_jump', ['id' => $liveCourse['id']], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
            if ($openLiveLesson) {
                $live = $this->getLiveCourseService()->editLiveRoom($liveCourse, $liveLesson, $routes);
                $liveLesson = $this->getOpenCourseService()->updateLesson(
                    $liveLesson['courseId'],
                    $liveLesson['id'],
                    $liveLesson
                );
            } else {
                $live = $this->getLiveCourseService()->createLiveRoom($liveCourse, $liveLesson, $routes);

                $liveLesson['mediaId'] = $live['id'];
                $liveLesson['liveProvider'] = $live['provider'];

                $liveLesson = $this->getOpenCourseService()->createLesson($liveLesson);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render(
            'open-course-manage/live-open-time-set.html.twig',
            [
                'course' => $liveCourse,
                'openLiveLesson' => $liveLesson,
                'canUpdateStartTime' => $canUpdateStartTime,
            ]
        );
    }

    public function marketingAction(Request $request, $id)
    {
        $openCourse = $this->getOpenCourseService()->tryManageOpenCourse($id);

        if ('POST' === $request->getMethod()) {
            $recommendIds = $request->request->get('recommendIds');

            $this->getOpenCourseRecommendedService()->updateOpenCourseRecommendedCourses($id, $recommendIds);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect(
                $this->generateUrl(
                    'open_course_manage_marketing',
                    [
                        'id' => $id,
                    ]
                )
            );
        }

        $recommends = $this->getOpenCourseRecommendedService()->findRecommendedGoodsByOpenCourseId($id);

        $goodsIds = ArrayToolkit::column($recommends, 'recommendGoodsId');
        $goodses = ArrayToolkit::index($this->getGoodsService()->findGoodsByIds($goodsIds), 'id');

        $creators = $this->getUserService()->findUsersByIds(ArrayToolkit::column($goodses, 'creator'));

        return $this->render(
            'open-course-manage/open-course-marketing.html.twig',
            [
                'goodses' => $goodses,
                'creators' => $creators,
                'openCourse' => $openCourse,
                'course' => $openCourse, //为了满足layout course变量名要求
                'recommends' => $recommends,
            ]
        );
    }

    public function pickAction(Request $request, $filter, $id)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($id);

        $conditions = $request->query->all();

        list($paginator, $goodses) = $this->_getPickGoodsData($request, $id, $conditions);

        $creators = $this->getUserService()->findUsersByIds(ArrayToolkit::column($goodses, 'creator'));

        return $this->render(
            'open-course-manage/open-course-pick-modal.html.twig',
            [
                'creators' => $creators,
                'goodses' => $goodses,
                'paginator' => $paginator,
                'courseId' => $id,
                'filter' => $filter,
            ]
        );
    }

    public function deleteRecommendCourseAction(Request $request, $id, $recommendId)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($id);
        $this->getOpenCourseRecommendedService()->deleteRecommend($recommendId);

        return $this->createJsonResponse(true);
    }

    public function searchAction(Request $request, $id, $filter)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($id);
        $key = $request->query->get('key');
        $conditions = ['titleLike' => $key];

        list($paginator, $goodses) = $this->_getPickGoodsData($request, $id, $conditions);

        $creators = $this->getUserService()->findUsersByIds(ArrayToolkit::column($goodses, 'creator'));

        return $this->render(
            'open-course-manage/open-course-pick-modal.html.twig',
            [
                'creators' => $creators,
                'goodses' => $goodses,
                'filter' => $filter,
                'courseId' => $id,
                'title' => $key,
                'paginator' => $paginator,
            ]
        );
    }

    public function recommendedCoursesSelectAction(Request $request, $id)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($id);
        $this->removeDeletedGoodsRelation($id);
        $recommendNum = $this->getOpenCourseRecommendedService()->countRecommends(['openCourseId' => $id]);

        $ids = $request->request->get('ids');

        if (empty($ids)) {
            return $this->createJsonResponse(['result' => true]);
        }

        if (($recommendNum + count($ids)) > 5) {
            return $this->createJsonResponse(['result' => false, 'message' => '推荐课程/班级数量不能超过5个！']);
        }

        $this->getOpenCourseRecommendedService()->addRecommendGoods($id, $ids);

        return $this->createJsonResponse(['result' => true]);
    }

    public function publishAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $result = $this->getOpenCourseService()->publishCourse($id);

        if ('liveOpen' === $course['type'] && !$result['result']) {
            $result['message'] = '请先设置直播时间';
        }

        if ('open' === $course['type'] && !$result['result']) {
            $result['message'] = '请先创建课时';
        }

        return $this->createJsonResponse($result);
    }

    public function studentsExportDatasAction(Request $request, $id)
    {
        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $students, $courseMemberCount) = $this->getExportContent(
            $request,
            $id,
            $start,
            $limit,
            $exportAllowCount
        );

        $file = '';
        if (0 == $start) {
            $file = ExportHelp::addFileTitle($request, 'open-course-students', $title);
        }

        $content = implode("\r\n", $students);
        $file = ExportHelp::saveToTempFile($request, $content, $file);
        $status = ExportHelp::getNextMethod($start + $limit, $courseMemberCount);

        return $this->createJsonResponse(
            [
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
            ]
        );
    }

    public function studentsExportAction(Request $request, $id)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($id);
        $fileName = sprintf('open-course-%s-students-(%s).csv', $id, date('Y-n-d'));

        return ExportHelp::exportCsv($request, $fileName);
    }

    public function studentDetailAction(Request $request, $id, $userId)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $user = $this->getUserService()->getUser($userId);
        $profile = $this->getUserService()->getUserProfile($userId);
        $profile['title'] = $user['title'];

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($userFields); ++$i) {
            if (strstr($userFields[$i]['fieldName'], 'textField')) {
                $userFields[$i]['type'] = 'text';
            }

            if (strstr($userFields[$i]['fieldName'], 'varcharField')) {
                $userFields[$i]['type'] = 'varchar';
            }

            if (strstr($userFields[$i]['fieldName'], 'intField')) {
                $userFields[$i]['type'] = 'int';
            }

            if (strstr($userFields[$i]['fieldName'], 'floatField')) {
                $userFields[$i]['type'] = 'float';
            }

            if (strstr($userFields[$i]['fieldName'], 'dateField')) {
                $userFields[$i]['type'] = 'date';
            }
        }

        return $this->render(
            'open-course-manage/student-detail-modal.html.twig',
            [
                'user' => $user,
                'profile' => $profile,
                'userFields' => $userFields,
            ]
        );
    }

    public function lessonTimeCheckAction(Request $request, $courseId)
    {
        $data = $request->query->all();

        $startTime = $data['startTime'];
        $length = $data['length'];
        $lessonId = empty($data['lessonId']) ? '' : $data['lessonId'];

        list($result, $message) = $this->getOpenCourseService()->liveLessonTimeCheck(
            $courseId,
            $lessonId,
            $startTime,
            $length
        );

        if ('success' === $result) {
            $response = ['success' => true, 'message' => '这个时间段的课时可以创建'];
        } else {
            $response = ['success' => false, 'message' => $message];
        }

        return $this->createJsonResponse($response);
    }

    protected function _getType($filter)
    {
        $type = 'open';

        if ('openCourse' === $filter) {
            $type = 'open';
        } elseif ('otherCourse' === $filter || 'normal' === $filter) {
            $type = 'normal';
        }

        return $type;
    }

    protected function _getPickGoodsData(Request $request, $openCourseId, $conditions)
    {
        $coursesRecommended = $this->getOpenCourseRecommendedService()->searchRecommends(
            ['openCourseId' => $openCourseId],
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );
        $existRecommendGoodsIds = ArrayToolkit::column($coursesRecommended, 'recommendGoodsId');

        $conditions['status'] = 'published';
        $conditions['types'] = ['course', 'classroom'];

        if (!empty($existRecommendGoodsIds)) {
            $conditions['excludeIds'] = $existRecommendGoodsIds;
        }

        if (isset($conditions['titleLike']) && '' == $conditions['titleLike']) {
            unset($conditions['titleLike']);
        }

        $paginator = new Paginator(
            $request,
            $this->getGoodsService()->countGoods($conditions),
            5
        );

        $goodses = $this->getGoodsService()->searchGoods(
            $conditions,
            ['createdTime' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return [$paginator, $goodses];
    }

    protected function getExportContent($request, $id, $start, $limit, $exportAllowCount)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $gender = ['female' => '女', 'male' => '男', 'secret' => '秘密'];
        $conditions = ['courseId' => $course['id'], 'role' => 'student'];
        $userType = $request->query->get('userType', '');
        if ('login' === $userType) {
            $conditions['userIdGT'] = 0;
        } elseif ('unlogin' === $userType) {
            $conditions['userId'] = 0;
        }

        if (1 == $request->query->get('isNotified', 0)) {
            $conditions['isNotified'] = 1;
        }

        $courseMemberCount = $this->getOpenCourseService()->countMembers($conditions);
        $courseMemberCount = ($courseMemberCount > $exportAllowCount) ? $exportAllowCount : $courseMemberCount;
        if ($courseMemberCount < ($start + $limit + 1)) {
            $limit = $courseMemberCount - $start;
        }
        $courseMembers = $this->getOpenCourseService()->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );
        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        $fields['weibo'] = '微博';

        foreach ($userFields as $userField) {
            $fields[$userField['fieldName']] = $userField['title'];
        }

        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $progresses = [];

        $str = '用户名,Email,手机号,加入学习时间,上次进入时间,IP,姓名,性别,QQ号,微信号,公司,职业,头衔';

        foreach ($fields as $key => $value) {
            $str .= ','.$value;
        }

        $students = [];

        foreach ($courseMembers as $courseMember) {
            $member = '';

            if ('login' === $userType) {
                $member .= $users[$courseMember['userId']]['nickname'].',';
                $member .= $users[$courseMember['userId']]['email'].',';
                $member .= $users[$courseMember['userId']]['verifiedMobile'] ? $users[$courseMember['userId']]['verifiedMobile'].',' : '-,';
                $member .= date('Y-n-d H:i:s', $courseMember['createdTime']).',';
                $member .= date('Y-n-d H:i:s', $courseMember['lastEnterTime']).',';
                $member .= $courseMember['ip'].',';
                $member .= $profiles[$courseMember['userId']]['truename'] ? $profiles[$courseMember['userId']]['truename'].',' : '-'.',';
                $member .= $gender[$profiles[$courseMember['userId']]['gender']].',';
                $member .= $profiles[$courseMember['userId']]['qq'] ? $profiles[$courseMember['userId']]['qq'].',' : '-'.',';
                $member .= $profiles[$courseMember['userId']]['weixin'] ? $profiles[$courseMember['userId']]['weixin'].',' : '-'.',';
                $member .= $profiles[$courseMember['userId']]['company'] ? $profiles[$courseMember['userId']]['company'].',' : '-'.',';
                $member .= $profiles[$courseMember['userId']]['job'] ? $profiles[$courseMember['userId']]['job'].',' : '-'.',';
                $member .= $users[$courseMember['userId']]['title'] ? $users[$courseMember['userId']]['title'].',' : '-'.',';

                foreach ($fields as $key => $value) {
                    $member .= $profiles[$courseMember['userId']][$key] ? '"'.str_replace([PHP_EOL, '"'], '', $profiles[$courseMember['userId']][$key]).'",' : '-'.',';
                }
            } else {
                $member .= '-,-,';
                $member .= $courseMember['mobile'] ? $courseMember['mobile'].',' : '-,';
                $member .= date('Y-n-d H:i:s', $courseMember['createdTime']).',';
                $member .= date('Y-n-d H:i:s', $courseMember['lastEnterTime']).',';
                $member .= $courseMember['ip'].',';
                $member .= '-,-,-,-,-,-,-,';
                $member .= str_repeat('-,', count($fields) - 1).'-,';
            }

            $students[] = $member;
        }

        return [$str, $students, $courseMemberCount];
    }

    protected function _findCoursesPriceInterval($courseSetIds)
    {
        if (empty($courseSetIds)) {
            return [];
        }

        return $this->getCourseService()->findPriceIntervalByCourseSetIds($courseSetIds);
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    protected function _getTeacherUsers(array $courses)
    {
        $courseIds = ArrayToolkit::column($courses, 'defaultCourseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $teachers = ArrayToolkit::column($courses, 'teacherIds');

        if (empty($teachers)) {
            return [];
        }

        $userIds = call_user_func_array('array_merge', $teachers);

        return $this->getUserService()->findUsersByIds($userIds);
    }

    protected function removeDeletedGoodsRelation($openCourseId)
    {
        //删除 已经被删除的课程的推荐关系
        $recommends = $this->getOpenCourseRecommendedService()->searchRecommends(['openCourseId' => $openCourseId], [], 0, \PHP_INT_MAX);
        $recommends = ArrayToolkit::index($recommends, 'recommendGoodsId');
        $courseSets = $this->getGoodsService()->findGoodsByIds(array_keys($recommends));

        $removeIds = [];
        foreach ($recommends as $key => $value) {
            if (empty($courseSets[$key])) {
                $removeIds[] = $value['id'];
            }
        }

        $this->getOpenCourseRecommendedService()->deleteBatchRecommend($removeIds);
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return OpenCourseRecommendedService
     */
    protected function getOpenCourseRecommendedService()
    {
        return $this->createService('OpenCourse:OpenCourseRecommendedService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LiveCourseService
     */
    protected function getLiveCourseService()
    {
        return $this->createService('OpenCourse:LiveCourseService');
    }
}
