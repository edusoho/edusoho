<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use PHPUnit\Framework\TestCase;
use Mockery;

class IntegrationTestCase extends TestCase
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    public static $classLoader = null;

    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var \Redis|\RedisArray
     */
    protected $redis;

    public function setUp()
    {
        $this->biz = $this->createBiz();

        $this->db = $this->biz['db'];
        $this->db->beginTransaction();
    }

    public function tearDown()
    {
        $this->db->rollBack();

        unset($this->db);
        unset($this->biz);
    }

    /**
     * 用于 mock　service　和　dao
     * 如　$this->mockObjectIntoBiz(
     *      'Course:CourseService',
     *       array(
     *          array(
     *              'functionName' => 'tryManageCourse',
     *              'returnValue' => array('id' => 1),
     *          ),
     *      )
     *  );
     * ＠param $alias  createService　或　createDao 里面的字符串
     * ＠param $params 二维数组
     *  array(
     *      array(
     *          'functionName' => 'tryManageCourse',　//必填
     *          'returnValue' => array('id' => 1),　// 非必填，填了表示有相应的返回结果
     *          'throwException' => new \Exception(), //object Exception or string Exception ，和returnValue 只能二选一，否则throwException优先
     *          'withParams' => array('param1', array('arrayParamKey1' => '123')),　
     *                          //非必填，表示填了相应参数才会有相应返回结果
     *                          //参数必须要用一个数组包含
     *          'runTimes' => 1 //非必填，表示跑第几次会出相应结果, 不填表示无论跑多少此，结果都一样
     *      )
     *  )
     *
     * @return \Mockery\MockInterface
     */
    protected function mockObjectIntoBiz($alias, $params = array())
    {
        $aliasList = explode(':', $alias);
        $className = end($aliasList);
        $mockObj = Mockery::mock($className);

        foreach ($params as $param) {
            $expectation = $mockObj->shouldReceive($param['functionName']);

            if (!empty($param['runTimes'])) {
                $expectation = $expectation->times($param['runTimes']);
            }

            if (!empty($param['withParams'])) {
                $expectation = $expectation->withArgs($param['withParams']);
            } else {
                $expectation = $expectation->withAnyArgs();
            }

            if (!empty($param['returnValue'])) {
                $expectation->andReturn($param['returnValue']);
            }

            if (!empty($param['andReturnValues'])) {
                $expectation->andReturnValues($param['andReturnValues']);
            }

            if (!empty($param['throwException'])) {
                $expectation->andThrow($param['throwException']);
            }
        }

        $this->biz['@'.$alias] = $mockObj;

        return $mockObj;
    }

    protected function createBiz(array $options = array())
    {
        return require dirname(__DIR__).'/biz.php';
    }
}
