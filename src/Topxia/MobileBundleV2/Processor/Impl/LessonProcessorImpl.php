<?php

namespace Topxia\MobileBundleV2\Processor\Impl;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\MediaParser\ParserProxy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\LessonProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LessonProcessorImpl extends BaseProcessor implements LessonProcessor
{
    public function getVideoMediaUrl()
    {
        $courseId = $this->request->get('courseId');
        $lessonId = $this->request->get('lessonId');

        if (empty($courseId)) {
            return $this->createErrorResponse('not_courseId', '课程信息不存在！');
        }

        $user = $this->controller->getUserByToken($this->request);
        $lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            return $this->createErrorResponse('not_courseId', '课时信息不存在！');
        }

        if (1 == $lesson['free']) {
            if ($user->isLogin()) {
                if ($this->controller->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
                    $this->controller->getCourseService()->startLearnLesson($courseId, $lessonId);
                }
            }

            $lesson = $this->coverLesson($lesson);

            if ('self' == $lesson['mediaSource']) {
                $response = $this->curlRequest('GET', $lesson['mediaUri'], null);

                return new Response($response);
            }

            return $lesson['mediaUri'];
        }

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $this->controller->getCourseService()->startLearnLesson($courseId, $lessonId);
        $member = $this->controller->getCourseMemberService()->getCourseMember($courseId, $user['id']);
        $member = $this->previewAsMember($member, $courseId, $user);

        if ($member && in_array($member['role'], array('teacher', 'student'))) {
            $lesson = $this->coverLesson($lesson);

            if ('self' == $lesson['mediaSource']) {
                $response = $this->curlRequest('GET', $lesson['mediaUri'], null);

                return new Response($response);
            }

            return $lesson['mediaUri'];
        }

        if ('self' == $lesson['mediaSource']) {
            $response = $this->curlRequest('GET', $lesson['mediaUri'], null);

            return new Response($response);
        }

        return $lesson['mediaUri'];
    }

    public function getLessonMaterial()
    {
        $lessonId = $this->getParam('lessonId');
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $lessonMaterials = $this->controller->getMaterialService()->searchMaterials(
            array(
                'lessonId' => $lessonId,
                'source' => 'coursematerial',
                'type' => 'course',
            ),
            array('createdTime', 'DESC'),
            0,
            1000
        );
        $files = $this->controller->getUploadFileService()->findFilesByIds(ArrayToolkit::column($lessonMaterials, 'fileId'));

        return array(
            'start' => $start,
            'limit' => $limit,
            'total' => 1000,
            'data' => $this->filterMaterial($lessonMaterials, $files),
        );
    }

    private function filterMaterial($lessonMaterials, $files)
    {
        $newFiles = array();

        foreach ($files as $key => $file) {
            $newFiles[$file['id']] = $file;
        }

        return array_map(function ($lessonMaterial) use ($newFiles) {
            $lessonMaterial['createdTime'] = date('c', $lessonMaterial['createdTime']);
            $field = $lessonMaterial['fileId'];
            $lessonMaterial['fileMime'] = $newFiles[$field]['type'];

            return $lessonMaterial;
        }, $lessonMaterials);
    }

    public function downMaterial()
    {
        $courseId = $this->request->get('courseId');
        $materialId = $this->request->get('materialId');
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        list($course, $member) = $this->controller->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->controller->getCourseMemberService()->isMemberNonExpired($course, $member)) {
            return 'course_materials';
        }

        if ($member && $member['levelId'] > 0) {
            if ('ok' != $this->controller->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId'])) {
                return 'course_show';
            }
        }

        $material = $this->controller->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            throw new \RuntimeException('资料不存在');
        }

        return $this->controller->forward('TopxiaWebBundle:UploadFile:download', array('fileId' => $material['fileId']));
    }

    public function learnLesson()
    {
        $courseId = $this->getParam('courseId');
        $lessonId = $this->getParam('lessonId');
        $user = $this->controller->getuserByToken($this->request);
        $task = $this->getTaskService()->getTask($lessonId);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        try {
            if ($this->canStartTask($task)) {
                $this->getActivityService()->trigger($task['activityId'], 'start', array(
                    'task' => $task,
                ));
            }

            $this->getTaskService()->finishTaskResult($task['id']);
        } catch (\Exception $e) {
            return $this->createErrorResponse('404', $e->getMessage());
        }

        return 'finished';
    }

    public function getLessonStatus()
    {
        $user = $this->controller->getuserByToken($this->request);
        $courseId = $this->getParam('courseId');
        $lessonId = $this->getParam('lessonId');

        if ($user->isLogin()) {
            $learnStatus = $this->getTaskResultService()->getUserTaskResultByTaskId($lessonId);
            if ($learnStatus) {
                $learnStatus['lessonId'] = $lessonId;
            }

            $conditions = array(
                'lessonId' => $lessonId,
                'source' => 'courseactivity',
                'type' => 'course',
            );
            $lessonMaterialCount = $this->getMaterialService()->countMaterials($conditions);

            return array(
                'learnStatus' => $learnStatus,
                'hasMaterial' => empty($lessonMaterialCount) ? false : true,
            );
        }

        return array();
    }

    public function getLearnStatus()
    {
        $user = $this->controller->getuserByToken($this->request);
        $courseId = $this->getParam('courseId');

        if ($user->isLogin() && !empty($courseId)) {
            $taskResults = $this->controller->getTaskResultService()->findUserTaskResultsByCourseId($courseId);
            $learnStatuses = array();
            foreach ($taskResults as $result) {
                if ('finish' === $result['status']) {
                    $status = 'finished';
                } elseif ('start' === $result['status']) {
                    $status = 'learning';
                } else {
                    continue;
                }
                $learnStatuses[$result['courseTaskId']] = $status;
            }
        } else {
            $learnStatuses = array();
        }

        return $learnStatuses;
    }

    public function unLearnLesson()
    {
        $courseId = $this->getParam('courseId');
        $lessonId = $this->getParam('lessonId');
        $user = $this->controller->getuserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $this->controller->getCourseService()->cancelLearnLesson($courseId, $lessonId);

        return 'learning';
    }

    public function getCourseDownLessons()
    {
        $courseId = $this->getParam('courseId');
        $user = $this->controller->getuserByToken($this->request);

        if (!$this->controller->getCourseService()->canTakeCourse($courseId)) {
            return $this->createErrorResponse('403', 'Access Denied');
        }
        $course = $this->controller->getCourseService()->getCourse($courseId);
        $lessons = $this->controller->getCourseService()->findCourseTasksAndChapters($courseId);

        $lessons = $this->controller->filterItems($lessons);

        $fileIds = ArrayToolkit::column($lessons, 'mediaId');

        $files = ArrayToolkit::index($this->getUploadFileService()->findFilesByIds($fileIds), 'id');
        $files = array_map(function ($file) {
            $file['convertParams'] = null; //过滤convertParams防止移动端报错
            return $file;
        }, $files);

        $lessons = $this->filterLessons($lessons, $files);

        return array(
            'lessons' => array_values($lessons),
            'course' => $this->controller->filterCourse($course),
        );
    }

    public function getCourseLessons()
    {
        $user = $this->controller->getuserByToken($this->request);
        $courseId = $this->getParam('courseId');

        $lessons = $this->controller->getCourseService()->findCourseTasksAndChapters($courseId);
        $lessons = $this->controller->filterItems($lessons);

        if ($user->isLogin()) {
            $learnStatuses = $this->_findUserLearnTaskStatus($courseId);
        } else {
            $learnStatuses = null;
        }

        $files = $this->getUploadFiles($courseId);
        $lessons = $this->filterLessons($lessons, $files);

        return array(
            'lessons' => array_values($lessons),
            'learnStatuses' => empty($learnStatuses) ? array('-1' => 'learning') : $learnStatuses,
        );
    }

    private function makeTryLookVideoUrl($lessons, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        foreach ($lessons as $key => $lesson) {
            if ('video' == $lesson['type']) {
                $lessonFree = $this->isLessonFree($lesson);
                $courseTryLookAble = $this->isCourseTryLookAble($course);

                if ($lessonFree) {
                    $lessons[$key] = $this->getVideoLesson($lesson);
                }

                if ($courseTryLookAble && !$lessonFree) {
                    $tryLookTime = $course['tryLookTime'];
                    $options = array('watchTimeLimit' => $tryLookTime * 60);
                    $lessons[$key] = $this->getVideoLesson($lesson, $options);
                }
            }
        }

        return $lessons;
    }

    private function isCourseTryLookAble($course)
    {
        return empty($course['tryLookable']) ? false : true;
    }

    private function isLessonFree($lesson)
    {
        return empty($lesson['free']) ? false : true;
    }

    private function getUploadFiles($courseId)
    {
        $conditions = array(
            'targetType' => 'courselesson',
            'targetId' => $courseId,
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            100
        );

        $uploadFiles = array();

        foreach ($files as $key => $file) {
            unset($file['metas']);
            unset($file['metas2']);
            unset($file['hashId']);
            unset($file['etag']);
            $uploadFiles[$file['id']] = $file;
        }

        return $uploadFiles;
    }

    public function getLesson()
    {
        $courseId = $this->getParam('courseId');
        $lessonId = $this->getParam('lessonId');
        $ssl = $this->request->isSecure() ? true : false;

        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return $this->createErrorResponse('not_courseId', '课程信息不存在！');
        }

        $user = $this->controller->getUserByToken($this->request);
        $lesson = $this->getTaskService()->getTask($lessonId);
        $lessons = $this->getCourseService()->convertTasks(array($lesson), $course);
        $lesson = $lessons[0];

        if (empty($lesson)) {
            return $this->createErrorResponse('not_courseId', '课时信息不存在！');
        }

        if (1 == $lesson['free']) {
            if ($user->isLogin()) {
                if ($this->controller->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
                    $this->getTaskService()->startTask($lesson['id']);
                }
            }

            return $this->coverLesson($lesson, $ssl);
        }

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        if ($this->controller->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $this->getTaskService()->startTask($lesson['id']);
        }

        $member = $this->controller->getCourseMemberService()->getCourseMember($courseId, $user['id']);
        $member = $this->previewAsMember($member, $courseId, $user);

        if ($member && in_array($member['role'], array('teacher', 'student'))) {
            return $this->coverLesson($lesson, $ssl);
        }

        return $this->createErrorResponse('not_student', '你不是该课程学员，请加入学习!');
    }

    private function getTestpaperLesson($lesson)
    {
        $user = $this->controller->getUser();
        $id = $lesson['mediaId'];

        $testpaper = $this->getTestpaperService()->getTestpaper($id);

        if (empty($testpaper)) {
            return $this->createErrorResponse('error', '试卷不存在!');
        }

        $testResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $id, $lesson['courseId'], $lesson['activityId'], 'testpaper');
        $lesson['content'] = array(
            'status' => empty($testResult) ? 'nodo' : $testResult['status'],
            'resultId' => empty($testResult) ? 0 : $testResult['id'],
        );

        return $lesson;
    }

    public function getTestpaperInfo()
    {
        $id = $this->getParam('testId');
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($id);

        if (empty($testpaper)) {
            return $this->createErrorResponse('error', '试卷已删除，请联系管理员。!');
        }

        $items = $this->getTestpaperService()->showTestpaperItems($id);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaper' => $testpaper,
            'items' => $this->filterTestpaperItems($items),
        );
    }

    private function filterTestpaperItems($items)
    {
        $itemArray = array();

        foreach ($items as $questionType => $item) {
            $itemArray[$questionType] = count($item);
        }

        return $itemArray;
    }

    private function coverLesson($lesson, $ssl = false)
    {
        $lesson['createdTime'] = date('c', $lesson['createdTime']);

        switch ($lesson['type']) {
            case 'ppt':
                return $this->getPPTLesson($lesson);
            case 'audio':
            case 'video':
                return $this->getVideoLesson($lesson);
            case 'testpaper':
                return $this->getTestpaperLesson($lesson);
            case 'document':
                return $this->getDocumentLesson($lesson, $ssl);
            default:
                $lesson['content'] = $this->wrapContent($lesson['content']);
        }

        return $lesson;
    }

    protected function makeTokenData($fields)
    {
        $options = array();
        if (!empty($fields['options'])) {
            $options = $fields['options'];
            unset($fields['options']);
        }

        return array_merge($fields, $options);
    }

    private function getVideoLesson($lesson, $options = null)
    {
        $token = $this->controller->getUserToken($this->request);
        $mediaId = $lesson['mediaId'];
        $mediaSource = $lesson['mediaSource'];
        $mediaUri = $lesson['mediaUri'];
        if ($lesson['length'] > 0) {
            $lesson['length'] = $this->getContainer()->get('web.twig.extension')->durationFilter($lesson['length']);
        } else {
            $lesson['length'] = '';
        }

        if ('self' == $mediaSource) {
            $file = $this->controller->getUploadFileService()->getFullFile($lesson['mediaId']);

            if (!empty($file)) {
                if ('cloud' == $file['storage']) {
                    $lesson['mediaConvertStatus'] = $file['status'];

                    if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                        if (isset($file['convertParams']['convertor']) && ('HLSEncryptedVideo' == $file['convertParams']['convertor'])) {
                            $headLeaderInfo = $this->getHeadLeaderInfo();

                            if ($headLeaderInfo) {
                                $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                    'data' => array(
                                        'id' => $headLeaderInfo['id'],
                                        'fromApi' => true,
                                    ),
                                    'times' => 2,
                                    'duration' => 3600,
                                ));

                                $headUrl = array(
                                    'url' => $this->controller->generateUrl('hls_playlist', array(
                                        'id' => $headLeaderInfo['id'],
                                        'token' => $token['token'],
                                        'line' => $this->request->get('line'),
                                        'hideBeginning' => 1,
                                    ), UrlGeneratorInterface::ABSOLUTE_URL),
                                );

                                $lesson['headUrl'] = $headUrl['url'];
                                $lesson['headLength'] = $headLeaderInfo['length'];
                            }

                            $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                'data' => $this->makeTokenData(array(
                                    'id' => $file['id'],
                                    'fromApi' => true,
                                    'options' => $options,
                                )),
                                'times' => 2,
                                'duration' => 3600,
                            ));

                            $url = array(
                                'url' => $this->controller->generateUrl('hls_playlist', array(
                                    'id' => $file['id'],
                                    'token' => $token['token'],
                                    'line' => $this->request->get('line'),
                                    'hideBeginning' => 1,
                                ), UrlGeneratorInterface::ABSOLUTE_URL),
                            );
                        } else {
                            throw new \RuntimeException('当前视频不支持播放！');
                        }

                        $lesson['mediaUri'] = (isset($url) && is_array($url) && !empty($url['url'])) ? $url['url'] : '';
                    } else {
                        if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                            $key = $file['metas']['hd']['key'];
                        } else {
                            if ('video' == $file['type']) {
                                $key = null;
                            } else {
                                $key = $file['hashId'];
                            }
                        }

                        if ($key) {
                            $result = $this->controller->getMaterialLibService()->player($file['globalId']);
                            $lesson['mediaUri'] = $result['url'];
                        } else {
                            $lesson['mediaUri'] = '';
                        }
                    }
                } else {
                    $lesson['mediaUri'] = $this->request->getSchemeAndHttpHost()."/mapi_v2/Lesson/getLocalVideo?targetId={$file['id']}&token={$token['token']}";
                }
            } else {
                $lesson['mediaUri'] = '';
            }
        } else {
            $proxy = new ParserProxy();
            $lesson = $proxy->prepareMediaUriForMobile($lesson, $this->getSchema());
        }

        return $lesson;
    }

    public function getLocalVideo()
    {
        $fileId = $this->getParam('targetId');
        $user = $this->controller->getuserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            return $this->createErrorResponse('error', '视频文件不存在!');
        }

        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();
        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);

        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }

    private function getPPTLesson($lesson)
    {
        $file = $this->controller->getUploadFileService()->getFullFile($lesson['mediaId']);

        if (empty($file)) {
            return $this->createErrorResponse('not_ppt', '获取ppt课时失败!');
        }

        if ('success' != $file['convertStatus']) {
            if ('error' == $file['convertStatus']) {
                return $this->createErrorResponse('not_ppt', 'PPT文档转换失败，请到课程文件管理中，重新转换!');
            } else {
                return $this->createErrorResponse('not_ppt', 'PPT文档还在转换中，还不能查看，请稍等。!');
            }
        }

        $ppt = $this->controller->getMaterialLibService()->player($file['globalId']);

        if (isset($ppt['convertStatus'])) {
            $ppt = array();
        }

        $lesson['content'] = $ppt['images'];

        return $lesson;
    }

    private function getDocumentLesson($lesson, $ssl = false)
    {
        $file = $this->controller->getUploadFileService()->getFullFile($lesson['mediaId']);

        if (empty($file)) {
            return $this->createErrorResponse('not_document', '文档还在转换中，还不能查看，请稍等。!');
        }

        if ('success' != $file['convertStatus']) {
            if ('error' == $file['convertStatus']) {
                return $this->createErrorResponse('not_document', '文档转换失败，请联系管理员!');
            } else {
                return $this->createErrorResponse('not_document', '文档还在转换中，还不能查看，请稍等!');
            }
        }

        $result = $this->controller->getMaterialLibService()->player($file['globalId'], $ssl);

        $content = $lesson['content'];
        $content = $this->controller->convertAbsoluteUrl($this->request, $content);

        $response = $this->controller->render('material-lib/player/global-document-player.html.twig', array(
                'globalId' => $file['globalId'],
                'token' => $result['token'],
            ));

        $lesson['content'] = $response->getContent();

        return $lesson;
    }

    private function getHeadLeaderInfo()
    {
        $storage = $this->controller->getSettingService()->get('storage');

        if (!empty($storage) && array_key_exists('video_header', $storage) && $storage['video_header']) {
            $file = $this->controller->getUploadFileService()->getFileByTargetType('headLeader');

            return $file;
        }

        return false;
    }

    private function wrapContent($content)
    {
        $content = $this->controller->convertAbsoluteUrl($this->request, $content);

        $render = $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            'content' => $content,
        ));

        return $render->getContent();
    }

    private function filterLessons($lessons, $files)
    {
        return array_map(function ($lesson) use ($files) {
            $lesson['content'] = '';

            if (isset($lesson['mediaId'])) {
                $file = isset($files[$lesson['mediaId']]) ? $files[$lesson['mediaId']] : null;
                $lesson['uploadFile'] = $file;
                $lesson['mediaName'] = isset($file['filename']) ? $file['filename'] : '';
            }

            unset($lesson['tags']);

            return $lesson;
        }, $lessons);
    }

    private function _findUserLearnTaskStatus($courseId)
    {
        $taskResults = $this->controller->getTaskResultService()->findUserTaskResultsByCourseId($courseId);
        $learnStatuses = array();
        foreach ($taskResults as $result) {
            if ('finish' === $result['status']) {
                $status = 'finished';
            } elseif ('start' === $result['status']) {
                $status = 'learning';
            } else {
                continue;
            }
            $learnStatuses[$result['courseTaskId']] = $status;
        }

        return $learnStatuses;
    }

    private function canStartTask($task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $config = $this->getActivityService()->getActivityConfig($activity['mediaType']);

        return $config->allowTaskAutoStart($activity);
    }

    protected function getActivityService()
    {
        return $this->controller->getService('Activity:ActivityService');
    }

    protected function getTaskResultService()
    {
        return $this->controller->getService('Task:TaskResultService');
    }

    protected function getMaterialService()
    {
        return $this->controller->getService('Course:MaterialService');
    }

    protected function getTaskService()
    {
        return $this->controller->getService('Task:TaskService');
    }
}
