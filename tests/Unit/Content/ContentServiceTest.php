<?php

namespace Tests\Unit\Content;

use Biz\BaseTestCase;
use Biz\Content\Service\ContentService;

class ContentServiceTest extends BaseTestCase
{
    public function testCreateContent()
    {
        $fields = array('title' => 'test article', 'type' => 'article');

        $content = $this->getContentService()->createContent($fields);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals($fields['title'], $content['title']);
        $this->assertEquals($fields['type'], $content['type']);
    }

    public function testUpdateContent()
    {
        $content = $this->getContentService()->createContent(array(
            'title' => 'test article',
            'type' => 'article',
        ));

        $fields = array(
            'title' => 'updated title',
            'body' => 'updated body',
        );
        $updated = $this->getContentService()->updateContent($content['id'], $fields);

        $this->assertEquals($fields['title'], $updated['title']);
        $this->assertEquals($fields['body'], $updated['body']);
    }

    public function testGetContent()
    {
        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getContentService()->getContent(111);

        $this->assertEquals(array('id' => 111, 'title' => 'title'), $result);
    }

    public function testGetContentByAlias()
    {
        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'getByAlias',
                    'returnValue' => array('id' => 111, 'alias' => 'alias'),
                    'withParams' => array('alias'),
                ),
            )
        );
        $result = $this->getContentService()->getContentByAlias('alias');

        $this->assertEquals(array('id' => 111, 'alias' => 'alias'), $result);
    }

    public function testSearchContents()
    {
        $this->mockBiz(
            'Taxonomy:CategoryService',
            array(
                array(
                    'functionName' => 'findCategoryChildrenIds',
                    'returnValue' => array(222),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 111, 'alias' => 'alias')),
                    'withParams' => array(array('categoryIds' => array(111, 222)), array('createdTime' => 'DESC'), 0, 5),
                ),
            )
        );
        $result = $this->getContentService()->searchContents(array('categoryId' => 111), array(), 0, 5);

        $this->assertEquals(array(array('id' => 111, 'alias' => 'alias')), $result);
    }

    public function testSearchContentCount()
    {
        $this->mockBiz(
            'Taxonomy:CategoryService',
            array(
                array(
                    'functionName' => 'findCategoryChildrenIds',
                    'returnValue' => array(222),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 1,
                    'withParams' => array(array('categoryIds' => array(111, 222))),
                ),
            )
        );
        $result = $this->getContentService()->searchContentCount(array('categoryId' => 111));

        $this->assertEquals(1, $result);
    }

    public function testTrashContent()
    {
        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'update',
                    'withParams' => array(111, array('status' => 'trash')),
                ),
            )
        );
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array('content', 'trash', '内容#111移动到回收站'),
                ),
            )
        );
        $this->getContentService()->trashContent(111);
        $this->getContentDao()->shouldHaveReceived('update');
        $this->getLogService()->shouldHaveReceived('info');
    }

    public function testDeleteContent()
    {
        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'delete',
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array('content', 'delete', '内容#111永久删除'),
                ),
            )
        );
        $this->getContentService()->deleteContent(111);
        $this->getContentDao()->shouldHaveReceived('delete');
        $this->getLogService()->shouldHaveReceived('info');
    }

    public function testPublishContent()
    {
        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'update',
                    'withParams' => array(111, array('status' => 'published')),
                ),
            )
        );
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array('content', 'publish', '内容#111发布'),
                ),
            )
        );
        $this->getContentService()->publishContent(111);
        $this->getContentDao()->shouldHaveReceived('update');
        $this->getLogService()->shouldHaveReceived('info');
    }

    public function testIsAliasAvaliable()
    {
        $result1 = $this->getContentService()->isAliasAvaliable(array());
        $this->assertTrue($result1);

        $this->mockBiz(
            'Content:ContentDao',
            array(
                array(
                    'functionName' => 'getByAlias',
                    'returnValue' => array('id' => 111),
                    'withParams' => array('alias'),
                ),
            )
        );
        $result2 = $this->getContentService()->isAliasAvaliable('alias');

        $this->assertFalse($result2);
    }

    /**
     * @return ContentService
     */
    protected function getContentService()
    {
        return $this->createService('Content:ContentService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return ContentDao
     */
    protected function getContentDao()
    {
        return $this->createDao('Content:ContentDao');
    }
}
