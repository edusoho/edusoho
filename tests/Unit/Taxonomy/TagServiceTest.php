<?php

namespace  Tests\Taxonomy;

use Biz\Taxonomy\Service\TagService;
use Biz\BaseTestCase;

class TagServiceTest extends BaseTestCase
{
    /**
     * @group add
     */
    public function testAddTag()
    {
        $tag = array();
        $tag['name'] = '测试标签';
        $tag = $this->getTagService()->addTag($tag);
        $this->assertNotEmpty($tag);
        $this->assertEquals('测试标签', $tag['name']);
        $this->assertEquals('1', $tag['id']);
        $this->assertGreaterThan(0, $tag['createdTime']);
    }

    public function testAddTagGroup()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);

        $tagGroup = array(
            'name' => '测试标签组',
            'tagIds' => array(1, 2),
            'tagNum' => 2,
        );

        $tagGroup = $this->getTagService()->addTagGroup($tagGroup);
        $this->assertEquals(2, $tagGroup['tagNum']);
    }

    /**
     * @group add
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddTagWithEmptyTagName()
    {
        $tag = array();
        $tag['name'] = null;
        $this->getTagService()->addTag($tag);
        $tag['name'] = '';
        $this->getTagService()->addTag($tag);
        $tag['name'] = 0;
        $this->getTagService()->addTag($tag);
    }

    /**
     * @group add
     */
    public function testAddTagWithTooLongTagName()
    {
        $tag = array();
        $tag['name'] = '过长的标签名称过长的标签名称过长的标签名称过长的标签名称';
        $this->getTagService()->addTag($tag);
    }

    /**
     * @group add
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddMultiTagNameTag()
    {
        $this->getTagService()->addTag(array('name' => '测试标签'));
        $this->getTagService()->addTag(array('name' => '测试标签'));
    }

    /**
     * @group get
     */
    public function testGetTag()
    {
        $tag = $this->getTagService()->addTag(array('name' => '测试标签'));
        $foundTag = $this->getTagService()->getTag($tag['id']);

        $this->assertEquals('测试标签', $foundTag['name']);
        $this->assertGreaterThan(0, $foundTag['createdTime']);
    }

    public function testGetTagGroup()
    {
        $tagGroup = $this->getTagService()->addTagGroup(array(
            'name' => '测试标签组',
            'tagNum' => 0,
        ));

        $foundTagGroup = $this->getTagService()->getTagGroup($tagGroup['id']);
        $this->assertEquals('测试标签组', $foundTagGroup['name']);
    }

    public function testGetTagWithNotExistTagId()
    {
        $tag = $this->getTagService()->addTag(array('name' => '测试标签'));
        $foundTag = $this->getTagService()->getTag(999);
        $this->assertNull($foundTag);
    }

    public function testGetTagByName()
    {
        $tag = $this->getTagService()->addTag(array('name' => '测试标签'));
        $tagByName = $this->getTagService()->getTagByName('测试标签');

        $this->assertNotEmpty($tagByName);
        $this->assertEquals('测试标签', $tagByName['name']);
        $this->assertGreaterThan(0, $tagByName['createdTime']);
    }

    public function testGetTagByNameWithNotExistTagName()
    {
        $tag = $this->getTagService()->addTag(array('name' => '测试标签'));
        $foundTag = $this->getTagService()->getTagByName('xxx');
        $this->assertFalse($foundTag);
    }

    public function testGetTagOwnerRelationByTagIdAndOwner()
    {
        $fields = array(
            'tagId' => 1,
            'ownerType' => 'course',
            'ownerId' => 1,
        );

        $this->getTagService()->addTagOwnerRelation($fields);

        $tagOwner = $this->getTagService()->getTagOwnerRelationByTagIdAndOwner(1, array('ownerType' => 'course', 'ownerId' => 1));
        $this->assertEquals(1, count($tagOwner));
    }

    public function testfindAllTagsAndGetTagsCount()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $this->getTagService()->addTag($tagA);
        $this->assertEquals(1, $this->getTagService()->getAllTagCount());

        $this->getTagService()->addTag($tagB);
        $this->assertEquals(2, $this->getTagService()->getAllTagCount());
        $tags = $this->getTagService()->findAllTags(0, 1);
        $this->assertEquals(1, count($tags));
        $tags = $this->getTagService()->findAllTags(0, 2);
        $this->assertEquals(2, count($tags));
    }

    /**
     * @group get
     */
    public function testfindAllTagsAndGetTagsCountWithEmptyTags()
    {
        $this->assertEquals(0, $this->getTagService()->getAllTagCount());
        $tags = $this->getTagService()->findAllTags(0, 1);
        $this->assertEquals(0, count($tags));
        $tags = $this->getTagService()->findAllTags(0, 2);
        $this->assertEquals(0, count($tags));
    }

    /**
     * @group get
     */
    public function testfindTagsByIds()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);
        $ids = array($tagA['id'], $tagB['id']);
        $tags = $this->getTagService()->findTagsByIds($ids);
        $this->assertEquals(2, count($tags));
    }

    /**
     * @group get
     */
    public function testfindTagsByIdsWithNotExistId()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);
        $tags = $this->getTagService()->findTagsByIds(array($tagA['id'], $tagB['id'], 99, 12));
        $this->assertEquals(2, count($tags));

        $tags = $this->getTagService()->findTagsByIds(array(99, 12));
        $this->assertEquals(0, count($tags));
    }

    /**
     * @group get
     */
    public function testfindTagsByNames()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);
        $tags = $this->getTagService()->findTagsByNames(array('测试标签1', '测试标签2'));
        $this->assertEquals(2, count($tags));
    }

    public function testFindTagsByGroupId()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);

        $tagGroup = array(
            'name' => '测试标签组',
            'tagIds' => array(1, 2),
            'tagNum' => 2,
        );

        $tagGroup = $this->getTagService()->addTagGroup($tagGroup);
        $count = count($this->getTagService()->findTagsByGroupId($tagGroup['id']));
        $this->assertEquals(2, $count);
    }

    /**
     * @group get
     */
    public function testfindTagsByNamesWithNotExistId()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);
        $tags = $this->getTagService()->findTagsByNames(array('xxx'));
        $this->assertEquals(0, count($tags));

        $tags = $this->getTagService()->findTagsByNames(array('xxx', '测试标签1', '测试标签2'));
        $this->assertEquals(2, count($tags));
    }

    /**
     * @group current
     */
    public function testUpdateTag()
    {
        $tag = array();
        $tag['name'] = '修改前的分类名称';
        $tag = $this->getTagService()->addTag($tag);
        $updateTag = array('name' => '修改后的分类名称');
        $updatedTag = $this->getTagService()->updateTag($tag['id'], $updateTag);
        $this->assertEquals('修改后的分类名称', $updatedTag['name']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUpdateTagWithNotExistId()
    {
        $tag = array();
        $tag['name'] = '修改前的分类名称';
        $tag = $this->getTagService()->addTag($tag);
        $updateTag = array('name' => '修改后的分类名称');
        $updatedTag = $this->getTagService()->updateTag(999, $updateTag);
        $this->assertFalse($updatedTag);
    }

    /**
     * @group current
     */
    public function testUpdateTagWithTooLongName()
    {
        $tag = array();
        $tag['name'] = '修改前的分类名称';
        $tag = $this->getTagService()->addTag($tag);
        $updateTag = array('name' => '修改后的分类名称修改后的分类名称修改后的分类名称修改后的分类名称修改后的分类名称修改后的分类名称修改后的分类名称');
        $this->getTagService()->updateTag($tag['id'], $updateTag);
    }

    /**
     * @group update
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUpdateTagWithEmptyName()
    {
        $tag = array();
        $tag['name'] = '修改前的分类名称';
        $tag = $this->getTagService()->addTag($tag);
        $updateTag = array('name' => '');
        $this->getTagService()->updateTag($tag['id'], $updateTag);
    }

    public function testUpdateTagGroup()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);

        $tagGroup = array(
            'name' => '测试标签组',
            'tagIds' => array(1, 2),
            'tagNum' => 2,
        );

        $tagGroup = $this->getTagService()->addTagGroup($tagGroup);
        $updateTagGroup = $this->getTagService()->updateTagGroup($tagGroup['id'], array('name' => '标签组测试'));

        $this->assertEquals('标签组测试', $updateTagGroup['name']);
    }

    /**
     * @group current
     */
    public function testDeleteTag()
    {
        $tag = array('name' => '测试标签');
        $tag = $this->getTagService()->addTag($tag);
        $this->assertNull($this->getTagService()->deleteTag($tag['id']));
        $this->assertNull($this->getTagService()->deleteTag($tag['id']));
    }

    /**
     * @group delete
     */
    public function testDeleteTagWithNotExistId()
    {
        $this->assertEquals(0, $this->getTagService()->deleteTag(999));
    }

    public function testDeleteTagGroup()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);

        $tagGroup = array(
            'name' => '测试标签组',
            'tagIds' => array(1, 2),
            'tagNum' => 2,
        );

        $tagGroup = $this->getTagService()->addTagGroup($tagGroup);

        $this->getTagService()->deleteTagGroup($tagGroup['id']);
        $this->assertNull($this->getTagService()->deleteTag($tagGroup['id']));
    }

    public function testFindTagGroups()
    {
        $tagGroup = array(
            'name' => '测试标签组',
        );

        $this->getTagService()->addTagGroup($tagGroup);
        $this->assertEquals(1, count($this->getTagService()->findTagGroups()));
    }

    public function testFindTagRelationsByTagIds()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);

        $tagGroup = array(
            'name' => '测试标签组',
            'tagIds' => array(1, 2),
            'tagNum' => 2,
        );

        $tagGroup = $this->getTagService()->addTagGroup($tagGroup);
        $this->assertEquals(2, count($this->getTagService()->findTagRelationsByTagIds(array(1, 2))));
    }

    public function testSearchTags()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);

        $this->assertEquals(2, count($this->getTagService()->searchTags(array(), array(), 0, 2)));
    }

    public function testSearchTagCount()
    {
        $tagA = array('name' => '测试标签1');
        $tagB = array('name' => '测试标签2');
        $tagA = $this->getTagService()->addTag($tagA);
        $tagB = $this->getTagService()->addTag($tagB);
        $this->assertEquals(2, $this->getTagService()->searchTagCount(array()));
    }

    public function testIsTagGroupNameAvailable()
    {
        $tagGroup = array(
            'name' => '测试标签组',
        );

        $this->getTagService()->addTagGroup($tagGroup);
        $this->assertEquals(false, $this->getTagService()->isTagGroupNameAvailable('测试标签组', '测试标签组2'));
    }

    public function testFindGroupTagIdsByOwnerTypeAndOwnerIds()
    {
        $ownerId = 1;
        $fields = array(
            'tagId' => 1,
            'ownerType' => 'course',
            'ownerId' => $ownerId,
        );

        $this->getTagService()->addTagOwnerRelation($fields);
        $fields['tagId'] = 2;
        $this->getTagService()->addTagOwnerRelation($fields);
        $tagIds = $this->getTagService()->findGroupTagIdsByOwnerTypeAndOwnerIds('course', array($ownerId));
        $this->assertEquals(1, $tagIds['1'][0]);
        $this->assertEquals(2, $tagIds['1'][1]);
    }

    public function testFindTagIdsByOwnerTypeAndOwnerIds()
    {
        $ownerId = 1;
        $fields = array(
            'tagId' => 1,
            'ownerType' => 'course',
            'ownerId' => $ownerId,
        );

        $this->getTagService()->addTagOwnerRelation($fields);
        $fields['tagId'] = 2;
        $this->getTagService()->addTagOwnerRelation($fields);
        $tagIds = $this->getTagService()->findTagIdsByOwnerTypeAndOwnerIds('course', array($ownerId));
        $this->assertEquals(2, count($tagIds));
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }
}
