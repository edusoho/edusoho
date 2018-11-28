<?php

namespace AppBundle\Controller;

use AppBundle\Common\Exception\FileToolkitException;
use Biz\Content\FileException;
use Biz\Content\Service\FileService;
use Biz\File\Service\UploadFileService;
use Biz\Group\Service\GroupService;
use Biz\Group\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupThreadController extends BaseController
{
    public function addThreadAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        $groupinfo = $this->getGroupService()->getGroup($id);

        if (!$groupinfo) {
            return $this->createMessageResponse('info', '该小组已被关闭');
        }

        if (!$this->getGroupMemberRole($id)) {
            return $this->createMessageResponse('info', '只有小组成员可以发言');
        }

        if ($request->getMethod() == 'POST') {
            try {
                $threadData = $request->request->all();

                $info = array(
                    'title' => $threadData['thread']['title'],
                    'content' => $threadData['thread']['content'],
                    'groupId' => $id,
                    'userId' => $user['id'],
                );

                $thread = $this->getThreadService()->addThread($info);

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                if (isset($threadData['file'])) {
                    $file = $threadData['file'];
                    $this->getThreadService()->addAttach($file, $thread['id']);
                }

                return $this->redirect($this->generateUrl('group_thread_show', array(
                    'id' => $id,
                    'threadId' => $thread['id'],
                )));
            } catch (\Exception $e) {
                return $this->createMessageResponse('error', $this->trans($e->getMessage()), '错误提示', 1, $request->getPathInfo());
            }
        }

        return $this->render('group/add-thread.html.twig',
            array(
                'id' => $id,
                'groupinfo' => $groupinfo,
                'is_groupmember' => $this->getGroupMemberRole($id),
            ));
    }

    public function updateThreadAction(Request $request, $id, $threadId)
    {
        $user = $this->getCurrentUser();
        $groupinfo = $this->getGroupService()->getGroup($id);

        if (!$groupinfo) {
            return $this->createMessageResponse('info', '该小组已被关闭');
        }

        $thread = $this->getThreadService()->getThread($threadId);

        if (!$this->checkManagePermission($id, $thread)) {
            return $this->createMessageResponse('info', '您没有权限编辑');
        }

        $thread = $this->getThreadService()->getThread($threadId);

        $attachs = $this->getThreadService()->searchGoods(array('threadId' => $thread['id'], 'type' => 'attachment'), array('createdTime' => 'DESC'), 0, 1000);

        if ($request->getMethod() == 'POST') {
            try {
                $threadData = $request->request->all();
                $fields = array(
                    'title' => $threadData['thread']['title'],
                    'content' => $threadData['thread']['content'],
                );

                $thread = $this->getThreadService()->updateThread($threadId, $fields);

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                if (isset($threadData['file'])) {
                    $file = $threadData['file'];
                    $this->getThreadService()->addAttach($file, $thread['id']);
                }

                if ($user->isAdmin()) {
                    $message = array(
                        'id' => $id,
                        'threadId' => $thread['id'],
                        'title' => $thread['title'],
                        'type' => 'modify',
                    );
                    $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
                }

                return $this->redirect($this->generateUrl('group_thread_show', array(
                    'id' => $id,
                    'threadId' => $threadId,
                )));
            } catch (\Exception $e) {
                return $this->createMessageResponse('error', $this->trans($e->getMessage()), '错误提示', 1, $request->getPathInfo());
            }
        }

        return $this->render('group/add-thread.html.twig', array(
            'id' => $id,
            'groupinfo' => $groupinfo,
            'thread' => $thread,
            'attachs' => $attachs,
            'is_groupmember' => $this->getGroupMemberRole($id),
        ));
    }

    public function checkUserAction(Request $request)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);

        if ($result) {
            $response = array('success' => false, 'message' => 'json_response.user_not_found.message');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    public function collectAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '消息提示', '您尚未登录，不能收藏话题！');
        }

        $threadMain = $this->getThreadService()->getThread($threadId);

        $this->getThreadService()->threadCollect($user['id'], $threadId);

        $message = array(
            'id' => $threadMain['groupId'],
            'threadId' => $threadMain['id'],
            'title' => $threadMain['title'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'type' => 'collect',
        );
        $this->getNotifiactionService()->notify($threadMain['userId'], 'group-thread', $message);

        return $this->createJsonResponse(true);
    }

    public function uncollectAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '消息提示', '您尚未登录，不能收藏话题！');
        }

        $threadMain = $this->getThreadService()->getThread($threadId);

        $this->getThreadService()->unThreadCollect($user['id'], $threadId);

        $message = array(
            'id' => $threadMain['groupId'],
            'threadId' => $threadMain['id'],
            'title' => $threadMain['title'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'type' => 'uncollect',
        );
        $this->getNotifiactionService()->notify($threadMain['userId'], 'group-thread', $message);

        return $this->createJsonResponse(true);
    }

    public function groupThreadIndexAction(Request $request, $id, $threadId)
    {
        $group = $this->getGroupService()->getGroup($id);

        if ($group['status'] == 'close') {
            return $this->createMessageResponse('info', '该小组已被关闭');
        }

        $user = $this->getCurrentUser();

        $threadMain = $this->getThreadService()->getThread($threadId);

        if (empty($threadMain)) {
            return $this->createMessageResponse('info', '该话题已被管理员删除');
        }

        if ($threadMain['status'] == 'close') {
            return $this->createMessageResponse('info', '该话题已被关闭');
        }

        if ($threadMain['status'] != 'close') {
            $isCollected = $this->getThreadService()->isCollected($this->getCurrentUser()->id, $threadMain['id']);
        } else {
            $isCollected = false;
        }

        $this->getThreadService()->waveHitNum($threadId);

        if ($request->query->get('post')) {
            $url = $this->getPost($request->query->get('post'), $threadId, $id);

            return $this->redirect($url);
        }

        $owner = $this->getUserService()->getUser($threadMain['userId']);

        $filters = $this->getPostSearchFilters($request);

        $condition = $this->getPostCondition($filters['type'], $threadMain['userId'], $threadId);

        $sort = $this->getPostOrderBy($filters['sort']);

        $postCount = $this->getThreadService()->searchPostsCount($condition);

        $paginator = new Paginator(
            $this->get('request'),
            $postCount,
            30
        );

        $post = $this->getThreadService()->searchPosts($condition, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $postMemberIds = ArrayToolkit::column($post, 'userId');

        $postId = ArrayToolkit::column($post, 'id');

        $postReplyAll = array();
        $postReply = array();
        $postReplyCount = array();
        $postReplyPaginator = array();
        $postFiles = array();

        foreach ($postId as $value) {
            $replyCount = $this->getThreadService()->searchPostsCount(array('postId' => $value));
            $replyPaginator = new Paginator(
                $this->get('request'),
                $replyCount,
                10
            );

            $reply = $this->getThreadService()->searchPosts(array('postId' => $value), array('createdTime' => 'ASC'),
                $replyPaginator->getOffsetCount(),
                $replyPaginator->getPerPageCount());

            $postReplyCount[$value] = $replyCount;
            $postReply[$value] = $reply;
            $postReplyPaginator[$value] = $replyPaginator;

            if ($reply) {
                $postReplyAll = array_merge($postReplyAll, ArrayToolkit::column($reply, 'userId'));
            }
        }

        $postReplyMembers = $this->getUserService()->findUsersByIds($postReplyAll);
        $postMember = $this->getUserService()->findUsersByIds($postMemberIds);

        $activeMembers = $this->getGroupService()->searchMembers(array('groupId' => $id),
            array('postNum' => 'DESC'), 0, 20);

        $memberIds = ArrayToolkit::column($activeMembers, 'userId');
        $members = $this->getUserService()->findUsersByIds($memberIds);

        $isAdopt = $this->getThreadService()->searchPosts(array('adopt' => 1, 'threadId' => $threadId), array('createdTime' => 'DESC'), 0, 1);

        $threadMain = $this->hideThings($threadMain);

        $threadMainContent = strip_tags($threadMain['content'], '');

        $threadMainContent = preg_replace('/ /', '', $threadMainContent);

        return $this->render('group/thread.html.twig', array(
            'groupinfo' => $group,
            'isCollected' => $isCollected,
            'threadMain' => $threadMain,
            'user' => $user,
            'owner' => $owner,
            'post' => $post,
            'paginator' => $paginator,
            'postMember' => $postMember,
            'filters' => $filters,
            'postCount' => $postCount,
            'postReply' => $postReply,
            'activeMembers' => $activeMembers,
            'postReplyMembers' => $postReplyMembers,
            'members' => $members,
            'postReplyCount' => $postReplyCount,
            'postReplyPaginator' => $postReplyPaginator,
            'isAdopt' => $isAdopt,
            'threadMainContent' => $threadMainContent,
            'is_groupmember' => $this->getGroupMemberRole($id),
        ));
    }

    public function postReplyAction(Request $request, $postId)
    {
        $postReplyAll = array();

        $replyCount = $this->getThreadService()->searchPostsCount(array('postId' => $postId));

        $postReplyPaginator = new Paginator(
            $this->get('request'),
            $replyCount,
            10
        );

        $postReply = $this->getThreadService()->searchPosts(array('postId' => $postId), array('createdTime' => 'ASC'),
            $postReplyPaginator->getOffsetCount(),
            $postReplyPaginator->getPerPageCount());

        if ($postReply) {
            $postReplyAll = array_merge($postReplyAll, ArrayToolkit::column($postReply, 'userId'));
        }

        $postReplyMembers = $this->getUserService()->findUsersByIds($postReplyAll);

        return $this->render('group/thread-reply-list.html.twig', array(
            'postMain' => array('id' => $postId),
            'postReply' => $postReply,
            'postReplyMembers' => $postReplyMembers,
            'postReplyCount' => $replyCount,
            'postReplyPaginator' => $postReplyPaginator,
        ));
    }

    public function postThreadAction(Request $request, $groupId, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        if (!$this->getGroupMemberRole($groupId)) {
            $this->getGroupService()->joinGroup($user, $groupId);
        }

        $thread = $this->getThreadService()->getThread($threadId);

        $postContent = $request->request->all();

        $fromUserId = empty($postContent['fromUserId']) ? 0 : $postContent['fromUserId'];
        $content = array(
            'content' => $postContent['content'], 'fromUserId' => $fromUserId,
        );

        if (isset($postContent['postId'])) {
            $post = $this->getThreadService()->postThread($content, $groupId, $user['id'], $threadId, $postContent['postId']);
        } else {
            $post = $this->getThreadService()->postThread($content, $groupId, $user['id'], $threadId);

            if (isset($postContent['file'])) {
                $file = $postContent['file'];
                $this->getThreadService()->addPostAttach($file, $thread['id'], $post['id']);
            }
        }

        $attachment = $request->request->get('attachment');
        $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $post['id'], $attachment['targetType'], $attachment['type']);

        $message = array(
            'id' => $groupId,
            'threadId' => $thread['id'],
            'title' => $thread['title'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'page' => $this->getPostPage($post['id'], $threadId),
            'post' => $post['id'],
            'type' => 'reply',
        );

        if ($user->id != $thread['userId']) {
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }

        if (empty($fromUserId) && !empty($postContent['postId'])) {
            $post = $this->getThreadService()->getPost($postContent['postId']);

            if ($post['userId'] != $user->id && $post['userId'] != $thread['userId']) {
                $this->getNotifiactionService()->notify($post['userId'], 'group-thread', $message);
            }
        }

        if (!empty($fromUserId) && $fromUserId != $user->id && $fromUserId != $thread['userId']) {
            $this->getNotifiactionService()->notify($postContent['fromUserId'], 'group-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function searchResultAction(Request $request, $id)
    {
        $keyWord = $request->query->get('keyWord') ?: '';
        $group = $this->getGroupService()->getGroup($id);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->countThreads(array('status' => 'open', 'title' => $keyWord, 'groupId' => $id)),
            15
        );
        $threads = $this->getThreadService()->searchThreads(
            array('status' => 'open', 'title' => $keyWord, 'groupId' => $id),
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $ownerIds = ArrayToolkit::column($threads, 'userId');

        $userIds = ArrayToolkit::column($threads, 'lastPostMemberId');

        $owner = $this->getUserService()->findUsersByIds($ownerIds);

        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('group/group-search-result.html.twig', array(
            'groupinfo' => $group,
            'keyWord' => $keyWord,
            'threads' => $threads,
            'owner' => $owner,
            'paginator' => $paginator,
            'lastPostMembers' => $lastPostMembers,
            'is_groupmember' => $this->getGroupMemberRole($id),
        ));
    }

    public function setEliteAction($threadId)
    {
        return $this->postAction($threadId, 'setElite');
    }

    public function removeEliteAction($threadId)
    {
        return $this->postAction($threadId, 'removeElite');
    }

    public function setStickAction($threadId)
    {
        return $this->postAction($threadId, 'setStick');
    }

    public function removeStickAction($threadId)
    {
        return $this->postAction($threadId, 'removeStick');
    }

    public function closeThreadAction($threadId, $memberId)
    {
        $thread = $this->getThreadService()->getThread($threadId);

        $groupMemberRole = $this->getGroupMemberRole($thread['groupId']);

        if ($groupMemberRole == 2 || $thread['userId'] == $memberId) {
            $this->getThreadService()->closeThread($threadId);
        }

        return new Response($this->generateUrl('group_show', array(
            'id' => $thread['groupId'],
        )));
    }

    public function deletePostAction($postId)
    {
        $post = $this->getThreadService()->getPost($postId);

        $thread = $this->getThreadService()->getThread($post['threadId']);

        $groupMemberRole = $this->getGroupMemberRole($thread['groupId']);

        $user = $this->getCurrentUser();

        if ($user['id'] == $post['userId'] || $groupMemberRole == 2 || $groupMemberRole == 3 || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') == true) {
            $this->getThreadService()->deletePost($postId);

            $thread = $this->getThreadService()->getThread($post['threadId']);
            $message = array(
                'id' => $thread['groupId'],
                'threadId' => $thread['id'],
                'title' => $thread['title'],
                'type' => 'delete-post',
            );
            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }

        return new Response($this->generateUrl('group_thread_show', array(
            'id' => $thread['groupId'], 'threadId' => $post['threadId'],
        )));
    }

    public function adoptAction($postId)
    {
        $post = $this->getThreadService()->getPost($postId);

        $thread = $this->getThreadService()->getThread($post['threadId']);

        $groupMemberRole = $this->getGroupMemberRole($thread['groupId']);

        $isAdopt = $this->getThreadService()->searchPosts(array('adopt' => 1, 'threadId' => $post['threadId']), array('createdTime', 'desc'), 0, 1);

        if ($isAdopt) {
            goto response;
        }

        if ($groupMemberRole == 2 || $groupMemberRole == 3 || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') == true) {
            $post = $this->getThreadService()->updatePost($post['id'], array('adopt' => 1));
        }

        response:
        return new Response($this->generateUrl('group_thread_show', array(
            'id' => $thread['groupId'], 'threadId' => $post['threadId'],
        )));
    }

    protected function postAction($threadId, $action)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $groupMemberRole = $this->getGroupMemberRole($thread['groupId']);

        $message = array(
            'title' => $thread['title'],
            'groupId' => $thread['groupId'],
            'threadId' => $thread['id'],
        );

        if ($groupMemberRole == 2 || $groupMemberRole == 3 || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') == true) {
            if ($action == 'setElite') {
                $this->getThreadService()->setElite($threadId);
                $message['type'] = 'elite';
                $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
            }

            if ($action == 'removeElite') {
                $this->getThreadService()->removeElite($threadId);
                $message['type'] = 'unelite';
                $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
            }

            if ($action == 'setStick') {
                $this->getThreadService()->setStick($threadId);
                $message['type'] = 'top';
                $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
            }

            if ($action == 'removeStick') {
                $this->getThreadService()->removeStick($threadId);
                $message['type'] = 'untop';
                $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
            }
        }

        return new Response($this->generateUrl('group_thread_show', array(
            'id' => $thread['groupId'],
            'threadId' => $threadId,
        )));
    }

    protected function getPost($postId, $threadId, $id)
    {
        $post = $this->getThreadService()->getPost($postId);

        if ($post['postId'] != 0) {
            $postId = $post['postId'];
        }

        $page = $this->getPostPage($postId, $threadId);

        $url = $this->generateUrl('group_thread_show', array('id' => $id, 'threadId' => $threadId));

        $url = $url."?page=$page#post-$postId";

        return $url;
    }

    public function getPostPage($postId, $threadId)
    {
        $count = $this->getThreadService()->countThreads(array('threadId' => $threadId, 'status' => 'open', 'id' => $postId, 'postId' => 0));

        return floor(($count) / 30) + 1;
    }

    public function hideThings($thread)
    {
        $thread['content'] = str_replace('#', '<!--></>', $thread['content']);
        $thread['content'] = str_replace('[hide=reply', '#[hide=reply', $thread['content']);
        $thread['content'] = str_replace('[hide=coin', '#[hide=coin', $thread['content']);
        $data = explode('[/hide]', $thread['content']);

        $user = $this->getCurrentUser();
        $role = $this->getGroupMemberRole($thread['groupId']);
        $context = '';
        $count = 0;

        foreach ($data as $value) {
            $value = ' '.$value;
            sscanf($value, '%[^#]#[hide=coin%[^]]]%[^$$]', $content, $coin, $hideContent);

            sscanf($value, '%[^#]#[hide=reply]%[^$$]', $replyContent, $replyHideContent);

            $trade = $this->getThreadService()->getTradeByUserIdAndThreadId($user->id, $thread['id']);

            if ($role == 2 || $role == 3 || $user['id'] == $thread['userId'] || !empty($trade)) {
                if ($coin) {
                    if ($role == 2 || $role == 3 || $user['id'] == $thread['userId']) {
                        $context .= $content."<div class=\"hideContent mtl mbl clearfix\"><span class=\"pull-right\" style='font-size:10px;'>".'隐藏区域'.'</span>'.$hideContent.'</div>';
                    } else {
                        $context .= $content.$hideContent;
                    }
                } else {
                    $context .= $content;
                }
            } else {
                if ($coin) {
                    $count = 1;

                    if ($user['id']) {
                        $context .= $content."<div class=\"hideContent mtl mbl\"><h4> <a href=\"javascript:\" data-toggle=\"modal\" data-target=\"#modal\" data-urL=\"/thread/{$thread['id']}/hide\">".'点击查看'.'</a>'.'本话题隐藏内容'.'</h4></div>';
                    } else {
                        $context .= $content.'<div class="hideContent mtl mbl"><h4>'.'游客,如果您要查看本话题隐藏内容请先'.'<a href="/login">'.'登录'.'</a>'.'或'.'<a href="/register">'.'注册'.'</a>！</h4></div>';
                    }
                } else {
                    $context .= $content;
                }
            }

            if ($replyHideContent) {
                $context .= $this->replyCanSee($role, $thread, $content, $replyHideContent);
            }

            unset($coin);
            unset($content);
            unset($replyHideContent);
            unset($hideContent);
            unset($replyContent);
        }

        if ($context) {
            $thread['content'] = $context;
        }

        $thread['count'] = $count;

        $thread['content'] = str_replace('<!--></>', '#', $thread['content']);

        return $thread;
    }

    protected function replyCanSee($role, $thread, $content, $replyHideContent)
    {
        $context = '';
        $user = $this->getCurrentUser();

        if ($replyHideContent) {
            if ($role == 2 || $role == 3 || $user['id'] == $thread['userId']) {
                $context = $content."<div class=\"hideContent mtl mbl clearfix\"><span class=\"pull-right\" style='font-size:10px;'>".'回复可见区域'.'</span>'.$replyHideContent.'</div>';

                return $context;
            }

            if (!$user['id']) {
                $context .= $content.'<div class="hideContent mtl mbl"><h4>'.'游客,如果您要查看本话题隐藏内容请先'.'<a href="/login">'.'登录'.'</a>或<a href="/register">'.'注册'.'</a>！</h4></div>';

                return $context;
            }

            $count = $this->getThreadService()->countThreads(array('userId' => $user['id'], 'threadId' => $thread['id']));

            if ($count > 0) {
                $context .= $content.$replyHideContent;
            } else {
                $context .= $content.'<div class="hideContent mtl mbl"><h4> <a href="#post-thread-form">'.'回复'.'</a>'.'本话题可见'.'</h4></div>';
            }
        }

        return $context;
    }

    public function uploadAction(Request $request)
    {
        $group = $request->query->get('group');
        $file = $this->get('request')->files->get('file');

        if (!is_object($file)) {
            $this->createNewException(FileException::FILE_EMPTY_ERROR());
        }

        if (filesize($file) > 1024 * 1024 * 2) {
            $this->createNewException(FileException::FILE_SIZE_LIMIT());
        }

        if (FileToolkit::validateFileExtension($file, 'png jpg gif doc xls txt rar zip')) {
            $this->createNewException(FileToolkitException::FILE_TYPE_ERROR());
        }

        $record = $this->getFileService()->uploadFile($group, $file);

        unset($record['uri']);
        $record['name'] = $file->getClientOriginalName();

        return new Response(json_encode($record));
    }

    protected function isFeatureEnabled($feature)
    {
        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return in_array($feature, $features);
    }

    protected function getPostSearchFilters($request)
    {
        $filters = array();

        $filters['type'] = $request->query->get('type');

        if (!in_array($filters['type'], array('all', 'onlyOwner'))) {
            $filters['type'] = 'all';
        }

        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('asc', 'desc'))) {
            $filters['sort'] = 'asc';
        }

        return $filters;
    }

    protected function getPostCondition($filters, $ownId, $threadId)
    {
        if ($filters == 'all') {
            return array('threadId' => $threadId, 'status' => 'open', 'postId' => 0);
        }

        if ($filters == 'onlyOwner') {
            return array('threadId' => $threadId, 'status' => 'open', 'userId' => $ownId, 'postId' => 0);
        }

        return false;
    }

    protected function getPostOrderBy($sort)
    {
        if ($sort == 'asc') {
            return array('createdTime' => 'asc');
        }

        if ($sort == 'desc') {
            return array('createdTime' => 'desc');
        }
    }

    protected function getGroupMemberRole($groupId)
    {
        $user = $this->getCurrentUser();

        if (!$user['id']) {
            return 0;
        }

        if ($this->getGroupService()->isOwner($groupId, $user['id'])) {
            return 2;
        }

        if ($this->getGroupService()->isAdmin($groupId, $user['id'])) {
            return 3;
        }

        if ($this->getGroupService()->isMember($groupId, $user['id'])) {
            return 1;
        }

        return 0;
    }

    protected function checkManagePermission($id, $thread)
    {
        $user = $this->getCurrentUser();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') == true) {
            return true;
        }

        if ($this->getGroupService()->isOwner($id, $user['id'])) {
            return true;
        }

        if ($this->getGroupService()->isAdmin($id, $user['id'])) {
            return true;
        }

        if ($thread['userId'] == $user['id']) {
            return true;
        }

        return false;
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotifiactionService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->getBiz()->service('Content:FileService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Group:ThreadService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->getBiz()->service('Group:GroupService');
    }
}
