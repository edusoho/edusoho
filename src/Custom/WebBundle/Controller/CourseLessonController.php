<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\StringToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;

class CourseLessonController extends BaseController
{
	public function previewAction(Request $request,$courseId,$lessonId)
	{
        $course = $this->getCourseService()->getCourse($courseId);
		$lessons=$this->getCourseService()->getCourseLessons($courseId);
        if(empty($lessonId)){
			$lesson = empty($lessons[0]) ? array() : $lessons[0];
			$isModal = false;
		}else{
        	$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
			$isModal = true;
		}
        $user = $this->getCurrentUser();

        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if (!empty($course['status']) && $course['status'] == 'closed') {
            return $this->render('TopxiaWebBundle:CourseLesson:preview-notice-modal.html.twig',array('course' => $course));
        }
        //如果不是第一个课时
        if ($isModal){
            if (!$user->isLogin()) {
                throw $this->createAccessDeniedException();
            }
            if (empty($lesson['free'])) {
                return $this->forward('TopxiaWebBundle:CourseOrder:buy', array('id' => $courseId), array('preview' => true));
            }else{
                $allowAnonymousPreview = $this->setting('course.allowAnonymousPreview', 1);
                if (empty($allowAnonymousPreview) && !$user->isLogin()) {
                    throw $this->createAccessDeniedException();
                }
            }
        }

        if ($lesson['type'] == 'video' and $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);

                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $token = $this->getTokenService()->makeToken('hlsvideo.view', array('data' => $lessonId, 'times' => 1, 'duration' => 3600));
                    $hlsKeyUrl = $this->generateUrl('course_lesson_hlskeyurl', array('courseId' => $lesson['courseId'], 'lessonId' => $lesson['id'], 'token' => $token['token']), true);
                    $headLeaderInfo = $this->getHeadLeaderInfo();
                    $hls = $client->generateHLSEncryptedListUrl($file['convertParams'], $file['metas2'], $hlsKeyUrl, $headLeaderInfo['headLeaders'], $headLeaderInfo['headLeaderHlsKeyUrl'], 3600);
                } else {
                    $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }

            }

        } else if ($lesson['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $lesson['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        } else if ($lesson['mediaSource'] == 'tudou'){
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $lesson['mediaUri'] = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        }
        return $this->render('TopxiaWebBundle:CourseLesson:preview-modal.html.twig', array(
            'user' => $user,
            'course' => $course,
            'lesson' => $lesson,
            'isModal' => $isModal,
            'hlsUrl' => (isset($hls) and is_array($hls) and !empty($hls['url'])) ? $hls['url'] : '',
        ));
	}
    private function convertFiltersToConditions($course, $filters)
    {
        $conditions = array('courseId' => $course['id']);
        switch ($filters['type']) {
            case 'question':
                $conditions['type'] = 'question';
                break;
            case 'elite':
                $conditions['isElite'] = 1;
                break;
            default:
                break;
        }
        return $conditions;
    }
    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all', 'question', 'elite'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }
        return $filters;
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

    private function getHeadLeaderInfo()
    {
        $storage = $this->getSettingService()->get("storage");
        if(!empty($storage) && array_key_exists("video_header", $storage) && $storage["video_header"]){

            $headLeader = $this->getUploadFileService()->getFileByTargetType('headLeader');
            $headLeaderArray = json_decode($headLeader['metas2'],true);
            $headLeaders = array();
            foreach ($headLeaderArray as $key => $value) {
                $headLeaders[$key] = $value['key'];
            }
            $headLeaderHlsKeyUrl = $this->generateUrl('uploadfile_cloud_get_head_leader_hlskey', array(), true);

            return array(
                'headLeaders' => $headLeaders,
                'headLeaderHlsKeyUrl' => $headLeaderHlsKeyUrl,
                'headLength' => $headLeader['length']
            );
        } else {
            return array(
                'headLeaders' => '',
                'headLeaderHlsKeyUrl' => '',
                'headLength' => 0
            );
        }
    }

    
    private function previewAsMember($as, $member, $course)
    {
        $user = $this->getCurrentUser();
        if (empty($user->id)) {
            return null;
        }


        if (in_array($as, array('member', 'guest'))) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $member = array(
                    'id' => 0,
                    'courseId' => $course['id'],
                    'userId' => $user['id'],
                    'levelId' => 0,
                    'learnedNum' => 0,
                    'isLearned' => 0,
                    'seq' => 0,
                    'isVisible' => 0,
                    'role' => 'teacher',
                    'locked' => 0,
                    'createdTime' => time(),
                    'deadline' => 0
                );
            }

            if (empty($member) or $member['role'] != 'teacher') {
                return $member;
            }

            if ($as == 'member') {
                $member['role'] = 'student';
            } else {
                $member = null;
            }
        }

        return $member;
    }

    private function groupCourseItems($items)
    {
        $grouped = array();

        $list = array();
        foreach ($items as $id => $item) {
            if ($item['itemType'] == 'chapter') {
                if (!empty($list)) {
                    $grouped[] = array('type' => 'list', 'data' => $list);
                    $list = array();
                }
                $grouped[] = array('type' => 'chapter', 'data' => $item);
            } else {
                $list[] = $item;
            }
        }

        if (!empty($list)) {
            $grouped[] = array('type' => 'list', 'data' => $list);
        }

        return $grouped;
    }




    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    private function getCustomCourseSearcheService(){
        return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
    }
    private function getCustomCourseService(){
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }
    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    private function getFavoriteDao ()
    {
        return $this->createDao('Course.FavoriteDao');
    }
    private function getStatusService()
    {
    return $this->createService('User.StatusService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getUploadFileService()
    {
    return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    private function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

}