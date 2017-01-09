<?php

namespace Tests\Dictionary;

use Biz\BaseTestCase;;

class DictionaryServiceTest extends BaseTestCase
{
    public function testAddDictionary()
    {
        $dict = array(
            'name' => '字典007',
            'type' => 'dict_007'
        );
        $result = $this->getDictionaryService()->addDictionary($dict);
        $this->assertTrue($result['id'] > 0);
    }

    public function testFindAllDictionaries()
    {
        $dict = array(
            'name' => '字典007',
            'type' => 'dict_007'
        );
        $this->getDictionaryService()->addDictionary($dict);
        $dicts = $this->getDictionaryService()->findAllDictionaries();
        $this->assertTrue(sizeof($dicts) === 1);
    }

    public function testGetDictionaryItem()
    {
        $item = array(
            'type' => 'dict_007',
            'code' => 'item_001',
            'name' => '这是第一个字典项'
        );
        $item = $this->getDictionaryService()->addDictionaryItem($item);
        $this->assertTrue($item['id'] > 0);
    }

    public function testDeleteDictionaryItem()
    {
        $item = array(
            'type' => 'dict_007',
            'code' => 'item_001',
            'name' => '这是第一个字典项'
        );
        $item = $this->getDictionaryService()->addDictionaryItem($item);
        $this->getDictionaryService()->deleteDictionaryItem($item['id']);
        $result = $this->getDictionaryService()->getDictionaryItem($item['id']);
        $this->assertEmpty($result);
    }

    public function testUpdateDictionaryItem()
    {
        $item = array(
            'type' => 'dict_007',
            'code' => 'item_001',
            'name' => '这是第一个字典项'
        );
        $created = $this->getDictionaryService()->addDictionaryItem($item);

        $created['name'] = '字典项名字变啦';
        $updated         = $this->getDictionaryService()->updateDictionaryItem($created['id'], $created);
        $this->assertTrue($updated['name'] != $item['name']);
    }

    public function testAddDictionaryItem()
    {
        $item = array(
            'type' => 'dict_007',
            'code' => 'item_001',
            'name' => '这是第一个字典项'
        );
        $created = $this->getDictionaryService()->addDictionaryItem($item);
        $this->assertTrue($created['id'] > 0);
    }

    public function testFindAllDictionaryItemsOrderByWeight()
    {
        $item = array(
            'type'   => 'dict_007',
            'code'   => 'item_001',
            'name'   => '这是第一个字典项',
            'weight' => 11
        );
        $this->getDictionaryService()->addDictionaryItem($item);

        $item2 = array(
            'type'   => 'dict_007',
            'code'   => 'item_002',
            'name'   => '这是第一个字典项',
            'weight' => 22
        );
        $this->getDictionaryService()->addDictionaryItem($item2);
        $items = $this->getDictionaryService()->findAllDictionaryItemsOrderByWeight();
        $this->assertTrue($items[0]['code'] === 'item_002');
    }

    public function testFindDictionaryItemByName()
    {
        $item = array(
            'type'   => 'dict_007',
            'code'   => 'item_001',
            'name'   => '大杭州',
            'weight' => 11
        );
        $this->getDictionaryService()->addDictionaryItem($item);

        $result = $this->getDictionaryService()->findDictionaryItemByName('大杭州');
        $this->assertTrue(sizeof($result) === 1);

        $result = $this->getDictionaryService()->findDictionaryItemByName('大杭州11');
        $this->assertEmpty($result);
    }

    public function testFindDictionaryItemByType()
    {
        $item = array(
            'type'   => 'dict_007',
            'code'   => 'item_001',
            'name'   => '大杭州',
            'weight' => 11
        );
        $this->getDictionaryService()->addDictionaryItem($item);

        $result = $this->getDictionaryService()->findDictionaryItemByType('dict_007');
        $this->assertTrue(sizeof($result) === 1);

        $result = $this->getDictionaryService()->findDictionaryItemByType('dict_008');
        $this->assertEmpty($result);
    }

    protected function getDictionaryService()
    {
        return $this->getBiz()->service('Dictionary:DictionaryService');
    }
}
