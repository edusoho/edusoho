# CHANGELOG

## v0.0.5

 * `GeneralDaoInterface`的接口由`search($conditions, $orderby, $start, $limit)`变为``search($conditions, $orderbys, $start, $limit)`。
   变更后原先的`$orderby`参数传入的值`array('field', 'asc')`需改为`array('filed' => 'asc')`。
   变更后支持多个字段的排序，例如`array('field1' => 'asc', 'field2' => 'desc')`。
   其中排序的字段需在`declares`中声明，不然会抛出`DaoException`例如：
   ```
   public function declares()
   {
       return array(
           'orderbys' => array('field1', 'field2'),
           'conditions' => array(
               'field1 = :field1',
               'field2 = :field2',
           ),
       );
   }
   ```
   排序字段涉及到数据库的索引的建立（不然会造成慢查询，拖垮数据库），不是所有字段都可开放给上层排序的，属于存储层逻辑，所以需在Dao中以白名单的方式声明。二来也方便DBA把控SQL的查询优化。

## v0.0.6

* `GeneralDaoInterface`的接口`get($id)`变更为`get($id, $lock = false)`。当lock为true时，获取数据记录的同时并加锁。用于数据库事务中锁定记录。