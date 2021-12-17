<?php

namespace Biz\Group\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\Group\Dao\GroupDao;
use Biz\Group\Dao\MemberDao;
use Biz\Group\GroupException;
use Biz\Group\Service\GroupService;
use Biz\Group\Service\ThreadService;
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
            $this->createNewException(GroupException::TITLE_REQUIRED());
        }
        $title = trim($group['title']);

        if (isset($group['about'])) {
            $group['about'] = $this->purifyHtml($group['about']);
        }
        $group['ownerId'] = $user['id'];
        $group['memberNum'] = 1;
        $group['createdTime'] = time();
        $group = $this->getGroupDao()->create($group);
        $member = [
            'groupId' => $group['id'],
            'userId' => $user['id'],
            'createdTime' => time(),
            'role' => 'owner',
        ];
        $this->getGroupMemberDao()->create($member);

        return $group;
    }

    public function addOwner($groupId, $userId)
    {
        $member = [
            'groupId' => $groupId,
            'userId' => $userId,
            'createdTime' => time(),
            'role' => 'owner',
        ];

        $member = $this->getGroupMemberDao()->create($member);

        $this->reCountGroupMember($groupId);

        return $member;
    }

    public function openGroup($id)
    {
        $group = $this->updateGroup($id, [
            'status' => 'open',
        ]);

        $this->dispatchEvent('group.open', $group);

        return $group;
    }

    public function deleteGroup($id)
    {
        $group = $this->getGroup($id);
        if ('close' != $group['status']) {
            $this->createNewException(GroupException::DELETE_GROUP_REQUIRE_CLOSE());
        }
        $this->beginTransaction();
        try {
            $this->getGroupThreadService()->deleteThreadsByGroupId($id);
            $this->getGroupMemberDao()->deleteByGroupId($id);
            $this->getGroupDao()->delete($id);
            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
        }
    }

    public function recommendGroup($id, $number)
    {
        if (!is_numeric($number)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $this->updateGroup($id, [
            'recommended' => 1,
            'recommendedSeq' => (int) $number,
            'recommendedTime' => time(),
        ]);
    }

    public function cancelRecommendGroup($id)
    {
        return $this->updateGroup($id, [
            'recommended' => 0,
            'recommendedSeq' => 0,
            'recommendedTime' => 0,
        ]);
    }

    public function closeGroup($id)
    {
        $group = $this->updateGroup($id, [
            'status' => 'close',
        ]);

        $this->dispatchEvent('group.close', $group);

        return $group;
    }

    public function changeGroupImg($id, $field, $data)
    {
        if (!in_array($field, ['logo', 'backgroundLogo'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $group = $this->getGroup($id);
        if (empty($group)) {
            $this->createNewException(GroupException::NOTFOUND_GROUP());
        }

        $fileIds = ArrayToolkit::column($data, 'id');

        $files = $this->getFileService()->getFilesByIds($fileIds);
        $files = ArrayToolkit::index($files, 'id');

        $fileIds = ArrayToolkit::index($data, 'type');

        $fields = [
            $field => $files[$fileIds[$field]['id']]['uri'],
        ];

        $oldAvatars = [
            $field => $group[$field] ? $group[$field] : null,
        ];
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
            $this->createNewException(GroupException::NOTFOUND_GROUP());
        }

        if ($this->isMember($groupId, $user['id'])) {
            $this->createNewException(GroupException::DUPLICATE_JOIN());
        }

        $member = [
            'groupId' => $groupId,
            'userId' => $user['id'],
            'createdTime' => time(),
        ];
        $member = $this->getGroupMemberDao()->create($member);

        $this->reCountGroupMember($groupId);

        $this->dispatchEvent('group.join', new Event($group));

        return $member;
    }

    public function exitGroup($user, $groupId)
    {
        $group = $this->getGroup($groupId);
        if (empty($group)) {
            $this->createNewException(GroupException::NOTFOUND_GROUP());
        }

        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $user['id']);

        if (empty($member)) {
            $this->createNewException(GroupException::NOTFOUND_MEMBER());
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

        return [];
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

        return $member && 'admin' === $member['role'] ? true : false;
    }

    public function isMember($groupId, $userId)
    {
        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $userId);

        return $member ? true : false;
    }

    public function getMembersCountByGroupId($id)
    {
        return $this->getGroupMemberDao()->count(['groupId' => $id]);
    }

    public function getMemberByGroupIdAndUserId($groupid, $userId)
    {
        return $this->getGroupMemberDao()->getByGroupIdAndUserId($groupid, $userId);
    }

    protected function reCountGroupMember($groupId)
    {
        $groupMemberNum = $this->getGroupMemberDao()->count(['groupId' => $groupId]);
        $this->getGroupDao()->update($groupId, ['memberNum' => $groupMemberNum]);
    }

    public function validateWaveField($waveData, $field, $diff)
    {
        if (isset($waveData[$field])) {
            return ($diff + $waveData[$field]) > 0 ? $diff : -$waveData[$field];
        }
    }

    public function waveGroup($id, $field, $diff)
    {
        $group = $this->getGroupDao()->get($id);

        $diff = $this->validateWaveField($group, $field, $diff);

        return $this->getGroupDao()->wave([$id], [$field => $diff]);
    }

    public function waveMember($groupId, $userId, $field, $diff)
    {
        $member = $this->getGroupMemberDao()->getByGroupIdAndUserId($groupId, $userId);

        if ($member) {
            $diff = $this->validateWaveField($member, $field, $diff);

            return $this->getGroupMemberDao()->wave([$member['id']], [$field => $diff]);
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
        if (isset($conditions['ownerName']) && '' !== $conditions['ownerName']) {
            $owner = $this->getUserService()->getUserByNickname($conditions['ownerName']);

            if (!empty($owner)) {
                $conditions['ownerId'] = $owner['id'];
            } else {
                $conditions['ownerId'] = 0;
            }
        }
        if (isset($conditions['status'])) {
            if ('' == $conditions['status']) {
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
     * @return ThreadService
     */
    protected function getGroupThreadService()
    {
        return $this->createService('Group:ThreadService');
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
