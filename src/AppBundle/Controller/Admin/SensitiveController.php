<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class SensitiveController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'keyword' => '',
            'searchKeyWord' => '',
            'state' => '',
        );

        if (empty($fields)) {
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);
        $paginator = new Paginator($this->get('request'), $this->getSensitiveService()->searchkeywordsCount($conditions), 20);
        $keywords = $this->getSensitiveService()->searchKeywords($conditions, array('id' => 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        return $this->render('admin/sensitive/index.html.twig', array(
            'keywords' => $keywords,
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $name = $request->request->get('name');
            $keywords = preg_split('/\r/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
            $state = $request->request->get('state');
            $existedKeyWords = array();
            foreach ($keywords as $value) {
                $value = trim($value);

                if (!empty($value)) {
                    $keyword = $this->getSensitiveService()->getKeywordByName($value);

                    if (empty($keyword)) {
                        $this->getSensitiveService()->addKeyword($value, $state);
                        continue;
                    }
                    $existedKeyWords[] = $value;
                }
            }
            if (!empty($existedKeyWords)) {
                $this->setFlashMessage(
                    'warning',
                    $this->get('translator')->trans(
                        'admin.sensitive_manage.existed',
                        array('%keywords%' => implode(',', array_unique($existedKeyWords)))
                    )
                );
            }

            return $this->redirect($this->generateUrl('admin_keyword'));
        }

        return $this->render('admin/sensitive/keyword-add.html.twig');
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getSensitiveService()->deleteKeyword($id);

        return $this->redirect($this->generateUrl('admin_keyword'));
    }

    public function changeAction(Request $request, $id)
    {
        $state = $request->query->get('state');

        if ('banned' == $state) {
            $conditions['state'] = 'replaced';
        } else {
            $conditions['state'] = 'banned';
        }

        $this->getSensitiveService()->updateKeyword($id, $conditions);

        return $this->redirect($this->generateUrl('admin_keyword'));
    }

    public function banlogsAction(Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'keyword' => '',
            'searchBanlog' => '',
            'state' => '',
        );

        if (empty($fields)) {
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        if (empty($banlogs)) {
            $banlogs = array();
        }

        if ('userName' == $conditions['searchBanlog']) {
            $userName = $conditions['keyword'];
            $userTemp = $this->getUserService()->searchUsers(
                array('nickname' => $userName),
                array('createdTime' => 'DESC'),
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
            if (empty($count)) {
                $count = 0;
            }
            foreach ($userIds as $value) {
                $conditions['userId'] = $value;
                $countTemp = $this->getSensitiveService()->searchBanlogsCount($conditions);
                $count += $countTemp;
            }
            $paginator = new Paginator($this->get('request'), $count, 20);
            $banlogs = $this->getSensitiveService()->searchBanlogsByUserIds($userIds, array(
                'id' => 'DESC',
            ), $paginator->getOffsetCount(), $paginator->getPerPageCount());
        } else {
            $count = $this->getSensitiveService()->searchBanlogsCount($conditions);
            $paginator = new Paginator($this->get('request'), $count, 20);

            $banlogs = $this->getSensitiveService()->searchBanlogs($conditions, array(
                'id' => 'DESC',
            ), $paginator->getOffsetCount(), $paginator->getPerPageCount());
        }

        foreach ($banlogs as &$value) {
            $value['text'] = str_replace($value['keywordName'], "<span style='color:#FF0000'>".$value['keywordName'].'</span>', $value['text']);
            $value['text'] = preg_replace("/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i", '', $value['text']);
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($banlogs, 'userId'));

        return $this->render('admin/sensitive/banlogs.html.twig', array(
            'banlogs' => $banlogs,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }
}
