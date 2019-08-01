<?php

namespace Tests\Unit\Content\Service;

use Biz\BaseTestCase;
use Biz\Content\Service\BlockService;
use Biz\User\Service\UserService;

class BlockServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $template = array('code' => 'test', 'mode' => 'template', 'category' => 'test', 'meta' => 'test', 'data' => '', 'templateName' => 'template', 'title' => '默认');
        $this->getBlockService()->createBlockTemplate($template);
    }

    public function testCreateBlockTemplate()
    {
        $template = array('code' => 'test', 'mode' => 'template', 'category' => 'test', 'meta' => 'test', 'data' => '', 'templateName' => 'template', 'title' => '默认');
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 111, 'code' => 'test'),
                    'withParams' => array(array(
                        'code' => 'test',
                        'mode' => 'template',
                        'category' => 'test',
                        'meta' => 'test',
                        'data' => '',
                        'templateName' => 'template',
                        'title' => '默认',
                    )),
                ),
            )
        );
        $result = $this->getBlockService()->createBlockTemplate($template);

        $this->assertEquals(array('id' => 111, 'code' => 'test'), $result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreateBlockTemplateWithoutCode()
    {
        $template = array('mode' => 'template', 'category' => 'test', 'meta' => 'test', 'data' => '', 'templateName' => 'template', 'title' => '默认');
        $this->getBlockService()->createBlockTemplate($template);
    }

    public function testGetLatestBlockHistory()
    {
        $this->mockBiz(
            'Content:BlockHistoryDao',
            array(
                array(
                    'functionName' => 'getLatest',
                    'returnValue' => array('id' => 111, 'blockId' => 111),
                ),
            )
        );
        $result = $this->getBlockService()->getLatestBlockHistory();

        $this->assertEquals(array('id' => 111, 'blockId' => 111), $result);
    }

    public function testGetLatestBlockHistoriesByBlockIds()
    {
        $this->mockBiz(
            'Content:BlockHistoryDao',
            array(
                array(
                    'functionName' => 'getLatestByBlockId',
                    'returnValue' => array('id' => 111, 'blockId' => 111),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:BlockDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'blockTemplateId' => 1),
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getBlockService()->getLatestBlockHistoriesByBlockIds(array(111));

        $this->assertEquals(array('id' => 111, 'blockId' => 111), $result[1]);
    }

    public function testGetBlocksByBlockTemplateIdsAndOrgId()
    {
        $this->mockBiz(
            'Content:BlockDao',
            array(
                array(
                    'functionName' => 'getByTemplateIdAndOrgId',
                    'returnValue' => array('id' => 111, 'blockTemplateId' => 1),
                    'withParams' => array(111, 111),
                ),
            )
        );
        $result = $this->getBlockService()->getBlocksByBlockTemplateIdsAndOrgId(array(111), 111);

        $this->assertEquals(array('id' => 111, 'blockTemplateId' => 1), $result[0]);
    }

    public function testUpdateTemplateContent()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'content' => 'content'),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 111, 'content' => 'test'),
                    'withParams' => array(111, array('content' => 'test')),
                ),
            )
        );
        $result = $this->getBlockService()->updateTemplateContent(111, 'test');

        $this->assertEquals(array('id' => 111, 'content' => 'test'), $result);
    }

    /**
     * @expectedException \Biz\Content\BlockException
     */
    public function testUpdateTemplateContentWithEmptyTemplate()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getBlockService()->updateTemplateContent(111, 'test');
    }

    public function testRecovery()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'mode' => 'html'),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:BlockDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'blockTemplateId' => 111),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 111, 'content' => 'content'),
                    'withParams' => array(111, array('content' => 'content', 'data' => 'data')),
                ),
            )
        );
        $result = $this->getBlockService()->recovery(111, array('content' => 'content', 'data' => 'data'));

        $this->assertEquals(array('id' => 111, 'content' => 'content'), $result);
    }

    /**
     * @expectedException \Biz\Content\BlockException
     */
    public function testRecoveryWithEmptyBlock()
    {
        $this->mockBiz(
            'Content:BlockDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getBlockService()->recovery(111, array('content' => 'content', 'data' => 'data'));
    }

    /**
     * @expectedException \Biz\Content\BlockException
     */
    public function testRecoveryWithTemplateModeAndEmptyData()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'mode' => 'template'),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:BlockDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'blockTemplateId' => 111),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getBlockService()->recovery(111, array('content' => 'content', 'data' => ''));
    }

    public function testGetBlockByTemplateIdAndOrgId()
    {
        $this->mockBiz(
            'Content:BlockDao',
            array(
                array(
                    'functionName' => 'getByTemplateIdAndOrgId',
                    'returnValue' => array(),
                    'withParams' => array(111, 11),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByTemplateIdAndOrgId',
                    'returnValue' => array('id' => 111),
                    'withParams' => array(111, 11),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'id' => 111,
                        'code' => 'code',
                        'template' => 'template',
                        'tips' => 'tips',
                        'mode' => 'html',
                        'meta' => 'meta',
                        'title' => 'title',
                        'templateName' => 'templateName',
                    ),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getBlockService()->getBlockByTemplateIdAndOrgId(111, 11);
        $result2 = $this->getBlockService()->getBlockByTemplateIdAndOrgId(111, 11);

        $this->assertEquals(array('id' => 111, 'blockTemplateId' => 111, 'blockId' => 0), $result1);
        $this->assertEquals(
            array(
                'id' => 111,
                'blockId' => 111,
                'blockTemplateId' => 111,
                'code' => 'code',
                'template' => 'template',
                'tips' => 'tips',
                'mode' => 'html',
                'meta' => 'meta',
                'title' => 'title',
                'templateName' => 'templateName',
            ),
            $result2
        );
    }

    public function testGetBlockTemplate()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'id' => 111,
                        'code' => 'code',
                    ),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getBlockService()->getBlockTemplate(111);
        $result2 = $this->getBlockService()->getBlockTemplate(111);

        $this->assertEquals(array(), $result1);
        $this->assertEquals(array('id' => 111, 'code' => 'code'), $result2);
    }

    public function testDeleteBlockTemplate()
    {
        $this->mockBiz(
            'Content:BlockDao',
            array(
                array(
                    'functionName' => 'getByTemplateId',
                    'returnValue' => array('id' => 111),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:BlockHistoryDao',
            array(
                array(
                    'functionName' => 'deleteByBlockId',
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'delete',
                    'returnValue' => 1,
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getBlockService()->deleteBlockTemplate(111);

        $this->getBlockHistoryDao()->shouldHaveReceived('deleteByBlockId');
        $this->getBlockDao()->shouldHaveReceived('delete');
        $this->assertEquals(1, $result);
    }

    public function testGetBlockTemplateByCode()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'returnValue' => array('id' => 111, 'code' => 'code'),
                    'withParams' => array('code'),
                ),
            )
        );
        $result = $this->getBlockService()->getBlockTemplateByCode('code');

        $this->assertEquals(array('id' => 111, 'code' => 'code'), $result);
    }

    public function testUpdateBlockTemplate()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'code' => 'code'),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 111, 'code' => 'test'),
                    'withParams' => array(111, array('code' => 'test')),
                ),
            )
        );
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array('blockTemplate', 'update_block_template', '更新编辑区模板#111'),
                ),
            )
        );
        $result = $this->getBlockService()->updateBlockTemplate(111, array('code' => 'test'));

        $this->getLogService()->shouldHaveReceived('info');
        $this->assertEquals(array('id' => 111, 'code' => 'test'), $result);
    }

    /**
     * @expectedException \Biz\Content\BlockException
     */
    public function testUpdateBlockTemplateWithEmptyTemplate()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getBlockService()->updateBlockTemplate(111, array());
    }

    public function testSearchBlockTemplates()
    {
        $this->mockBiz(
            'Content:BlockTemplateDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 111)),
                    'withParams' => array(array('id' => 111), array(), 0, 5),
                ),
            )
        );
        $result = $this->getBlockService()->searchBlockTemplates(array('id' => 111), array(), 0, 5);

        $this->assertEquals(array(array('id' => 111)), $result);
    }

    public function testGetBlock()
    {
        $blockFields1 = array(
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $createdBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $getBlock = $this->getBlockService()->getBlock($createdBlock1['id']);
        $this->assertNotNull($getBlock);
    }

    /**
     * @expectedException \Biz\Content\BlockException
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
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $blockByCode = $this->getBlockService()->getBlockByCode($blockFields1['code']);
        $this->assertEquals($blockByCode['code'], $createBlock1['code']);
    }

    /**
     * @group get
     */
    public function testGetBlockWithNotExistCode()
    {
        $getBlock = $this->getBlockService()->getBlockByCode('not_exist_code');
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
        $template = array('code' => 'test1', 'mode' => 'template', 'category' => 'test1', 'meta' => 'test', 'data' => '', 'templateName' => 'template', 'title' => '默认');
        $template1 = $this->getBlockService()->createBlockTemplate($template);
        $findedBlocks = $this->getBlockService()->searchBlockTemplates(array('title' => '默认'), array('createdTime' => 'DESC'), 0, 2);

        $this->assertEquals(2, count($findedBlocks));
    }

    /**
     * @group search
     */
    public function testSearchBlocksWithEmptyBlocks()
    {
        $findedBlocks = $this->getBlockService()->searchBlockTemplates(array('title' => '默认'), array('createdTime' => 'DESC'), 0, 30);
        $this->assertEquals(1, count($findedBlocks));
    }

    /**
     * @group history
     */
    public function testfindBlockHistorysByBlockId()
    {
        $blockFields1 = array(
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $historys = $this->getBlockService()->findBlockHistorysByBlockId($createBlock1['id'], 0, 30);

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
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $count = $this->getBlockService()->findBlockHistoryCountByBlockId($createBlock1['id']);

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
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $historys = $this->getBlockService()->findBlockHistorysByBlockId($createBlock1['id'], 0, 30);
        $blockHistory = $this->getBlockService()->getBlockHistory($historys[0]['id']);

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
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $this->assertNotNull($createBlock1);
    }

    /**
     * @group create
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreateBlockWithIncompleteFields()
    {
        $blockFields1 = array(
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
        );

        $this->getBlockService()->createBlock($blockFields1);
    }

    /**
     * @group update
     */
    public function testUpdateBlock()
    {
        $blockFields1 = array(
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $updateInfo = array('content' => 'updated content');
        $updatedBlock = $this->getBlockService()->updateBlock($createBlock1['id'], $updateInfo);
        $this->assertEquals($updateInfo['content'], $updatedBlock['content']);
    }

    /**
     * @group update
     * @expectedException \Biz\Content\BlockException
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
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $result = $this->getBlockService()->deleteBlock($createBlock1['id']);
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
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $result = $this->getBlockService()->updateBlock($createBlock1['id'], array('content' => 'tmp'));
        $this->assertEquals('tmp', $result['content']);
    }

    /**
     * @group  updateBlock
     * @expectedException \Biz\Content\BlockException
     */
    public function testUpdateContentWithNotExistBlockId()
    {
        $this->getBlockService()->updateBlock(999, 'updated_content');
    }

    /**
     * @group getContentsByCodes
     */
    public function testGetContentsByCodes()
    {
        $blockFields1 = array(
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $contents = $this->getBlockService()->getContentsByCodes(array('empty_code', 'homepage_course_slide'));
        $this->assertEmpty($contents['empty_code']);
        $this->assertEquals($contents['homepage_course_slide'], $createBlock1['content']);
    }

    /**
     * @group getContentsByCodes
     * @expectedException \Biz\Content\BlockException
     */
    public function testGetContentsByCodesWithEmpty()
    {
        $blockFields1 = array(
            'code' => 'homepage_course_slide',
            'data' => 'data',
            'content' => 'content',
            'blockTemplateId' => 1,
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createBlock1 = $this->getBlockService()->createBlock($blockFields1);
        $contents = $this->getBlockService()->getContentsByCodes(array());
    }

    public function testGetPostersWithTopBannerEmpty()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('uri' => 'test'),
            ),
        ));

        $result = $this->getBlockService()->getPosters();
        $this->assertEmpty($result);
    }

    public function testGetPostersWithTopBanner()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('uri' => 'test'),
            ),
        ));
        $this->mockBiz('Content:BlockDao', array(
            array(
                'functionName' => 'getByCode',
                'returnValue' => array(
                    'data' => array(
                        'posters' => array(
                            array(
                                'status' => 1,
                                'src' => 'test.png',
                                'href' => '/test',
                            ),
                        ),
                    ),
                ),
            ),
        ));

        $posters = array(
            array(
                'image' => 'test.png',
                'link' => array('type' => 'url', 'url' => '/test'),
            ),
        );
        $result = $this->getBlockService()->getPosters();
        $this->assertEquals($posters, $result);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return BlockService
     */
    protected function getBlockService()
    {
        return $this->createService('Content:BlockService');
    }

    /**
     * @return BlockHistoryDao
     */
    protected function getBlockHistoryDao()
    {
        return $this->createDao('Content:BlockHistoryDao');
    }

    /**
     * @return BlockDao
     */
    protected function getBlockDao()
    {
        return $this->createDao('Content:BlockDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
