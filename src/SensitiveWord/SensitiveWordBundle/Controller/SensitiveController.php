<?php
namespace SensitiveWord\SensitiveWordBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class SensitiveController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields     = $request->query->all();
        $conditions = array(
            'keyword'       => '',
            'searchKeyWord' => '',
            'state'         => ''
        );

        if (empty($fields)) {
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);
        $paginator  = new Paginator($this->get('request'), $this->getSensitiveService()->searchkeywordsCount(), 50);
        $keywords   = $this->getSensitiveService()->searchKeywords($conditions, array('createdTime', 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        return $this->render('SensitiveWordBundle:SensitiveAdmin:index.html.twig', array(
            'keywords'  => $keywords,
            'paginator' => $paginator
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $keyword = $request->request->get('name');
            $keyword = trim($keyword);
            $keyword = preg_split('/\r/', $keyword, -1, PREG_SPLIT_NO_EMPTY);
            $state   = $request->request->get('state');

            foreach ($keyword as $key => $value) {
                $value = trim($value);

                if (!empty($value)) {
                    $keyword = $this->getSensitiveService()->addKeyword($value, $state);
                }
            }

            return $this->redirect($this->generateUrl('admin_keyword'));
        }

        return $this->render('SensitiveWordBundle:SensitiveAdmin:keyword-add.html.twig');
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getSensitiveService()->deleteKeyword($id);
        return $this->redirect($this->generateUrl('admin_keyword'));
    }

    public function changeAction(Request $request, $id)
    {
        $state = $request->query->get('state');

        if ($state == 'banned') {
            $conditions['state'] = 'replaced';
        } else {
            $conditions['state'] = 'banned';
        }

        $this->getSensitiveService()->updateKeyword($id, $conditions);
        return $this->redirect($this->generateUrl('admin_keyword'));
    }

    public function banlogsAction(Request $request)
    {
        $fields     = $request->query->all();
        $conditions = array(
            'keyword'      => '',
            'searchBanlog' => '',
            'state'        => ''
        );

        if (empty($fields)) {
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        if (empty($banlogs)) {
            $banlogs = array();
        }

        if ($conditions['searchBanlog'] == 'userName') {
            $userName = $conditions['keyword'];
            $userTemp = $this->getUserService()->searchUsers(
                array('nickname' => $userName),
                array('createdTime', 'DESC'),
                0,
                1000
            );
            $userIds = ArrayToolkit::column($userTemp, 'id');

            if (!empty($userTemp)) {
                $conditions['userId'] = $userIds;
            } else {
                if (!empty($conditions['keyword'])) {
                    $conditions['userId'] = 0;
                }
            }

            $count = $this->getSensitiveService()->searchBanlogsCount($conditions);

            $paginator = new Paginator($this->get('request'), $count, 50);

            foreach ($userIds as $value) {
                $conditions['userId'] = $value;
                $banlogsTemp          = $this->getSensitiveService()->searchBanlogs($conditions, array(
                    'createdTime',
                    'DESC'
                ), $paginator->getOffsetCount(), $paginator->getPerPageCount());
                $banlogs = array_merge($banlogs, $banlogsTemp);
            }
        } else {
            $count = $this->getSensitiveService()->searchBanlogsCount($conditions);

            $paginator = new Paginator($this->get('request'), $count, 50);

            $banlogs = $this->getSensitiveService()->searchBanlogs($conditions, array(
                'createdTime',
                'DESC'
            ), $paginator->getOffsetCount(), $paginator->getPerPageCount());
        }

        foreach ($banlogs as &$value) {
            $value['text'] = str_replace($value['keywordName'], "<span style='color:#FF0000'>".$value['keywordName']."</span>", $value['text']);
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($banlogs, 'userId'));
        return $this->render('SensitiveWordBundle:SensitiveAdmin:banlogs.html.twig', array(
            'banlogs'   => $banlogs,
            'users'     => $users,
            'paginator' => $paginator
        ));
    }

    protected function getSensitiveService()
    {
        return $this->getServiceKernel()->createService('SensitiveWord:Sensitive.SensitiveService');
    }
}
