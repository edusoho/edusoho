<?php

namespace Biz\Group\Service\Impl;

use Biz\BaseService;
use Biz\Content\Service\FileService;
use Biz\Group\Dao\GroupDao;
use Biz\Group\Dao\MemberDao;
use AppBundle\Common\ArrayToolkit;
use Biz\Group\Service\GroupService;
use Codeages\Biz\Framework\Event\Event;

class GroupServiceImpl extends BaseService implements GroupService
{
    public function countMembers($conditions)
    {
        return $this->getGroupMemberDao()->count($conditions);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        return $this->getGroupMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function getGroupsByIds($ids)
    {
        $groups = $this->getGroupDao()->findByIds($ids);

        return ArrayToolkit::index($groups, 'id');
    }

    public function getGroup($id)
    {
        return $this->getGroupDao()->get($id);
    }

    public function searchGroups($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareGroupConditions($conditions);

        return $this->getGroupDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function updateGroup($id, $fields)
    {
        if (isset($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about']);
        }

        return $this->getGroupDao()->update($id, $fields);
    }

    public function updateMember($id, $fields)
    {
        return $this->getGroupMemberDao()->update($id, $fields);
    }

    public function addGroup($user, $group)
    {
        if (!isset($group['title']) || empty($group['title'])) {
            throw $this->createInvalidArgumentException('Title Required');
        }
        $title = trim($group['title']);

        if (isset($group['about'])) {
            $group['about'] = $this->purifyHtml($group['about']);
        }
        $group['ownerId'] = $user['id'];
        $group['memberNum'] = 1;
        $group['createdTime'] = time();
        $group = $this->getGroupDao()->create($group);
        $member = array(
            'groupId' => $group['id'],
            'userId' => $user['id'],
            'createdTime' => time(),
            'role' => 'owner',
        );
        $this->getGroupMemberDao()->create($member);

        return $group;
    }

    public function addOwner($groupId, $userId)
    {
        $member = array(
            'groupId' => $groupId,
            'userId' => $userId,
            'createdTime' => time(),
            'role' => 'owner',
        );

        $member = $this->getGroupMemberDao()->create($member);

        $this->reCountGroupMember($groupId);

        return $member;
    }

    public function openGroup($id)
    {
        return $this->updateGroup($id, array(
            'status' => 'open',
        ));
    }

    public function closeGroup($id)
    {
        return $this->updateGroup($id, array(
            'status' => 'close',
        ));
    }

    public function changeGroupImg($id, $field, $data)
    {
        if (!in_array($field, array('logo', 'backgroundLogo'))) {
            throw $this->createInvalidArgumentException('Invalid Field :'.$field);
        }

        $group = $this->getGroup($id);
        if (empty($group)) {
            throw $this->createNotFoundException('Group Not Found');
        }

        $fileIds = ArrayToolkit::column($data, 'id');

        $files = $this->getFileService()->getFilesByIds($fileIds);
        $files = ArrayToolkit::index($files, 'id');

        $fileIds = ArrayToolkit::index($data, 'type');

        $fields = array(
            $field => $files[$fileIds[$field]['id']]['uri'],
        );

        $oldAvatars = array(
            $field => $group[$field] ? $group[$field] : null,
        );
        $fileService = $this->getFileService();
        array_map(function ($oldAvatar) use ($fileService) {
            if (!empty($oldAvatar)) {
                $fileService->deleteFileByUri($oldAvatar);
            }
        }, $oldAvatars);

        return $this->getGroupDao()->update($id, $fields);
    }

    public function joinGroup($user, $groupId)
    {
        $group = $this->getGroup($groupId);
        if (empty($group)) {
            throw $this->createNotFoundException('Group Not Found');
        }

        if ($this->isMember($groupId, $user['id'])) {
            throw $this->createAccessDeniedException('You\'re in group');
        }

        $member = array(
            'groupId' => $groupId,
            'userId' => $user['id'],
            'createdTime' => time(),
        );
        $member = $this->getGroupMemberDao()->create($member);

        $this->reCountGroupMember($groupId);

        $this->dispatchEvent('group.join', new Event($group));

        return $member;
    }

    public function exitGroup($user, $groupId)
    {
        $group = $this->getGroup($groupId);
        if (empty($group)) {
            throw $this->createNotFoundException('Group Not Found');
        }

        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $user['id']);

        if (empty($member)) {
            throw $this->createNotFoundException("Member#{$user['id']} Not in Group");
        }

        $this->getGroupMemberDao()->delete($member['id']);

        $this->reCountGroupMember($groupId);
    }

    public function findGroupsByUserId($userId)
    {
        $members = $this->getGroupMemberDao()->findByUserId($userId);
        if ($members) {
            foreach ($members as $key) {
                $ids[] = $key['groupId'];
            }

            return $this->getGroupDao()->findByIds($ids);
        }

        return array();
    }

    public function findGroupByTitle($title)
    {
        return $this->getGroupDao()->findByTitle($title);
    }

    public function searchGroupsCount($conditions)
    {
        $conditions = $this->prepareGroupConditions($conditions);

        return $this->getGroupDao()->count($conditions);
    }

    public function isOwner($id, $userId)
    {
        $group = $this->getGroupDao()->get($id);

        return $group['ownerId'] == $userId ? true : false;
    }

    public function isAdmin($groupId, $userId)
    {
        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $userId);

        return $member['role'] == 'admin' ? true : false;
    }

    public function isMember($groupId, $userId)
    {
        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $userId);

        return $member ? true : false;
    }

    public function getMembersCountByGroupId($id)
    {
        return $this->getGroupMemberDao()->count(array('groupId' => $id));
    }

    public function getMemberByGroupIdAndUserId($groupid, $userId)
    {
        return $this->getGroupMemberDao()->getByGroupIdAndUserId($groupid, $userId);
    }

    protected function reCountGroupMember($groupId)
    {
        $groupMemberNum = $this->getGroupMemberDao()->count(array('groupId' => $groupId));
        $this->getGroupDao()->update($groupId, array('memberNum' => $groupMemberNum));
    }

    public function waveGroup($id, $field, $diff)
    {
        return $this->getGroupDao()->wave(array($id), array($field => $diff));
    }

    public function waveMember($groupId, $userId, $field, $diff)
    {
        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $userId);

        if ($member) {
            return $this->getGroupMemberDao()->wave(array($member['id']), array($field => $diff));
        }
    }

    public function deleteMemberByGroupIdAndUserId($groupId, $userId)
    {
        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $userId);

        $this->getGroupMemberDao()->delete($member['id']);

        $this->reCountGroupMember($groupId);
    }

    protected function prepareGroupConditions($conditions)
    {
        if (isset($conditions['ownerName']) && $conditions['ownerName'] !== '') {
            $owner = $this->getUserService()->getUserByNickname($conditions['ownerName']);

            if (!empty($owner)) {
                $conditions['ownerId'] = $owner['id'];
            } else {
                $conditions['ownerId'] = 0;
            }
        }
        if (isset($conditions['status'])) {
            if ($conditions['status'] == '') {
                unset($conditions['status']);
            }
        }

        return $conditions;
    }

    /**
     * @return MemberDao
     */
    protected function getGroupMemberDao()
    {
        return $this->createDao('Group:MemberDao');
    }

    /**
     * @return GroupDao
     */
    protected function getGroupDao()
    {
        return $this->createDao('Group:GroupDao');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
