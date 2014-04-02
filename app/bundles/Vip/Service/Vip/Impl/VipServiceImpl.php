<?php
namespace Vip\Service\Vip\Impl;

use Vip\Service\Vip\VipService;
use Topxia\Service\Common\BaseService;

class VipServiceImpl extends BaseService implements VipService
{
    
    public function getMemberByUserId($userId)
    {
        return $this->getMemberDao()->getMemberByUserId($userId);
    }

    public function checkMemberName ($memberName)
    {
        $avaliable = $this->getUserService()->isNicknameAvaliable($memberName);
        if (!$avaliable) {
            if($this->isMemberNameAvaliable($memberName)) {
               return array('error_duplicate','该用户已经是会员！');
            }
            return array('success','');
        }
        return array('error_duplicate','用户名不存在，请检查！');
    }
    
    public function searchMembers(array $conditions, array $orderBy, $start, $limit)
    {
        return $this->getMemberDao()->searchMembers($conditions, $orderBy, $start, $limit);
    }

    public function searchMembersCount($conditions)
    {
        return $this->getMemberDao()->searchMembersCount($conditions);
    }

    public function becomeMember($userId, $levelId, $duration, $unit, $orderId = 0)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，开通会员失败。');
        }

        $level = $this->getLevelService()->getLevel($levelId);
        if (empty($level) or empty($level['enabled'])) {
            throw $this->createServiceException('会员等级不存在，开通会员失败。');
        }

        if (!in_array($unit, array('month', 'year'))) {
            throw $this->createServiceException('会员付费方式不正确，开通会员失败。');
        }

        $orderId = intval($orderId);
        if ($orderId > 0) {
            $order = $this->getOrderService()->getOrder($orderId);
            if (empty($order)) {
                throw $this->createServiceException('开通会员的订单不存在，开通会员失败。');
            }
        } else {
            $order = array('id' => 0, 'amount' => 0);
        }

        $member = array();
        $member['userId'] = $userId;
        $member['levelId'] = $levelId;
        $member['deadline'] = strtotime("+ {$duration} {$unit}s");
        $member['boughtType'] = 'new';
        $member['boughtTime'] = time();
        $member['boughtDuration'] = $duration;
        $member['boughtUnit'] = $unit;
        $member['boughtAmount'] = $order['amount'];
        $member['orderId'] = $order['id'];
        $member['createdTime'] = time();

        $currentUser = $this->getCurrentUser();
        if ($currentUser->id != $member['userId']) {
            $member['operatorId'] = $currentUser->id;
        } else {
            $member['operatorId'] = 0;
        }

        $member = $this->getMemberDao()->addMember($member);

        $history = $member;
        unset($history['id']);

        $this->getMemberHistoryDao()->addMemberHistory($history);

        return $member;
    }

    public function renewMember($userId, $duration, $unit, $orderId = 0)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，会员续费失败。');
        }

        $currentMember = $this->getMemberByUserId($user['id']);
        if (empty($currentMember)) {
            throw $this->createServiceException('用户不是会员，会员续费失败。');
        }

        $level = $this->getLevelService()->getLevel($currentMember['levelId']);
        if (empty($level) or empty($level['enabled'])) {
            throw $this->createServiceException('会员等级不存在或已关闭，会员续费失败。');
        }

        $duration = intval($duration);
        if (empty($duration)) {
            throw $this->createServiceException('会员开通时长不正确，开通会员失败。');
        }

        if (!in_array($unit, array('month', 'year'))) {
            throw $this->createServiceException('会员付费方式不正确，开通会员失败。');
        }

        $orderId = intval($orderId);
        if ($orderId > 0) {
            $order = $this->getOrderService()->getOrder($orderId);
            if (empty($order)) {
                throw $this->createServiceException('开通会员的订单不存在，开通会员失败。');
            }
        } else {
            $order = array('id' => 0, 'amount' => 0);
        }

        $member = array();
        $member['deadline'] = strtotime("+ {$duration} {$unit}s", max($currentMember['deadline'], time()));
        $member['boughtType'] = 'renew';
        $member['boughtTime'] = time();
        $member['boughtDuration'] = $duration;
        $member['boughtUnit'] = $unit;
        $member['boughtAmount'] = $order['amount'];
        $member['orderId'] = $order['id'];

        $currentUser = $this->getCurrentUser();
        if ($currentUser->id != $currentMember['userId']) {
            $member['operatorId'] = $currentUser->id;
        } else {
            $member['operatorId'] = 0;
        }

        $member = $this->getMemberDao()->updateMember($currentMember['id'], $member);

        $history = $member;
        unset($history['id']);

        $this->getMemberHistoryDao()->addMemberHistory($history);

        return $member;
    }

    public function upgradeMember($userId, $newLevelId, $orderId = 0)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，会员升级失败。');
        }

        $currentMember = $this->getMemberByUserId($user['id']);
        if (empty($currentMember)) {
            throw $this->createServiceException('用户不是会员，会员升级失败。');
        }

        $level = $this->getLevelService()->getLevel($newLevelId);
        if (empty($level) or empty($level['enabled'])) {
            throw $this->createServiceException('会员等级不存在或已关闭，会员升级失败。');
        }

        if (!$this->canUpgradeMember($user['id'])) {
            throw $this->createServiceException('会员剩余天数小于，系统设定的最小可升级天数，不能升级。请续费后再升级。');
        }

        $orderId = intval($orderId);
        if ($orderId > 0) {
            $order = $this->getOrderService()->getOrder($orderId);
            if (empty($order)) {
                throw $this->createServiceException('会员升级订单不存在，会员升级失败。');
            }
        } else {
            $order = array('id' => 0, 'amount' => 0);
        }



        $member = array();
        $member['levelId'] = $level['id'];
        $member['boughtType'] = 'upgrade';
        $member['boughtTime'] = time();
        $member['boughtAmount'] = $order['amount'];
        $member['orderId'] = $order['id'];

        $currentUser = $this->getCurrentUser();
        if ($currentUser->id != $currentMember['userId']) {
            $member['operatorId'] = $currentUser->id;
        } else {
            $member['operatorId'] = 0;
        }

        $member = $this->getMemberDao()->updateMember($currentMember['id'], $member);

        $history = $member;
        unset($history['id']);

        $this->getMemberHistoryDao()->addMemberHistory($history);

        return $member;
    }

    public function canUpgradeMember($userId)
    {
        $setting = $this->getSettingService()->get('vip');
        if (empty($setting['upgrade_min_day'])) {
            return false;
        }

        $vip = $this->getMemberByUserId($userId);
        if (empty($vip)) {
            return false;
        }

        return ($vip['deadline'] - time()) > ($setting['upgrade_min_day'] * 86400);
    }

    public function calUpgradeMemberAmount($userId, $newLevelId)
    {
        $member = $this->getMemberByUserId($userId);
        if (empty($member)) {
            throw $this->createServiceException("用户不是会员，无法计算升级金额");
        }

        $preLevel = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($preLevel)) {
            throw $this->createServiceException("原始会员等级不存在，无法计算升级金额");
        }

        $level = $this->getLevelService()->getLevel($newLevelId);
        if (empty($level)) {
            throw $this->createServiceException("会员等级不存在，无法计算升级金额");
        }

        if ($member['boughtUnit'] == 'month') {
            $months = ($member['deadline'] - time()) / 86400 / 30;
            $amount = ($level['monthPrice'] - $preLevel['monthPrice']) * $months;
        } else {
            $years = ($member['deadline'] - time()) / 86400 / 365;
            $amount = ($level['yearPrice'] - $preLevel['yearPrice']) * $years;
        }

        return intval($amount * 100) / 100;
    }

    public function updateMemberInfo($userId, array $fields)
    {
        $member = $this->getMemberDao()->getMemberByUserId($userId);
        if(empty($member)){
            throw $this->createNotFoundException('member not exists!');
        }

        $memberData['levelId'] = $fields['levelId'];
        $memberData['deadline'] = strtotime($fields['deadline']);
        $memberData['boughtType'] = 'edit';
        $memberData['boughtUnit'] = $fields['boughtUnit'];

        $member = $this->getMemberDao()->updateMember($member['id'], $memberData);

        $history = $member;
        unset($history['id']);
        $this->getMemberHistoryDao()->addMemberHistory($history);

        $this->getLogService()->info('vip', 'edit', "管理员编辑会员)", $member);
        
        return $member;
    }

    public function cancelMemberByUserId($userId)
    {
        $member = $this->getMemberDao()->getMemberByUserId($userId);

        $historyData['createdTime'] = $member['createdTime'];
        $historyData['boughtType'] = 'cancel';
        $historyData['userId'] = $member['userId'];
        $historyData['levelId'] = $member['levelId'];
        $historyData['deadline'] = date('Y-m-d',$member['deadline']);
        $memberHistory = $this->createMemberHistory($historyData);

        $condition['userId'] = $member['userId'];
        $this->getMemberDao()->deleteMemberByUserId($condition);

        $this->getLogService()->info('Member', 'delete', "管理员删除会员资料 {$memberHistory['nickname']} (#{$memberHistory['userId']})", $historyData);
    }

    public function searchMembersHistoriesCount($conditions)
    {
        $new_conditions = array();

        if(array_key_exists('nickname',$conditions)){

            if($conditions['nickname']){
                $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
                $new_conditions['userId'] = $user ? $user['id'] : -1;
            }
        }

        if(array_key_exists('boughtType', $conditions)){
            $new_conditions['boughtType'] = $conditions['boughtType'];
        }
        return $this->getMemberHistoryDao()->searchMembersHistoriesCount($new_conditions);
    }
    
    public function searchMembersHistories(array $conditions, array $orderBy, $start, $limit)
    {
        $new_conditions = array();

         if(array_key_exists('nickname',$conditions)){
            if($conditions['nickname']){
                $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
                $new_conditions['userId'] = $user ? $user['id'] : -1;
            }
        }
        
        if(array_key_exists('boughtType', $conditions)){
            $new_conditions['boughtType'] = $conditions['boughtType'];
        }

        return $this->getMemberHistoryDao()->searchMembersHistories($new_conditions, $orderBy, $start, $limit);
    }

    public function checkUserInMemberLevel($userId, $levelId)
    {
        $setting = $this->getSettingService()->get('vip');
        if (empty($setting['enabled'])) {
            return 'vip_closed';
        }
        if (empty($userId)) {
            return 'not_login';
        }

        $member = $this->getMemberByUserId($userId);
        if (empty($member)) {
            return 'not_member';
        }

        if ($member['deadline'] < time()) {
            return 'member_expired';
        }

        $memberLevel = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($memberLevel)) {
            return 'level_not_exist';
        }

        if (empty($levelId)) {
            return 'level_not_exist';
        }

        $level = $this->getLevelService()->getLevel($levelId);
        if (empty($level)) {
            return 'level_not_exist';
        }

        if ($memberLevel['seq'] < $level['seq']) {
            return 'level_low';
        }

        return 'ok';
    }

    private function createMemberHistory($memberHistoyDate)
    {
        if(empty($memberHistoyDate)){
            return NULL;
        }

        if(isset($memberHistoyDate['nickname'])){
            $user = $this->getUserService()->getUserByNickname($memberHistoyDate['nickname']);
        }elseif (isset($memberHistoyDate['userId'])){
            $user = $this->getUserService()->getUser($memberHistoyDate['userId']);
            $memberHistoyDate['nickname'] = $user['nickname'];
        }
        $historyData['userId'] = $user['id'];
        $historyData['deadline'] = strtotime($memberHistoyDate['deadline']);
        $historyData['boughtType'] = $memberHistoyDate['boughtType'];
        $historyData['boughtTime'] = $memberHistoyDate['createdTime'];
        $historyData['levelId'] = $memberHistoyDate['levelId'];

        return $this->getMemberHistoryDao()->addMemberHistory($historyData);
    }

    private function isMemberNameAvaliable($memberName)
    {
        $user = $this->getUserService()->getUserByNickname($memberName);
        $condition['userId'] = $user['id'];
        $member = $this->searchMembersCount($condition);
        return ($member == 1) ? true : false;
    }


    private function getMemberDao()
    {
        return $this->createDao('Vip:Vip.VipDao');
    }

    private function getMemberHistoryDao()
    {
        return $this->createDao('Vip:Vip.VipHistoryDao');
    }

    private function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    private function getLevelService()
    {
        return $this->createService('Vip:Vip.LevelService');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

}