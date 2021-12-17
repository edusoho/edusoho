<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\Group\Service\GroupService;
use Biz\Group\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends BaseController
{
    public function indexAction()
    {
        $activeGroup = $this->getGroupService()->searchGroups(['status' => 'open'], ['recommended' => 'DESC', 'recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC', 'memberNum' => 'DESC'], 0, 12);
        $recentlyThread = $this->getThreadService()->searchThreads(
            [
                'createdTime' => time() - 30 * 24 * 60 * 60,
                'status' => 'open',
                'excludeAuditStatus' => 'illegal',
            ],
            $this->filterSort('byStick'), 0, 25
        );

        $ownerIds = ArrayToolkit::column($recentlyThread, 'userId');
        $groupIds = ArrayToolkit::column($recentlyThread, 'groupId');
        $userIds = ArrayToolkit::column($recentlyThread, 'lastPostMemberId');

        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        $groups = $this->getGroupService()->getGroupsByids($groupIds);

        list($user, $myJoinGroup, $newGroups) = $this->_getGroupList();

        return $this->render('group/index.html.twig', [
            'activeGroup' => $activeGroup,
            'myJoinGroup' => $myJoinGroup,
            'lastPostMembers' => $lastPostMembers,
            'owners' => $owners,
            'newGroups' => $newGroups,
            'groupinfo' => $groups,
            'user' => $user,
            'recentlyThread' => $recentlyThread,
        ]);
    }

    public function addGroupAction(Request $request)
    {
        if (true !== $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->createMessageResponse('info', '目前只允许管理员创建小组!');
        }

        $user = $this->getCurrentUser();

        if ('POST' == $request->getMethod()) {
            $mygroup = $request->request->all();

            $group = [
                'title' => $mygroup['group']['grouptitle'],
                'about' => $mygroup['group']['about'],
            ];

            $group = $this->getGroupService()->addGroup($user, $group);

            return $this->redirect($this->generateUrl('group_logo_set', ['id' => $group['id']]));
        }

        return $this->render('group/groupadd.html.twig');
    }

    public function searchAction(Request $request)
    {
        $keyWord = $request->query->get('keyWord') ?: '';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchGroupsCount(['title' => $keyWord, 'status' => 'open']),
            24
        );

        $groups = $this->getGroupService()->searchGroups(
            ['title' => $keyWord, 'status' => 'open'],
            ['recommended' => 'DESC', 'recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC', 'createdTime' => 'DESC'], $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($user, $myJoinGroup, $newGroups) = $this->_getGroupList();

        return $this->render('group/search.html.twig', [
            'paginator' => $paginator,
            'groups' => $groups,
            'user' => $user,
            'myJoinGroup' => $myJoinGroup,
            'newGroups' => $newGroups,
            'keyWord' => $keyWord,
        ]);
    }

    public function groupIndexAction(Request $request, $id)
    {
        $group = $this->getGroupService()->getGroup($id);

        if ('close' == $group['status']) {
            return $this->createMessageResponse('info', '该小组已被关闭');
        }

        list($user, $groupOwner, $recentlyJoinMember, $recentlyMembers, $userIsGroupMember) = $this->_getMemberList($group);

        $filters = $this->getThreadSearchFilters($request);

        $conditions = $this->convertFiltersToConditions($id, $filters);
        $conditions['excludeAuditStatus'] = 'illegal';

        $threadSetting = $this->getSettingService()->get('ugc_thread', []);
        if (empty($threadSetting['enable_thread']) || empty(($threadSetting['enable_group_thread']))) {
            return $this->redirect($this->generateUrl('group_member', ['id' => $group['id']]));
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->countThreads($conditions),
            $conditions['num']
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            $this->filterSort($filters['sort']),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $ownerIds = ArrayToolkit::column($threads, 'userId');

        $userIds = ArrayToolkit::column($threads, 'lastPostMemberId');

        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        $activeMembers = $this->getGroupService()->searchMembers(['groupId' => $id, 'role' => 'member'],
            ['postNum' => 'DESC'], 0, 15);

        $memberIds = ArrayToolkit::column($activeMembers, 'userId');

        $groupAbout = strip_tags($group['about'], '');

        $groupAbout = preg_replace('/ /', '', $groupAbout);

        return $this->render('group/groupindex.html.twig', [
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'recentlyJoinMember' => $recentlyJoinMember,
            'owner' => $owners,
            'user' => $user,
            'groupOwner' => $groupOwner,
            'id' => $id,
            'threads' => $threads,
            'paginator' => $paginator,
            'condition' => $filters,
            'lastPostMembers' => $lastPostMembers,
            'userIsGroupMember' => $userIsGroupMember,
            'members' => $recentlyMembers,
            'groupAbout' => $groupAbout,
        ]);
    }

    public function groupMemberAction(Request $request, $id)
    {
        $group = $this->getGroupService()->getGroup($id);

        if ('close' == $group['status']) {
            return $this->createMessageResponse('info', '该小组已被关闭');
        }

        list($user, $groupOwner, $recentlyJoinMember, $recentlyMembers, $userIsGroupMember) = $this->_getMemberList($group);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->countMembers(['groupId' => $id, 'role' => 'member']),
            30
        );

        $members = $this->getGroupService()->searchMembers(['groupId' => $id, 'role' => 'member'],
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $memberIds = ArrayToolkit::column($members, 'userId');

        $users = $this->getUserService()->findUsersByIds($memberIds);
        $owner = $this->getUserService()->getUser($group['ownerId']);

        $groupAdmin = $this->getGroupService()->searchMembers(['groupId' => $id, 'role' => 'admin'],
            ['createdTime' => 'DESC'],
            0,
            1000);

        $groupAdminIds = ArrayToolkit::column($groupAdmin, 'userId');
        $usersLikeAdmin = $this->getUserService()->findUsersByIds($groupAdminIds);

        return $this->render('group/groupmember.html.twig', [
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'groupmember_info' => $members,
            'owner_info' => $owner,
            'paginator' => $paginator,
            'members' => $users,
            'usersLikeAdmin' => $usersLikeAdmin,
            'groupAdmin' => $groupAdmin,
            'user' => $user,
            'userIsGroupMember' => $userIsGroupMember,
            'groupOwner' => $groupOwner,
            'recentlyJoinMember' => $recentlyJoinMember,
            'recentlyMembers' => $recentlyMembers,
        ]);
    }

    protected function checkManagePermission($id)
    {
        $user = $this->getCurrentUser();

        if (true == $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->getGroupService()->isOwner($id, $user['id'])) {
            return true;
        }

        if ($this->getGroupService()->isAdmin($id, $user['id'])) {
            return true;
        }

        return false;
    }

    protected function checkOwnerPermission($id)
    {
        $user = $this->getCurrentUser();

        if (true == $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->getGroupService()->isOwner($id, $user['id'])) {
            return true;
        }

        return false;
    }

    public function deleteMembersAction(Request $request, $id)
    {
        if (!$this->checkManagePermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        $deleteMemberIds = $request->request->all();

        $group = $this->getGroupService()->getGroup($id);

        if (isset($deleteMemberIds['memberId'])) {
            $deleteMemberIds = $deleteMemberIds['memberId'];

            foreach ($deleteMemberIds as $memberId) {
                $this->getGroupService()->deleteMemberByGroupIdAndUserId($id, $memberId);
                $message = [
                    'id' => $id,
                    'title' => $group['title'],
                    'type' => 'remove', ];
                $this->getNotifiactionService()->notify($memberId, 'group-profile', $message);
            }
        }

        return new Response('success');
    }

    public function setAdminAction(Request $request, $id)
    {
        if (!$this->checkOwnerPermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        $memberIds = $request->request->all();
        $group = $this->getGroupService()->getGroup($id);

        if (isset($memberIds['memberId'])) {
            $memberIds = $memberIds['memberId'];

            foreach ($memberIds as $memberId) {
                $member = $this->getGroupService()->getMemberByGroupIdAndUserId($id, $memberId);
                $this->getGroupService()->updateMember($member['id'], ['role' => 'admin']);
                $message = [
                    'id' => $id,
                    'title' => $group['title'],
                    'type' => 'setAdmin', ];
                $this->getNotifiactionService()->notify($memberId, 'group-profile', $message);
            }
        }

        return new Response('success');
    }

    public function removeAdminAction(Request $request, $id)
    {
        if (!$this->checkOwnerPermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        $memberIds = $request->request->all();

        $group = $this->getGroupService()->getGroup($id);

        if (isset($memberIds['adminId'])) {
            $memberIds = $memberIds['adminId'];
            $message = [
                'id' => $id,
                'title' => $group['title'],
                'type' => 'removeAdmin', ];

            foreach ($memberIds as $memberId) {
                $member = $this->getGroupService()->getMemberByGroupIdAndUserId($id, $memberId);
                $this->getGroupService()->updateMember($member['id'], ['role' => 'member']);
                $this->getNotifiactionService()->notify($memberId, 'group-profile', $message);
            }
        }

        return new Response('success');
    }

    public function groupSetAction(Request $request, $id)
    {
        $group = $this->getGroupService()->getGroup($id);

        if (!$this->checkManagePermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        return $this->render('group/setting-info.html.twig', [
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'id' => $id,
            'logo' => $group['logo'],
            'backgroundLogo' => $group['backgroundLogo'], ]
        );
    }

    public function logoCropAction(Request $request, $id)
    {
        $group = $this->getGroupService()->getGroup($id);

        if (!$this->checkManagePermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        if ('POST' == $request->getMethod()) {
            $options = $request->request->all();

            if ('backGroundLogoCrop' == $request->query->get('page')) {
                $this->getGroupService()->changeGroupImg($id, 'backgroundLogo', $options['images']);
            } else {
                $this->getGroupService()->changeGroupImg($id, 'logo', $options['images']);
            }

            return $this->redirect($this->generateUrl('group_show', [
                'id' => $id,
            ]));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 1140, 150);

        return $this->render('group/setting-logo-crop.html.twig', [
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ]);
    }

    public function setGroupLogoAction(Request $request, $id)
    {
        $group = $this->getGroupService()->getGroup($id);

        if (!$this->checkManagePermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        return $this->render('group/setting-logo.html.twig', [
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'id' => $id,
            'logo' => $group['logo'],
            'backgroundLogo' => $group['backgroundLogo'], ]
        );
    }

    public function setGroupBackgroundLogoAction(Request $request, $id)
    {
        $group = $this->getGroupService()->getGroup($id);

        if (!$this->checkManagePermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        return $this->render('group/setting-background.html.twig', [
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'id' => $id,
            'logo' => $group['backgroundLogo'], ]
        );
    }

    public function hotGroupAction($count = 15, $colNum = 4)
    {
        $hotGroups = $this->getGroupService()->searchGroups(['status' => 'open'], ['memberNum' => 'DESC'], 0, $count);

        return $this->render('group/groups-ul.html.twig', [
            'groups' => $hotGroups,
            'colNum' => $colNum,
        ]
        );
    }

    public function hotThreadAction($textNum = 15)
    {
        $groupSetting = $this->getSettingService()->get('group', []);

        $time = 7 * 24 * 60 * 60;

        if (isset($groupSetting['threadTime_range'])) {
            $time = $groupSetting['threadTime_range'] * 24 * 60 * 60;
        }

        $hotThreads = $this->getThreadService()->searchThreads(
            [
                'createdTime' => time() - $time,
                'status' => 'open',
            ],
            $this->filterSort('byPostNum'), 0, 11
        );

        return $this->render('group/hot-thread.html.twig', [
            'hotThreads' => $hotThreads,
            'textNum' => $textNum,
        ]
        );
    }

    protected function getGroupMemberRole($userId)
    {
        $user = $this->getCurrentUser();

        if (!$user['id']) {
            return 0;
        }

        if ($this->getGroupService()->isOwner($userId, $user['id'])) {
            return 2;
        }

        if ($this->getGroupService()->isAdmin($userId, $user['id'])) {
            return 3;
        }

        if ($this->getGroupService()->isMember($userId, $user['id'])) {
            return 1;
        }

        return 0;
    }

    public function groupJoinAction($id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createJsonResponse([
                'status' => 'error',
                'message' => 'json_response.not_login.message',
            ]);
        }

        $isMember = $this->getGroupService()->isMember($id, $user['id']);

        if ($isMember) {
            return $this->createJsonResponse([
                'status' => 'error',
                'message' => 'json_response.have_joined_group.message',
            ]);
        }

        try {
            $this->getGroupService()->joinGroup($user, $id);
        } catch (\Exception $e) {
            return $this->createJsonResponse([
                'status' => 'error',
                'message' => 'json_response.join_group_failed.message',
            ]);
        }

        return $this->createJsonResponse([
            'status' => 'success',
        ]);
    }

    public function groupExitAction($id)
    {
        $user = $this->getCurrentUser();
        $this->getGroupService()->exitGroup($user, $id);

        return $this->createJsonResponse([
            'status' => 'success',
        ]);
    }

    public function groupEditAction(Request $request, $id)
    {
        if (!$this->checkManagePermission($id)) {
            return $this->createMessageResponse('info', '您没有权限!');
        }

        $groupinfo = $request->request->all();
        $group = [];

        if ($groupinfo) {
            $group = [
                'title' => $groupinfo['group']['grouptitle'],
                'about' => $groupinfo['group']['about'], ];
        }

        $this->getGroupService()->updateGroup($id, $group);

        return $this->redirect($this->generateUrl('group_show', [
            'id' => $id,
        ]));
    }

    protected function filterSort($sort)
    {
        switch ($sort) {
            case 'byPostNum':
                $orderBys = ['isStick' => 'DESC', 'postNum' => 'DESC', 'createdTime' => 'DESC'];
                break;
            case 'byStick':
            case 'byCreatedTime':
                $orderBys = ['isStick' => 'DESC', 'createdTime' => 'DESC'];
                break;
            case 'byLastPostTime':
                $orderBys = ['isStick' => 'DESC', 'lastPostTime' => 'DESC'];
                break;
            case 'byCreatedTimeOnly':
                $orderBys = ['createdTime' => 'DESC'];
                break;
            default:

                $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $orderBys;
    }

    protected function getThreadSearchFilters($request)
    {
        $filters = [];
        $filters['type'] = $request->query->get('type');

        if (!in_array($filters['type'], ['all', 'elite', 'reward'])) {
            $filters['type'] = 'all';
        }

        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], ['byCreatedTime', 'byLastPostTime', 'byPostNum'])) {
            $filters['sort'] = 'byCreatedTime';
        }

        $filters['num'] = $request->query->get('num');

        if (!in_array($filters['num'], [25])) {
            $filters['num'] = 25;
        }

        return $filters;
    }

    protected function convertFiltersToConditions($id, $filters)
    {
        $conditions = ['groupId' => $id, 'num' => 10, 'status' => 'open'];

        switch ($filters['type']) {
            case 'elite':
                $conditions['isElite'] = 1;
                break;
            case 'reward':
                $conditions['type'] = 'reward';
                break;
            default:
                break;
        }

        $conditions['num'] = $filters['num'];

        return $conditions;
    }

    protected function _getGroupList()
    {
        $user = $this->getCurrentUser();

        $myJoinGroup = [];

        if ($user['id']) {
            $membersCount = $this->getGroupService()->countMembers(['userId' => $user['id']]);

            $start = $membersCount > 12 ? rand(0, $membersCount - 12) : 0;

            $members = $this->getGroupService()->searchMembers(['userId' => $user['id']], ['createdTime' => 'DESC'], $start, 12);

            $groupIds = ArrayToolkit::column($members, 'groupId');

            $myJoinGroup = $this->getGroupService()->getGroupsByids($groupIds);
        }

        $newGroups = $this->getGroupService()->searchGroups(['status' => 'open'], ['createdTime' => 'DESC'], 0, 8);

        return [$user, $myJoinGroup, $newGroups];
    }

    protected function _getMemberList($group)
    {
        $user = $this->getCurrentUser();

        $groupOwner = $this->getUserService()->getUser($group['ownerId']);

        $recentlyJoinMember = $this->getGroupService()->searchMembers(['groupId' => $group['id']], ['createdTime' => 'DESC'], 0, 20);

        $memberIds = ArrayToolkit::column($recentlyJoinMember, 'userId');

        $recentlyMembers = $this->getUserService()->findUsersByIds($memberIds);

        $userIsGroupMember = $this->getGroupService()->getMemberByGroupIdAndUserId($group['id'], $user['id']);

        return [$user, $groupOwner, $recentlyJoinMember, $recentlyMembers, $userIsGroupMember];
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Group:ThreadService');
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->getBiz()->service('Group:GroupService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotifiactionService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->getBiz()->service('Content:FileService');
    }
}
