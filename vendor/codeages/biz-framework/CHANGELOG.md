# CHANGELOG

## v0.3.10

  * 增加`Targetlog`。

## v0.3.7

  * `create`方法不更新`update`Field

## v0.2.13

  * 替换一致性哈希库

## v0.2.12

  * 修复`php`方式序列化错误

## v0.2.8

## v0.2.7

  * 增加 `sql` 方法用以防止拼接SQL时候造成的SQL注入问题
  * `update` 方法第一个参数支持 `id` 或 `conditions`

## v0.2.6

  * 修复`getByFields`返回array
  * 序列化引用问题

## v0.2.5

  * 修复`unserialize`方法未反序列化null值的情况。

## v0.2.4

  * 修复`getByFields`方法,返回NULL的情况。

## v0.2.3

  * 修复单元测试 PHPUnit\Framework\TestCase Not Found

## v0.2.2

  * 新增BizAware Class 和 BizAware Trait

## v0.2.1

  * 修复Bad Smells。
  * GeneralDaoImpl的`_createQueryBuilder`重命名为`createQueryBuilder`，`_getQueryBuilder`重命名为`getQueryBuilder`。

## v0.2.0

  * DAO `declares`方法`IN`关键字,如果传入的参数是空数组会返回空记录

## v0.1.9

 * DAO `declares`方法的`LIKE`关键字默认是模糊全匹配，即'%xxx%';
 * DAO `declares`方法的增加`PRE_LIKE`关键字,前缀字符串匹配，即'xxx%';
 * DAO `declares`方法的增加`SUF_LIKE`关键字,后缀字符串匹配，即'%xxx';

## v0.0.5

 * `GeneralDaoInterface`的接口由`search($conditions, $orderby, $start, $limit)`变为`search($conditions, $orderbys, $start, $limit)`。
   变更后原先的`$orderby`参数传入的值`array('field', 'asc')`需改为`array('filed' => 'asc')`。
   变更后支持多个字段的排序，例如`array('field1' => 'asc', 'field2' => 'desc')`。
   其中排序的字段需在`declares`中声明，不然会抛出`DaoException`例如：
   ```php
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
