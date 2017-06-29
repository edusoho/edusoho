<?php

namespace Biz\Dictionary\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Dictionary\Dao\DictionaryDao;
use Biz\Dictionary\Service\DictionaryService;

class DictionaryServiceImpl extends BaseService implements DictionaryService
{
    public function addDictionary($dictionary)
    {
        return $this->getDictionaryDao()->create($dictionary);
    }

    public function findAllDictionaries()
    {
        return $this->getDictionaryDao()->findAll();
    }

    public function getDictionaryItem($id)
    {
        return $this->getDictionaryItemDao()->get($id);
    }

    public function deleteDictionaryItem($id)
    {
        $this->getDictionaryItemDao()->delete($id);
    }

    public function updateDictionaryItem($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('type', 'type', 'name', 'weight'));

        return $this->getDictionaryItemDao()->update($id, $fields);
    }

    public function addDictionaryItem($fields)
    {
        return $this->getDictionaryItemDao()->create($fields);
    }

    public function findAllDictionaryItemsOrderByWeight()
    {
        return $this->getDictionaryItemDao()->findAllOrderByWeight();
    }

    public function findDictionaryItemByName($name)
    {
        return $this->getDictionaryItemDao()->findByName($name);
    }

    public function findDictionaryItemByType($type)
    {
        return $this->getDictionaryItemDao()->findByType($type);
    }

    protected function getDictionaryItemDao()
    {
        return $this->createDao('Dictionary:DictionaryItemDao');
    }

    /**
     * @return DictionaryDao
     */
    protected function getDictionaryDao()
    {
        return $this->createDao('Dictionary:DictionaryDao');
    }
}
