<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;

class GroupController extends BaseController
{
    public function memberCenterAction()
    {
        $user = $this->getUser();

        $groupsCount = $this->getGroupService()->countMembers(array('userId' => $user['id']));
        $members = $this->getGroupService()->searchMembers(array('userId' => $user['id']), array('createdTime' => 'DESC'), 0,
            9);

        $groupIds = ArrayToolkit::column($members, 'groupId');
        $groups = $this->getGroupService()->getGroupsByIds($groupIds);
        $ownThreads = $this->getThreadService()->searchThreads(array('userId' => $user['id']), array('createdTime' => 'DESC'), 0, 10);

        $groupIds = ArrayToolkit::column($ownThreads, 'groupId');
        $threadsCount = $this->getThreadService()->countThreads(array('userId' => $user['id']));
        $groupsAsOwnThreads = $this->getGroupService()->getGroupsByIds($groupIds);

        $userIds = ArrayToolkit::column($ownThreads, 'lastPostMemberId');
        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);
        $collectThreadsIds = $this->getThreadService()->searchThreadCollects(array('userId' => $user['id']), array('id' => 'DESC'), 0, 10);
        $collectThreads = array();

        foreach ($collectThreadsIds as $collectThreadsId) {
            $collectThreads[] = $this->getThreadService()->getThread($collectThreadsId['threadId']);
        }

        $collectCount = $this->getThreadService()->countThreadCollects(array('userId' => $user['id']));

        $groupIdsAsCollectThreads = ArrayToolkit::column($collectThreads, 'groupId');
        $groupsAsCollectThreads = $this->getGroupService()->getGroupsByIds($groupIdsAsCollectThreads);

        $userIds = ArrayToolkit::column($collectThreads, 'lastPostMemberId');
        $collectLastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        $userIds = ArrayToolkit::column($ownThreads, 'lastPostMemberId');
        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        $postThreadsIds = $this->getThreadService()->searchPostsThreadIds(array('userId' => $user['id']), array('id' => 'DESC'), 0, 10);

        $threads = array();

        foreach ($postThreadsIds as $postThreadsId) {
            $threads[] = $this->getThreadService()->getThread($postThreadsId['threadId']);
        }

        $postsCount = $this->getThreadService()->countPostsThreadIds(array('userId' => $user['id']));

        $groupIdsAsPostThreads = ArrayToolkit::column($threads, 'groupId');
        $groupsAsPostThreads = $this->getGroupService()->getGroupsByIds($groupIdsAsPostThreads);

        $userIds = ArrayToolkit::column($threads, 'lastPostMemberId');
        $postLastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('my/learning/group/group-member-center.html.twig', array(
            'user' => $user,
            'groups' => $groups,
            'threads' => $threads,
            'threadsCount' => $threadsCount,
            'postsCount' => $postsCount,
            'collectCount' => $collectCount,
            'groupsAsCollectThreads' => $groupsAsCollectThreads,
            'collectLastPostMembers' => $collectLastPostMembers,
            'collectThreads' => $collectThreads,
            'postLastPostMembers' => $postLastPostMembers,
            'groupsAsPostThreads' => $groupsAsPostThreads,
            'lastPostMembers' => $lastPostMembers,
            'groupsAsOwnThreads' => $groupsAsOwnThreads,
            'ownThreads' => $ownThreads,
            'groupsCount' => $groupsCount, ));
    }

    public function memberJoinAction(Request $request)
    {
        $user = $this->getUser();

        $admins = $this->getGroupService()->searchMembers(array('userId' => $user['id'], 'role' => 'admin'),
            array('createdTime' => 'DESC'), 0, 1000
        );
        $owners = $this->getGroupService()->searchMembers(array('userId' => $user['id'], 'role' => 'owner'),
            array('createdTime' => 'DESC'), 0, 1000
        );
        $members = array_merge($admins, $owners);
        $groupIds = ArrayToolkit::column($members, 'groupId');
        $adminGroups = $this->getGroupService()->getGroupsByIds($groupIds);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->countMembers(array('userId' => $user['id'], 'role' => 'member')),
            12
        );

        $members = $this->getGroupService()->searchMembers(array('userId' => $user['id'], 'role' => 'member'), array('createdTime' => 'DESC'), $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $groupIds = ArrayToolkit::column($members, 'groupId');
        $groups = $this->getGroupService()->getGroupsByIds($groupIds);

        return $this->render('my/learning/group/group-member-join.html.twig', array(
            'user' => $user,
            'adminGroups' => $adminGroups,
            'paginator' => $paginator,
            'groups' => $groups, ));
    }

    public function memberThreadsAction()
    {
        $user = $this->getUser();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->countThreads(array('userId' => $user['id'])),
            12
        );

        $threads = $this->getThreadService()->searchThreads(array('userId' => $user['id']), array('createdTime' => 'DESC'), $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $groupIds = ArrayToolkit::column($threads, 'groupId');

        $userIds = ArrayToolkit::column($threads, 'lastPostMemberId');
        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);
        $groups = $this->getGroupService()->getGroupsByIds($groupIds);

        return $this->render('my/learning/group/group-member-threads.html.twig', array(
            'user' => $user,
            'paginator' => $paginator,
            'lastPostMembers' => $lastPostMembers,
            'threads' => $threads,
            'groups' => $groups, ));
    }

    public function memberPostsAction()
    {
        $user = $this->getUser();
        $threads = array();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->countPostsThreadIds(array('userId' => $user['id'])),
            12
        );

        $postThreadsIds = $this->getThreadService()->searchPostsThreadIds(array('userId' => $user['id']),
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        foreach ($postThreadsIds as $postThreadsId) {
            $threads[] = $this->getThreadService()->getThread($postThreadsId['threadId']);
        }

        $groupIdsAsPostThreads = ArrayToolkit::column($threads, 'groupId');
        $groupsAsPostThreads = $this->getGroupService()->getGroupsByIds($groupIdsAsPostThreads);

        $userIds = ArrayToolkit::column($threads, 'lastPostMemberId');
        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('my/learning/group/group-member-posts.html.twig', array(
            'user' => $user,
            'paginator' => $paginator,
            'threads' => $threads,
            'lastPostMembers' => $lastPostMembers,
            'groups' => $groupsAsPostThreads,
        ));
    }

    public function collectingAction()
    {
        $user = $this->getUser();

        $threads = array();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->countThreadCollects(array('userId' => $user['id'])),
            12
        );

        $collectThreadsIds = $this->getThreadService()->searchThreadCollects(
            array('userId' => $user['id']),
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($collectThreadsIds as $collectThreadsId) {
            $threads[] = $this->getThreadService()->getThread($collectThreadsId['threadId']);
        }

        $groupIdsAsPostThreads = ArrayToolkit::column($threads, 'groupId');
        $groupsAsPostThreads = $this->getGroupService()->getGroupsByIds($groupIdsAsPostThreads);

        $userIds = ArrayToolkit::column($threads, 'lastPostMemberId');
        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('my/learning/group/group-member-collect.html.twig', array(
            'user' => $user,
            'paginator' => $paginator,
            'threads' => $threads,
            'lastPostMembers' => $lastPostMembers,
            'groups' => $groupsAsPostThreads,
        ));
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
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
