<?php

namespace Tests;

use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Tests\Example\Dao\AdvancedExampleDao;

class BatchUpdateHelperTest extends IntegrationTestCase
{
    public function testFlush()
    {
        $count = 1000;

        $this->createBatchRecord($count);

        $examples = $this->getAdvancedExampleDao()->search(array(), array(), 0, $count);
        $helper = new BatchUpdateHelper($this->getAdvancedExampleDao());
        foreach ($examples as $example) {
            $update['name'] = 'change_name_'.$example['id'];
            $update['content'] = 'change_content_'.$example['id'];

            $helper->add('id', $example['id'], $update);
        }

        $helper->flush();

        $examples = $this->getAdvancedExampleDao()->search(array(), array(), 0, $count);

        $this->assertEquals('change_name_'.$examples[0]['id'], $examples[0]['name']);
        $this->assertEquals('change_content_'.$examples[0]['id'], $examples[0]['content']);
        $this->assertEquals('change_name_'.$examples[400]['id'], $examples[400]['name']);
        $this->assertEquals('change_content_'.$examples[400]['id'], $examples[400]['content']);
    }

    public function testGet()
    {
        $examples = array(
            array('id' => 1, 'name' => 'name1'),
            array('id' => 2, 'name' => 'name2'),
            array('id' => 3, 'name' => 'name3'),
            array('id' => 4, 'name' => 'name4'),
            array('id' => 5, 'name' => 'name5'),
            array('id' => 6, 'name' => 'name6'),
        );

        $helper = new BatchUpdateHelper($this->getAdvancedExampleDao());
        foreach ($examples as $example) {
            $update['name'] = $example['name'];
            $helper->add('id', $example['id'], $update);
        }

        $updateFields = $helper->get('id', 5);
        $this->assertEquals('name5', $updateFields['name']);
    }

    public function testFindIdentifyKeys()
    {
        $examples = array(
            array('id' => 1, 'name' => 'name1'),
            array('id' => 2, 'name' => 'name2'),
            array('id' => 3, 'name' => 'name3'),
            array('id' => 4, 'name' => 'name4'),
            array('id' => 5, 'name' => 'name5'),
            array('id' => 6, 'name' => 'name6'),
        );

        $helper = new BatchUpdateHelper($this->getAdvancedExampleDao());
        foreach ($examples as $example) {
            $update['name'] = $example['name'];
            $helper->add('id', $example['id'], $update);
        }

        $identifyKeys = $helper->findIdentifyKeys('id');

        $this->assertEquals(array(1, 2, 3, 4, 5, 6), $identifyKeys);
    }

    private function createBatchRecord($count)
    {
        $news = array();
        for ($i = 1; $i <= $count; ++$i) {
            $fields = array(
                'name' => 'test'.$i,
                'content' => 'content',
            );
            $news[] = $fields;
        }

        $this->getAdvancedExampleDao()->batchCreate($news);
    }

    /**
     * @return AdvancedExampleDao
     */
    private function getAdvancedExampleDao()
    {
        return $this->biz->dao('Example:AdvancedExampleDao');
    }
}
