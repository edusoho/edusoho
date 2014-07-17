<?php
namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\SettingService;

class SettingServiceImpl extends BaseService implements SettingService
{
    const CACHE_NAME = 'settings';

    private $cached;

    public function set($name, $value)
    {
        $this->getSettingDao()->deleteSettingByName($name);
        $setting = array(
            'name'  => $name,
            'value' => serialize($value)
        );
        $this->getSettingDao()->addSetting($setting);
        $this->clearCache();
    }

    public function get($name, $default = NULL)
    {
        if (is_null($this->cached)) {
            $this->cached = $this->getCacheService()->get(self::CACHE_NAME);
            if (is_null($this->cached)) {
                $settings = $this->getSettingDao()->findAllSettings();
                foreach ($settings as $setting) {
                    $this->cached[$setting['name']] = $setting['value'];
                }
                $this->getCacheService()->set(self::CACHE_NAME, $this->cached);
            }
        }

        return isset($this->cached[$name]) ? unserialize($this->cached[$name]) : $default;
    }

    public function delete($name)
    {
        $this->getSettingDao()->deleteSettingByName($name);
        $this->clearCache();
    }

    public function getField($id)
    {
        return $this->getUserFieldDao()->getField($id);
    }

    public function addUserField($fields)
    {   
        $fieldName=$this->checkType($fields['field_type']);
        if($fieldName==false) return false;
        $field['fieldName']=$fieldName;
        $field['title']=$fields['field_title'];
        $field['seq']=$fields['field_seq'];
        $field['enabled']=0;
        if(isset($fields['field_enabled'])) $field['enabled']=1;
        $field['createdTime']=time();

        return $this->getUserFieldDao()->addField($field);
    }
    public function searchFieldCount($condition)
    {
        return $this->getUserFieldDao()->searchFieldCount($condition);
    }

    public function getAllFieldsOrderBySeq()
    {
        return $this->getUserFieldDao()->getAllFieldsOrderBySeq();
    }

    public function updateField($id,$fields)
    {   
        return $this->getUserFieldDao()->updateField($id, $fields);
    }

    private function checkType($type)
    {   
        $fieldName="";
        if($type=="text"){
            for($i=1;$i<11;$i++){
                $field=$this->getUserFieldDao()->getFieldByFieldName("textField".$i);
                if(!$field){
                    $fieldName="textField".$i;
                    break;
                }
            }
        }
        if($type=="int"){
           for($i=1;$i<6;$i++){
                $field=$this->getUserFieldDao()->getFieldByFieldName("intField".$i);
                if(!$field){
                    $fieldName="intField".$i;
                    break;
                }
            }
        }
        if($type=="date"){
             for($i=1;$i<6;$i++){
                $field=$this->getUserFieldDao()->getFieldByFieldName("dateField".$i);
                if(!$field){
                    $fieldName="dateField".$i;
                    break;
                }
            }
        }
        if($type=="float"){
            for($i=1;$i<6;$i++){
                $field=$this->getUserFieldDao()->getFieldByFieldName("floatField".$i);
                if(!$field){
                    $fieldName="floatField".$i;
                    break;
                }
            }
        }
        if($type=="varchar"){
            for($i=1;$i<11;$i++){
                $field=$this->getUserFieldDao()->getFieldByFieldName("varcharField".$i);
                if(!$field){
                    $fieldName="varcharField".$i;
                    break;
                }
            }
        }
        if($fieldName=="") return false;
        return $fieldName;
        
    }

    protected function clearCache()
    {
        $this->getCacheService()->clear(self::CACHE_NAME);
        $this->cached = null;
    }

    protected function getCacheService()
    {
        return $this->createService('System.CacheService');
    }

    protected function getUserFieldDao()
    {
        return $this->createDao('System.UserFieldDao');
    }

    protected function getSettingDao ()
    {
        return $this->createDao('System.SettingDao');
    }

}