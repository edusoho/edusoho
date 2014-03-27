<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Service\Taxonomy\TagService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class TagServiceImpl extends BaseService implements TagService
{

    public function getTag($id)
    {
        return $this->getTagDao()->getTag($id);
    }

    public function getTagByName($name)
    {
        return $this->getTagDao()->getTagByName($name);
    }
    
    public function isUserlevelNameAvalieable($name, $exclude)
    {

    }

    public function getTagByLikeName($name)
    {
        return $this->getTagDao()->getTagByLikeName($name);
    }

	public function findAllTags($start, $limit)
	{
		return $this->getTagDao()->findAllTags($start, $limit);
	}

    public function getAllTagCount()
    {
        return $this->getTagDao()->findAllTagsCount();
    }

     public function searchTags(array $conditions,array $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareTagConditions($conditions);

        
        
        return TagSerialize::unserializes($this->getTagDao()->searchTags($conditions, $orderBy, $start, $limit));
    }


    public function searchTagCount($conditions)
    {
        $conditions = $this->_prepareTagConditions($conditions);
        return $this->getTagDao()->searchTagCount($conditions);
    }



    public function findTagsByIds(array $ids)
    {
    	return $this->getTagDao()->findTagsByIds($ids);
    }

    public function findTagsByNames(array $names)
    {
    	return $this->getTagDao()->findTagsByNames($names);
    }

    public function isTagNameAvalieable($name, $exclude=null)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $tag = $this->getTagByName($name);

        return $tag ? false : true;
    }

    public function addTag(array $tag)
    {
        $tag = ArrayToolkit::parts($tag, array('name','isStick','stickSeq','stickNum'));

        $this->filterTagFields($tag);
        $tag['createdTime'] = time();

        $tag = $this->getTagDao()->addTag($tag);

        $this->getLogService()->info('tag', 'create', "添加标签{$tag['name']}(#{$tag['id']})");

        return $tag;
    }

    public function updateTag($id, array $fields)
    {
        $tag = $this->getTag($id);
        if (empty($tag)) {
            throw $this->createServiceException("标签(#{$id})不存在，更新失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name','isStick','stickSeq','stickNum'));

        $this->filterTagFields($fields, $tag);

        $this->getLogService()->info('tag', 'update', "编辑标签{$fields['name']}(#{$id})");

        return $this->getTagDao()->updateTag($id, $fields);
    }

    public function deleteTag($id)
    {
        $this->getTagDao()->deleteTag($id);

        $this->getLogService()->info('tag', 'delete', "编辑标签#{$id}");
    }

    private function filterTagFields(&$tag, $relatedTag = null)
    {
        if (empty($tag['name'])) {
            throw $this->createServiceException('标签名不能为空，添加失败！');
        }

        $tag['name'] = (string) $tag['name'];

        $exclude = $relatedTag ? $relatedTag['name'] : null;

        if (!$this->isTagNameAvalieable($tag['name'], $exclude)) {
            throw $this->createServiceException('该标签名已存在，添加失败！');
        }

        return $tag;
    }


     private function _prepareTagConditions($conditions)
    {
        $conditions = array_filter($conditions);
        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday'=>array(
                    strtotime('yesterday'),
                    strtotime('today'),
                ),
                'today'=>array(
                    strtotime('today'),
                    strtotime('tomorrow'),
                ),
                'this_week' => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ),
                'last_week' => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ),
                'next_week' => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'), 
                    strtotime('first day of next month midnight'),
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ),
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
                $conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        return $conditions;
    }

	private function getTagDao()
	{
        return $this->createDao('Taxonomy.TagDao');
	}

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}


class TagSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$tag)
    {
       
        if (isset($tag['strcreatedTime'])) {
            if (!empty($tag['strcreatedTime'])) {
                $tag['createdTime'] = strtotime($tag['strcreatedTime']);
            }
        }
        unset($tag['strcreatedTime']);


        return $tag;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $tag = null)
    {
        if (empty($tag)) {
            return $tag;
        }


        if(empty($tag['createdTime'])){
            $tag['createdTime']='';
        }else{
            $tag['paidTimeNum']=$tag['createdTime'];
            $tag['createdTime']=date("Y-m-d H:i",$tag['createdTime']);
        }

        return $tag;
    }

    public static function unserializes(array $tags)
    {
        return array_map(function($tag) {
            return TagSerialize::unserialize($tag);
        }, $tags);
    }
} 