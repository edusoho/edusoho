<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\GroupController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class Group1Controller extends GroupController
{
    public function index1Action(Request $request) 
    {   
        $mycreatedGroup = array();
        $myJoinGroup = array();

        $activeGroup = $this->getGroupService()->searchGroups(array('status'=>'open',),  array('memberNum', 'DESC'),0, 12);
        
        $type=$request->query->get('type');
        $sort=$this->filterSort('byCreatedTimeOnly');
        if($type && $type == "post"){

            $sort=$this->filterSort('byLastPostTimeOnly');

        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount(array(
                'createdTime'=>time()-30*24*60*60,
                'status'=>'open'
                )),
            20 
        );

        $recentlyThread = $this->getThreadService()->searchThreads(
            array(
                'createdTime'=>time()-30*24*60*60,
                'status'=>'open'
                ),
            $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $ownerIds = ArrayToolkit::column($recentlyThread, 'userId');
        $groupIds = ArrayToolkit::column($recentlyThread, 'groupId');
        $userIds =  ArrayToolkit::column($recentlyThread, 'lastPostMemberId');

        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);

        $owners=$this->getUserService()->findUsersByIds($ownerIds);

        $groups=$this->getGroupService()->getGroupsByids($groupIds);

        $user = $this->getCurrentUser();

        if ($user['id']) {

            $membersCount=$this->getGroupService()->searchMembersCount(array('userId'=>$user['id']));

            $start=$membersCount>12 ? rand(0,$membersCount-12) : 0 ;

            $members=$this->getGroupService()->searchMembers(array('userId'=>$user['id']),array('createdTime',"DESC"),$start,
            12);

            $groupIds = ArrayToolkit::column($members, 'groupId');

            $myJoinGroup=$this->getGroupService()->getGroupsByids($groupIds);

        }

        $vip =  $user->isLogin() ? $this->getVipService()->getMemberByUserId($user['id']) : null;
   
        $userLevel=array();
        $status="false";
        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level && $this->getVipService()->checkUserInMemberLevel($user->id,$vip['levelId'])=="ok"){
                
                $userLevel=$level;

                $status=$this->getVipService()->checkUserInMemberLevel($user->id,$vip['levelId']);

            }
        }

        $newGroups=$this->getGroupService()->searchGroups(array('status'=>'open',),
            array('createdTime','DESC'),0,8);

        $sign=$this->getSignService()->getSignByUserId($user['id']);

        $groupId=$sign ? $sign['targetId'] : 1;
  
        return $this->render("CustomWebBundle:Group:index.html.twig", array(
            'activeGroup' => $activeGroup,
            'myJoinGroup' => $myJoinGroup,
            'lastPostMembers'=>$lastPostMembers,
            'owners'=>$owners,
            'newGroups'=>$newGroups,
            'groupinfo'=>$groups,
            'user'=>$user,
            'type'=>$type,
            'groupId'=>$groupId,
            'paginator'=>$paginator,
            'userLevel'=>$userLevel,  
            'recentlyThread'=>$recentlyThread,
        ));
    }

    public function groupIndexAction(Request $request,$id) 
    {
        $group = $this->getGroupService()->getGroup($id);

        $groupOwner=$this->getUserService()->getUser($group['ownerId']);

        if($group['status']=="close"){
            return $this->createMessageResponse('info','该小组已被关闭');
        }

        $recentlyJoinMember=$this->getGroupService()->searchMembers(array('groupId'=>$id),
            array('createdTime','DESC'),0,12);

        $memberIds = ArrayToolkit::column($recentlyJoinMember, 'userId');

        $user=$this->getCurrentUser();

        $userIsGroupMember=$this->getGroupService()->getMemberByGroupIdAndUserId($id,$user['id']);
        $recentlyMembers=$this->getUserService()->findUsersByIds($memberIds);

        $filters = $this->getThreadSearchFilters($request);

        $conditions = $this->convertFiltersToConditions($id, $filters);  
    
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount($conditions),
            $conditions['num']  
        );
            
        $threads=$this->getThreadService()->searchThreads(
            $conditions,
            $this->filterSort($filters['sort']),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $ownerIds = ArrayToolkit::column($threads, 'userId');

        $userIds =  ArrayToolkit::column($threads, 'lastPostMemberId');

        $owners=$this->getUserService()->findUsersByIds($ownerIds);

        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);

        $activeMembers=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'member'),
            array('postNum','DESC'),0,12);

        $memberIds = ArrayToolkit::column($activeMembers, 'userId');

        $members=$this->getUserService()->findUsersByIds($memberIds);

        $isSignedToday = $this->getSignService()->isSignedToday($user->id, 'group_sign', $group['id']);

        $week=array('日','一','二','三','四','五','六');

        $userSignStatistics = $this->getSignService()->getSignUserStatistics($user->id, 'group_sign', $group['id']);

        $day=date('d',time());
  
        $signDay=$this->getSignService()->getSignRecordsByPeriod($user->id, 'group_sign', $group['id'], date('Y-m',time()), date('Y-m-d',time()+3600));
        $notSign=$day-count($signDay);
        
        $vip =  $user->isLogin() ? $this->getVipService()->getMemberByUserId($user['id']) : null;
        $userLevel=array();
        $status="false";
        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level && $this->getVipService()->checkUserInMemberLevel($user->id,$vip['levelId'])=="ok"){
                
                $userLevel=$level;

                $status=$this->getVipService()->checkUserInMemberLevel($user->id,$vip['levelId']);

            }
        }
        
        $vipLevels=$this->getLevelService()->searchLevels(array(),0,1000);  
        
        $canUseCard=$this->getSignService()->getSignCardByUserId($user->id);

        return $this->render("CustomWebBundle:Group:groupindex.html.twig", array(
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'recentlyJoinMember'=>$recentlyJoinMember,
            'owner'=>$owners,
            'user'=>$user,
            'groupOwner'=>$groupOwner,
            'id'=>$id,
            'threads'=>$threads,
            'paginator'=>$paginator,
            'condition'=>$filters,
            'lastPostMembers'=>$lastPostMembers,
            'userIsGroupMember'=>$userIsGroupMember,
            'members'=>$recentlyMembers,
            'userSignStatistics'=>$userSignStatistics,
            'now'=>time(),
            'notSign'=>$notSign,
            'signDay'=>count($signDay),
            'week'=>$week[date('w',time())],
            'isSignedToday'=>$isSignedToday,
            'status'=>$status,
            'userLevel'=>$userLevel,
            'vipLevels'=>$vipLevels,
            'canUseCard'=>$canUseCard,
                   
        ));
    }

    private function getSignService()
    {
        return $this->getServiceKernel()->createService('Custom:Sign.SignService');
    }

    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all','elite','reward'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('byCreatedTime', 'byLastPostTime', 'byPostNum'))) {
            $filters['sort'] = 'byCreatedTime';
        }
        $filters['num'] = $request->query->get('num');

        if (!in_array($filters['num'], array(25))) {
            $filters['num'] = 25;
        }
        return $filters;
    }

    private function convertFiltersToConditions($id, $filters)
    {
        $conditions = array('groupId' => $id,'num'=>10,'status'=>'open');
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

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }
    
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getGroupService() 
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    public function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    public function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }   

    private function getGroupMemberRole($userId)
    {
        $user = $this->getCurrentUser();

        if (!$user['id']) return 0;

        if ($this->getGroupService()->isOwner($userId, $user['id'])) return 2;

        if ($this->getGroupService()->isAdmin($userId, $user['id'])) return 3;

        if ($this->getGroupService()->isMember($userId, $user['id'])) return 1;

        return 0;
    }
    private function filterSort($sort)
    {
        switch ($sort) {
            case 'byPostNum':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byStick':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byCreatedTime':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byLastPostTime':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('lastPostTime','DESC'),
                );
                break;
            case 'byCreatedTimeOnly':
                $orderBys=array(
                    array('createdTime','DESC'),
                );
                break;
            case 'byLastPostTimeOnly':
                $orderBys=array(
                    array('lastPostTime','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }
        return $orderBys;
    }
}