<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Response;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\LessonProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LessonProcessorImpl extends BaseProcessor implements LessonProcessor
{
    public function getVideoMediaUrl()
    {
        $courseId = $this->request->get("courseId");
        $lessonId = $this->request->get("lessonId");

        if (empty($courseId)) {
            return $this->createErrorResponse('not_courseId', '课程信息不存在！');
        }

        $user   = $this->controller->getUserByToken($this->request);
        $lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            return $this->createErrorResponse('not_courseId', '课时信息不存在！');
        }

        if ($lesson['free'] == 1) {
            if ($user->isLogin()) {
                if ($this->controller->getCourseService()->isCourseStudent($courseId, $user['id'])) {
                    $this->controller->getCourseService()->startLearnLesson($courseId, $lessonId);
                }
            }

            $lesson = $this->coverLesson($lesson);

            if ($lesson['mediaSource'] == 'self') {
                $response = $this->curlRequest("GET", $lesson['mediaUri'], null);
                return new Response($response);
            }

            return $lesson['mediaUri'];
        }

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $this->controller->getCourseService()->startLearnLesson($courseId, $lessonId);
        $member = $this->controller->getCourseService()->getCourseMember($courseId, $user['id']);
        $member = $this->previewAsMember($member, $courseId, $user);

        if ($member && in_array($member['role'], array("teacher", "student"))) {
            $lesson = $this->coverLesson($lesson);

            if ($lesson['mediaSource'] == 'self') {
                $response = $this->curlRequest("GET", $lesson['mediaUri'], null);
                return new Response($response);
            }

            return $lesson['mediaUri'];
        }

        if ($lesson['mediaSource'] == 'self') {
            $response = $this->curlRequest("GET", $lesson['mediaUri'], null);
            return new Response($response);
        }

        return $lesson['mediaUri'];
    }

    public function getLessonMaterial()
    {
        $lessonId        = $this->getParam("lessonId");
        $start           = (int) $this->getParam("start", 0);
        $limit           = (int) $this->getParam("limit", 10);
        $lessonMaterials = $this->controller->getMaterialService()->searchMaterials(
            array(
                'lessonId' => $lessonId,
                'source'   => 'coursematerial',
                'type'     => 'course'
            ),
            array('createdTime', 'DESC'),
            0, 1000
        );
        $files = $this->controller->getUploadFileService()->findFilesByIds(ArrayToolkit::column($lessonMaterials, 'fileId'));

        return array(
            "start" => $start,
            "limit" => $limit,
            "total" => 1000,
            "data"  => $this->filterMaterial($lessonMaterials, $files)
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
            $field                         = $lessonMaterial['fileId'];
            $lessonMaterial['fileMime']    = $newFiles[$field]['type'];
            return $lessonMaterial;
        }, $lessonMaterials);
    }

    public function downMaterial()
    {
        $courseId   = $this->request->get("courseId");
        $materialId = $this->request->get("materialId");
        $user       = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        list($course, $member) = $this->controller->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->controller->getCourseService()->isMemberNonExpired($course, $member)) {
            return "course_materials";
        }

        if ($member && $member['levelId'] > 0) {
            if ($this->controller->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                return "course_show";
            }
        }

        $material = $this->controller->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            throw "createNotFoundException";
        }

        return $this->controller->forward('TopxiaWebBundle:UploadFile:download', array('fileId' => $material['fileId']));
    }

    public function learnLesson()
    {
        $courseId = $this->getParam("courseId");
        $lessonId = $this->getParam("lessonId");
        $user     = $this->controller->getuserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $this->controller->getCourseService()->finishLearnLesson($courseId, $lessonId);

        return "finished";
    }

    public function getLessonStatus()
    {
        $user     = $this->controller->getuserByToken($this->request);
        $courseId = $this->getParam("courseId");
        $lessonId = $this->getParam("lessonId");

        if ($user->isLogin()) {
            $learnStatus     = $this->controller->getCourseService()->getUserLearnLessonStatus($user['id'], $courseId, $lessonId);
            $lessonMaterials = $this->controller->getMaterialService()->searchMaterials(
                array(
                    'lessonId' => $lessonId,
                    'source'   => 'coursematerial',
                    'type'     => 'course'
                ),
                array('createdTime', 'DESC'),
                0, 1
            );

            return array(
                "learnStatus" => $learnStatus,
                "hasMaterial" => empty($lessonMaterials) ? false : true
            );
        }

        return array();
    }

    public function getLearnStatus()
    {
        $user     = $this->controller->getuserByToken($this->request);
        $courseId = $this->getParam("courseId");

        if ($user->isLogin()) {
            $learnStatuses = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);
        } else {
            $learnStatuses = array();
        }

        return $learnStatuses;
    }

    public function unLearnLesson()
    {
        $courseId = $this->getParam("courseId");
        $lessonId = $this->getParam("lessonId");
        $user     = $this->controller->getuserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $this->controller->getCourseService()->cancelLearnLesson($courseId, $lessonId);

        return "learning";
    }

    public function getCourseDownLessons()
    {
        $token    = $this->controller->getUserToken($this->request);
        $user     = $this->controller->getUser();
        $courseId = $this->getParam("courseId");

        $course  = $this->controller->getCourseService()->getCourse($courseId);
        $lessons = $this->controller->getCourseService()->getCourseItems($courseId);
        $lessons = $this->controller->filterItems($lessons);
        if ($user->isLogin()) {
            $learnStatuses = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);
        } else {
            $learnStatuses = null;
        }
        // $files = $this->getUploadFiles($courseId);
        $fileIds = ArrayToolkit::column($lessons, 'mediaId');
        $files   = ArrayToolkit::index($this->getUploadFileService()->findFilesByIds($fileIds), 'id');
        $files   = array_map(function ($file) {
            $file['convertParams'] = null; //过滤convertParams防止移动端报错
            return $file;
        }, $files);
        $lessons = $this->filterLessons($lessons, $files);
        return array(
            "lessons" => array_values($lessons),
            "course"  => $this->controller->filterCourse($course)
        );
    }

    public function getCourseLessons()
    {
        $token    = $this->controller->getUserToken($this->request);
        $user     = $this->controller->getUser();
        $courseId = $this->getParam("courseId");

        $lessons = $this->controller->getCourseService()->getCourseItems($courseId);
        $lessons = $this->controller->filterItems($lessons);

        if ($user->isLogin()) {
            $learnStatuses = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);
        } else {
            $learnStatuses = null;
        }

        $files   = $this->getUploadFiles($courseId);
        $lessons = $this->filterLessons($lessons, $files);
        return array(
            "lessons"       => array_values($lessons),
            "learnStatuses" => empty($learnStatuses) ? array("-1" => "learning") : $learnStatuses
        );
    }

    private function getUploadFiles($courseId)
    {
        $conditions = array(
            'targetType' => "courselesson",
            'targetId'   => $courseId
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime', 'DESC'),
            0,
            100
        );

        $uploadFiles = array();

        foreach ($files as $key => $file) {
            unset($file["metas"]);
            unset($file["metas2"]);
            unset($file["hashId"]);
            unset($file["etag"]);
            $uploadFiles[$file["id"]] = $file;
        }

        return $uploadFiles;
    }

    public function getLesson()
    {
        $courseId = $this->getParam("courseId");
        $lessonId = $this->getParam("lessonId");

        if (empty($courseId)) {
            return $this->createErrorResponse('not_courseId', '课程信息不存在！');
        }

        $user   = $this->controller->getUserByToken($this->request);
        $lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            return $this->createErrorResponse('not_courseId', '课时信息不存在！');
        }

        if ($lesson['free'] == 1) {
            if ($user->isLogin()) {
                if ($this->controller->getCourseService()->isCourseStudent($courseId, $user['id'])) {
                    $this->controller->getCourseService()->startLearnLesson($courseId, $lessonId);
                }
            }

            return $this->coverLesson($lesson);
        }

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        if ($this->controller->getCourseService()->isCourseStudent($courseId, $user['id'])) {
            $this->controller->getCourseService()->startLearnLesson($courseId, $lessonId);
        }

        $member = $this->controller->getCourseService()->getCourseMember($courseId, $user['id']);
        $member = $this->previewAsMember($member, $courseId, $user);

        if ($member && in_array($member['role'], array("teacher", "student"))) {
            return $this->coverLesson($lesson);
        }

        return $this->createErrorResponse('not_student', '你不是该课程学员，请加入学习!');
    }

    private function getTestpaperLesson($lesson)
    {
        $user = $this->controller->getUser();
        $id   = $lesson['mediaId'];

        $testpaper = $this->getTestpaperService()->getTestpaper($id);

        if (empty($testpaper)) {
            return $this->createErrorResponse('error', '试卷不存在!');
        }

        $testResult        = $this->getTestpaperService()->findTestpaperResultByTestpaperIdAndUserIdAndActive($id, $user['id']);
        $lesson['content'] = array(
            'status'   => empty($testResult) ? 'nodo' : $testResult['status'],
            'resultId' => empty($testResult) ? 0 : $testResult['id']
        );

        return $lesson;
    }

    public function getTestpaperInfo()
    {
        $id   = $this->getParam("testId");
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($id);

        if (empty($testpaper)) {
            return $this->createErrorResponse('error', '试卷已删除，请联系管理员。!');
        }

        $items = $this->getTestpaperService()->getTestpaperItems($id);
        return array(
            'testpaper' => $testpaper,
            'items'     => $this->filterTestpaperItems($items)
        );
    }

    private function filterTestpaperItems($items)
    {
        $itemArray = array();

        foreach ($items as $key => $item) {
            $type = $item['questionType'];

            if (isset($itemArray[$type])) {
                $count            = $itemArray[$type];
                $itemArray[$type] = $count + 1;
            } else {
                $itemArray[$type] = 1;
            }
        }

        return $itemArray;
    }

    private function coverLesson($lesson)
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
                return $this->getDocumentLesson($lesson);
            default:
                $lesson['content'] = $this->wrapContent($lesson['content']);
        }

        return $lesson;
    }

    private function getVideoLesson($lesson)
    {
        $token       = $this->controller->getUserToken($this->request);
        $mediaId     = $lesson['mediaId'];
        $mediaSource = $lesson['mediaSource'];
        $mediaUri    = $lesson['mediaUri'];
        if ($lesson['length'] > 0) {
            $lesson['length'] = $this->getContainer()->get('topxia.twig.web_extension')->durationFilter($lesson['length']);
        } else {
            $lesson['length'] = '';
        }

        if ($mediaSource == 'self') {
            $file = $this->controller->getUploadFileService()->getFullFile($lesson['mediaId']);

            if (!empty($file)) {
                if ($file['storage'] == 'cloud') {
                    $enablePlayRate = $this->controller->setting('storage.enable_playback_rates');
                    if ($enablePlayRate && $file['mcStatus'] && $file['mcStatus'] == 'yes') {
                        $player = $this->controller->getMaterialLibService()->player($file['globalId']);
                        if (isset($player['mp4url'])) {
                            $lesson['mediaUri'] = $player['mp4url'];
                            return $lesson;
                        }
                    }

                    //do mp4
                    $lesson['mediaConvertStatus'] = $file['status'];

                    if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                        if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                            $headLeaderInfo = $this->getHeadLeaderInfo();

                            if ($headLeaderInfo) {
                                $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                    'data'     => array(
                                        'id'      => $headLeaderInfo['id'],
                                        'fromApi' => true
                                    ),
                                    'times'    => 2,
                                    'duration' => 3600
                                ));

                                $headUrl = array(
                                    'url' => $this->controller->generateUrl('hls_playlist', array(
                                        'id'            => $headLeaderInfo['id'],
                                        'token'         => $token['token'],
                                        'line'          => $this->request->get('line'),
                                        'hideBeginning' => 1
                                    ), true)
                                );

                                $lesson['headUrl'] = $headUrl['url'];
                            }

                            $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                'data'     => array(
                                    'id'      => $file['id'],
                                    'fromApi' => true
                                ),
                                'times'    => 2,
                                'duration' => 3600
                            ));

                            $url = array(
                                'url' => $this->controller->generateUrl('hls_playlist', array(
                                    'id'            => $file['id'],
                                    'token'         => $token['token'],
                                    'line'          => $this->request->get('line'),
                                    'hideBeginning' => 1
                                ), true)
                            );
                        } else {
                            $url = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                        }

                        $lesson['mediaUri'] = (isset($url) && is_array($url) && !empty($url['url'])) ? $url['url'] : '';
                    } else {
                        if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                            $key = $file['metas']['hd']['key'];
                        } else {
                            if ($file['type'] == 'video') {
                                $key = null;
                            } else {
                                $key = $file['hashId'];
                            }
                        }

                        if ($key) {
                            $result             = $this->controller->getMaterialLibService()->player($file['globalId']);
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
        } elseif ($mediaSource == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $lesson['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
            } else {
                $lesson['mediaUri'] = '';
            }
        } elseif ($mediaSource == 'tudou') {
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $lesson['mediaUri'] = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
            } else {
                $lesson['mediaUri'] = '';
            }
        } else {
            $lesson['mediaUri'] = $mediaUri;
        }

        return $lesson;
    }

    public function getLocalVideo()
    {
        $fileId = $this->getParam("targetId");
        $user   = $this->controller->getuserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            return $this->createErrorResponse('error', "视频文件不存在!");
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

        if ($file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                $url = $this->controller->generateUrl('course_manage_files', array('id' => $courseId));
                return $this->createErrorResponse('not_ppt', 'PPT文档转换失败，请到课程文件管理中，重新转换!');
            } else {
                return $this->createErrorResponse('not_ppt', 'PPT文档还在转换中，还不能查看，请稍等。!');
            }
        }

        $ppt = $this->controller->getMaterialLibService()->player($file['globalId']);

        if (isset($ppt["convertStatus"])) {
            $ppt = array();
        }

        $lesson['content'] = $ppt['images'];

        return $lesson;
    }

    public function test()
    {
        $user   = $this->controller->getUserByToken($this->request);
        $lesson = $this->controller->getCourseService()->getCourseLesson(11, 185);

        $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

        if (empty($file)) {
            return $this->createErrorResponse('not_document', '文档还在转换中，还不能查看，请稍等。!');
        }

        if ($file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                return $this->createErrorResponse('not_document', '文档转换失败，请联系管理员!');
            } else {
                return $this->createErrorResponse('not_document', '文档还在转换中，还不能查看，请稍等!');
            }
        }

        $factory = new CloudClientFactory();
        $client  = $factory->createClient();

        $metas2 = $file['metas2'];
        $url    = $client->generateFileUrl($client->getBucket(), $metas2['pdf']['key'], 3600);
        $pdfUri = $url['url'];
        $url    = $client->generateFileUrl($client->getBucket(), $metas2['swf']['key'], 3600);
        $swfUri = $url['url'];

        $content = $lesson['content'];
        $content = $this->controller->convertAbsoluteUrl($this->request, $content);
        $render  = $this->controller->render('TopxiaMobileBundleV2:Course:document.html.twig', array(
            'pdfUri' => $pdfUri,
            'swfUri' => $swfUri,
            'title'  => $lesson['title']
        ));

        $lesson['content'] = $render->getContent();
        return $render;
    }

    private function getDocumentLesson($lesson)
    {
        $file = $this->controller->getUploadFileService()->getFullFile($lesson['mediaId']);

        if (empty($file)) {
            return $this->createErrorResponse('not_document', '文档还在转换中，还不能查看，请稍等。!');
        }

        if ($file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                return $this->createErrorResponse('not_document', '文档转换失败，请联系管理员!');
            } else {
                return $this->createErrorResponse('not_document', '文档还在转换中，还不能查看，请稍等!');
            }
        }

        $file = $this->controller->getMaterialLibService()->player($file['globalId']);

        $content = $lesson['content'];
        $content = $this->controller->convertAbsoluteUrl($this->request, $content);
        $render  = $this->controller->render('TopxiaMobileBundleV2:Course:document.html.twig', array(
            'pdfUri' => $file['pdf'],
            'swfUri' => $file['swf'],
            'title'  => $lesson['title']
        ));

        $lesson['content'] = $render->getContent();

        return $lesson;
    }

    private function getHeadLeaderInfo()
    {
        $storage = $this->controller->getSettingService()->get("storage");

        if (!empty($storage) && array_key_exists("video_header", $storage) && $storage["video_header"]) {
            $file = $this->controller->getUploadFileService()->getFileByTargetType('headLeader');
            return $file;
        }

        return false;
    }

    private function wrapContent($content)
    {
        $content = $this->controller->convertAbsoluteUrl($this->request, $content);

        $render = $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            'content' => $content
        ));

        return $render->getContent();
    }

    private function filterLessons($lessons, $files)
    {
        return array_map(function ($lesson) use ($files) {
            $lesson['content'] = "";

            if (isset($lesson["mediaId"])) {
                $lesson["uploadFile"] = isset($files[$lesson["mediaId"]]) ? $files[$lesson["mediaId"]] : null;
            }

            return $lesson;
        }, $lessons);
    }
}
