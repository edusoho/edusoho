<?php

namespace AppBundle\Controller\AdminV2\Operating;

use Biz\Common\CommonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Biz\Group\Service\ThreadService;
use Biz\Group\Service\GroupService;

class GroupController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'status' => '',
            'title' => '',
            'ownerName' => '',
        );

        if (!empty($fields)) {
            $conditions = $fields;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchGroupsCount($conditions),
            10
        );

        $groupinfo = $this->getGroupService()->searchGroups(
                $conditions,
                array('createdTime' => 'desc'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        $ownerIds = ArrayToolkit::column($groupinfo, 'ownerId');
        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        return $this->render('admin-v2/operating/group/index.html.twig', array(
            'groupinfo' => $groupinfo,
            'owners' => $owners,
            'paginator' => $paginator, ));
    }

    public function threadAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->prepareThreadConditions($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->countThreads($conditions),
            10
        );

        $threadinfo = $this->getThreadService()->searchThreads(
            $conditions,
            $this->filterSort('byCreatedTime'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $memberIds = ArrayToolkit::column($threadinfo, 'userId');

        $owners = $this->getUserService()->findUsersByIds($memberIds);

        $groupIds = ArrayToolkit::column($threadinfo, 'groupId');

        $group = $this->getGroupService()->getGroupsByIds($groupIds);

        return $this->render('admin-v2/operating/group/thread.html.twig', array(
            'threadinfo' => $threadinfo,
            'owners' => $owners,
            'group' => $group,
            'paginator' => $paginator, ));
    }

    public function batchDeleteThreadAction(Request $request)
    {
        $threadIds = $request->request->all();
        $user = $this->getUser();
        foreach ($threadIds['ID'] as $threadId) {
            $thread = $this->getThreadService()->getThread($threadId);
            $message = array(
                'title' => $thread['title'],
                'type' => 'delete',
                'userId' => $user['id'],
                'userName' => $user['nickname'], );
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread',
                $message);
            $this->getThreadService()->deleteThread($threadId);
        }

        return new Response('success');
    }

    public function openGroupAction($id)
    {
        $this->getGroupService()->openGroup($id);

        $groupinfo = $this->getGroupService()->getGroup($id);

        $owners = $this->getUserService()->findUsersByIds(array('0' => $groupinfo['ownerId']));

        return $this->render('admin-v2/operating/group/table-tr.html.twig', array(
            'group' => $groupinfo,
            'owners' => $owners,
        ));
    }

    public function closeGroupAction($id)
    {
        $this->getGroupService()->closeGroup($id);

        $groupinfo = $this->getGroupService()->getGroup($id);

        $owners = $this->getUserService()->findUsersByIds(array('0' => $groupinfo['ownerId']));

        return $this->render('admin-v2/operating/group/table-tr.html.twig', array(
            'group' => $groupinfo,
            'owners' => $owners,
        ));
    }

    public function transferGroupAction(Request $request, $groupId)
    {
        $data = $request->request->all();
        $currentUser = $this->getUser();
        $user = $this->getUserService()->getUserByNickname($data['user']['nickname']);

        $group = $this->getGroupService()->getGroup($groupId);

        $ownerId = $group['ownerId'];

        $member = $this->getGroupService()->getMemberByGroupIdAndUserId($groupId, $ownerId);

        $this->getGroupService()->updateMember($member['id'], array('role' => 'member'));

        if ($currentUser['id'] != $group['ownerId']) {
            $message = array(
                'id' => $group['id'],
                'title' => $group['title'],
                'userId' => $user['id'],
                'userName' => $user['nickname'],
                'type' => 'chownout', );
            $this->getNotifiactionService()->notify($group['ownerId'], 'group-profile', $message);
        }

        $this->getGroupService()->updateGroup($groupId, array('ownerId' => $user['id']));

        if ($currentUser['id'] != $user['id']) {
            $message = array(
                'id' => $group['id'],
                'title' => $group['title'],
                'type' => 'chownin', );
            $this->getNotifiactionService()->notify($user['id'], 'group-profile', $message);
        }

        $member = $this->getGroupService()->getMemberByGroupIdAndUserId($groupId, $user['id']);

        if ($member) {
            $this->getGroupService()->updateMember($member['id'], array('role' => 'owner'));
        } else {
            $this->getGroupService()->addOwner($groupId, $user['id']);
        }

        return new Response('success');
    }

    public function removeEliteAction($threadId)
    {
        return $this->postAction($threadId, 'removeElite');
    }

    public function setEliteAction($threadId)
    {
        return $this->postAction($threadId, 'setElite');
    }

    public function removeStickAction($threadId)
    {
        return $this->postAction($threadId, 'removeStick');
    }

    public function setStickAction($threadId)
    {
        return $this->postAction($threadId, 'setStick');
    }

    public function closeThreadAction($threadId)
    {
        return $this->postAction($threadId, 'closeThread');
    }

    public function openThreadAction($threadId)
    {
        return $this->postAction($threadId, 'openThread');
    }

    public function deleteThreadAction($threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->deleteThread($threadId);

        $user = $this->getUser();

        $message = array(
            'title' => $thread['title'],
            'type' => 'delete',
            'userId' => $user['id'],
            'userName' => $user['nickname'], );
        $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);

        return $this->createJsonResponse('success');
    }

    protected function postAction($threadId, $action)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $message = array(
            'title' => $thread['title'],
            'groupId' => $thread['groupId'],
            'threadId' => $thread['id'],
            );
        if ('setElite' == $action) {
            $this->getThreadService()->setElite($threadId);
            $message['type'] = 'elite';
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }
        if ('removeElite' == $action) {
            $this->getThreadService()->removeElite($threadId);
            $message['type'] = 'unelite';
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }
        if ('setStick' == $action) {
            $this->getThreadService()->setStick($threadId);
            $message['type'] = 'top';
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }
        if ('removeStick' == $action) {
            $this->getThreadService()->removeStick($threadId);
            $message['type'] = 'untop';
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }
        if ('closeThread' == $action) {
            $this->getThreadService()->closeThread($threadId);
            $message['type'] = 'close';
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }
        if ('openThread' == $action) {
            $this->getThreadService()->openThread($threadId);
            $message['type'] = 'open';
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }

        $thread = $this->getThreadService()->getThread($threadId);

        $owners = $this->getUserService()->findUsersByIds(array('0' => $thread['userId']));

        $group = $this->getGroupService()->getGroupsByIds(array('0' => $thread['groupId']));

        return $this->render('admin-v2/operating/group/thread-table-tr.html.twig', array(
            'thread' => $thread,
            'owners' => $owners,
            'group' => $group,
        ));
    }

    protected function filterSort($sort)
    {
        switch ($sort) {
            case 'byPostNum':
                $orderBys = array(
                    'isStick' => 'DESC',
                    'postNum' => 'DESC',
                    'createdTime' => 'DESC',
                );
                break;
            case 'byStick':
                $orderBys = array(
                    'isStick' => 'DESC',
                    'createdTime' => 'DESC',
                );
                break;
            case 'byCreatedTime':
                $orderBys = array(
                    'createdTime' => 'DESC',
                );
                break;
            case 'byLastPostTime':
                $orderBys = array(
                    'isStick' => 'DESC',
                    'lastPostTime' => 'DESC',
                );
                break;
            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $orderBys;
    }

    protected function prepareThreadConditions($conditions)
    {
        if (isset($conditions['threadType']) && !empty($conditions['threadType'])) {
            $conditions[$conditions['threadType']] = 1;
            unset($conditions['threadType']);
        }

        if (isset($conditions['groupName']) && '' !== $conditions['groupName']) {
            $group = $this->getGroupService()->findGroupByTitle($conditions['groupName']);
            if (!empty($group)) {
                $conditions['groupId'] = $group[0]['id'];
            } else {
                $conditions['groupId'] = 0;
            }
        }

        if (isset($conditions['userName']) && '' !== $conditions['userName']) {
            $user = $this->getUserService()->getUserByNickname($conditions['userName']);
            if (!empty($user)) {
                $conditions['userId'] = $user['id'];
            } else {
                $conditions['userId'] = 0;
            }
        }

        if (empty($conditions['status'])) {
            unset($conditions['status']);
        }

        return $conditions;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Group:ThreadService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotifiactionService()
    {
        return $this->createService('User:NotificationService');
    }
}
