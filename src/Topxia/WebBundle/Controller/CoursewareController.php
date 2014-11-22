<?php 
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Util\CCVideoClientFactory;

class CoursewareController extends BaseController
{
    public function getVideoInfoAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $factory = new CCVideoClientFactory();
            $client = $factory->createClient();
            $courseware = $request->request->all();
            $userIdAndVideoId = $this->getUserIdAndVideoId($courseware['url']);
            $videoInfo = $client->getVideoInfo($userIdAndVideoId['userId'],$userIdAndVideoId['videoId']);
            $videoInfo = json_decode($videoInfo);

            if(!empty($videoInfo)){
                $title = $videoInfo->video->title;
                return $this->createJsonResponse(array("title" => $title ));
            } else {
                return $this->createJsonResponse(array("status" => "failed")); 
            }
        }

    }

    public function knowledgeMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');

        $knowledges = $this->getKnowledgeService()->searchKnowledge(array('keywords'=>$likeString),array('createdTime', 'DESC'), 0, 10);
        return $this->createJsonResponse($knowledges);
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }

    private function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param)
        {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    private function getUserIdAndVideoId($url)
    {
        $query = parse_url($url);
        $querys = $this->convertUrlQuery($query['query']);
        $queryArr = explode('_', $querys['videoID']);
        return array(
            'userId' => $queryArr[0],
            'videoId' => $queryArr[1]
        );
    }
}