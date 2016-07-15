<?php
namespace Topxia\Service\Content\Tests;

use Topxia\Service\Common\BaseTestCase;

class BlockServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $template = array('code' => 'test', 'mode' => 'template', 'category' => 'test', 'meta' => 'test', 'data' => '', 'templateName' => 'template', 'title' => '默认');
        $this->getBlockService()->createBlockTemplate($template);
    }

    public function testGetBlock()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $createdBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $getBlock      = $this->getBlockService()->getBlock($createdBlock1['id']);
        $this->assertNotNull($getBlock);
    }

    /**
     * @group get
     */
    public function testGetBlockWithNotExistId()
    {
        $getBlock = $this->getBlockService()->getBlock(999);

        $this->assertEquals(0, $getBlock['blockId']);
    }

    /**
     * @group get
     */
    public function testGetBlockByCode()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $blockByCode  = $this->getBlockService()->getBlockByCode($blockFields1['code']);
        $this->assertEquals($blockByCode['code'], $createBlock1['code']);
    }

    /**
     * @group get
     */
    public function testGetBlockWithNotExistCode()
    {
        $getBlock = $this->getBlockService()->getBlockByCode("not_exist_code");
        $this->assertEquals(0, $getBlock['blockId']);
    }

    /**
     * @group search
     */
    public function testSearchBlockTemplateCount()
    {
        $BlockCount = $this->getBlockService()->searchBlockTemplateCount(array('title' => '默认'));
        $this->assertEquals(1, $BlockCount);
    }

    /**
     * @group search
     */
    public function testSearchBlockCountWithEmptyBlock()
    {
        $BlockCount = $this->getBlockService()->searchBlockTemplateCount(array('title' => 'default'));
        $this->assertEquals(0, $BlockCount);
    }

    /**
     * @group search
     */
    public function testSearchBlocksWithStartAndLimit()
    {
        $template     = array('code' => 'test1', 'mode' => 'template', 'category' => 'test1', 'meta' => 'test', 'data' => '', 'templateName' => 'template', 'title' => '默认');
        $template1    = $this->getBlockService()->createBlockTemplate($template);
        $findedBlocks = $this->getBlockService()->searchBlockTemplates(array('title' => '默认'), array('createdTime', 'DESC'), 0, 2);

        $this->assertEquals(2, count($findedBlocks));
    }

    /**
     * @group search
     */
    public function testSearchBlocksWithEmptyBlocks()
    {
        $findedBlocks = $this->getBlockService()->searchBlockTemplates(array('title' => '默认'), array('createdTime', 'DESC'), 0, 30);
        $this->assertEquals(1, count($findedBlocks));
    }

    /**
     * @group history
     */
    public function testfindBlockHistorysByBlockId()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $historys     = $this->getBlockService()->findBlockHistorysByBlockId($createBlock1['id'], 0, 30);

        $this->assertEquals($createBlock1['id'], $historys[0]['blockId']);
        $this->assertNotEmpty($historys[0]['content']);
    }

    /**
     * @group history
     */
    public function testSearchBlockHistorysWithNotExistBlock()
    {
        $historys = $this->getBlockService()->findBlockHistorysByBlockId(999, 0, 30);
        $this->assertEmpty($historys);
    }

    /**
     * @group history
     */
    public function testfindBlockHistoryCountByBlockId()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $count          = $this->getBlockService()->findBlockHistoryCountByBlockId($createBlock1['id']);

        $this->assertEquals(1, $count);
    }

    /**
     * @group history
     */
    public function testsearchBlockHistoryCountWithNotExistBlockId()
    {
        $count = $this->getBlockService()->findBlockHistoryCountByBlockId(999);
        $this->assertEquals(0, $count);
    }

    /**
     * @group history
     */
    public function testGetBlockHistory()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $historys       = $this->getBlockService()->findBlockHistorysByBlockId($createBlock1['id'], 0, 30);
        $blockHistory   = $this->getBlockService()->getBlockHistory($historys[0]['id']);

        $this->assertEquals($historys[0], $blockHistory);
    }

    /**
     * @group history
     */
    public function testGetBlockHistoryWithNotExistId()
    {
        $blockHistory = $this->getBlockService()->getBlockHistory(999);
        $this->assertNull($blockHistory);
    }

    /**
     * @group create
     */
    public function testCreateBlock()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $this->assertNotNull($createBlock1);
    }

    /**
     * @group create
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateBlockWithIncompleteFields()
    {
        $blockFields1 = array(
            'code'    => 'homepage_course_slide',
            'data'    => 'data',
            'content' => 'content'
        );

        $this->getBlockService()->createBlock($blockFields1);
    }

    /**
     * @group update
     */
    public function testUpdateBlock()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $updateInfo     = array('content' => 'updated content');
        $updatedBlock   = $this->getBlockService()->updateBlock($createBlock1['id'], $updateInfo);
        $this->assertEquals($updateInfo['content'], $updatedBlock['content']);
    }

    /**
     * @group update
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateBlockWithNotExistId()
    {
        $updateInfo = array('content' => 'updated content');
        $this->getBlockService()->updateBlock(999, $updateInfo);
    }

    /**
     * @group delete
     */
    public function testDeleteBlock()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $result         = $this->getBlockService()->deleteBlock($createBlock1['id']);
        $this->assertEquals(1, $result);
        $result = $this->getBlockService()->deleteBlock($createBlock1['id']);
        $this->assertEquals(0, $result);
    }

    /**
     * @group  updateBlock
     */
    public function testUpdateContent()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $result         = $this->getBlockService()->updateBlock($createBlock1['id'], array('content' => 'tmp'));
        $this->assertEquals("tmp", $result['content']);
    }

    /**
     * @group  updateBlock
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateContentWithNotExistBlockId()
    {
        $this->getBlockService()->updateBlock(999, "updated_content");
    }

    /**
     * @group getContentsByCodes
     */
    public function testGetContentsByCodes()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $contents       = $this->getBlockService()->getContentsByCodes(array('empty_code', 'homepage_course_slide'));
        $this->assertEmpty($contents['empty_code']);
        $this->assertEquals($contents['homepage_course_slide'], $createBlock1['content']);
    }

    /**
     * @group getContentsByCodes
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testGetContentsByCodesWithEmpty()
    {
        $blockFields1 = array(
            'code'            => 'homepage_course_slide',
            'data'            => 'data',
            'content'         => 'content',
            'blockTemplateId' => 1
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1   = $this->getBlockService()->createBlock($blockFields1);
        $contents       = $this->getBlockService()->getContentsByCodes(array());
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }
}
