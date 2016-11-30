<?php

namespace Biz\Question\Config;

use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;

abstract class Question
{
    private $biz;

    /**
     * @inheritdoc
     */
    public function create($fields)
    {
    }

    /**
     * @inheritdoc
     */
    public function update($targetId, $fields)
    {
    }

    /**
     * @inheritdoc
     */
    public function delete($targetId)
    {
    }

    /**
     * @inheritdoc
     */
    public function get($targetId)
    {
    }

    final public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * Activity 创建、编辑、进行时指定的Controller配置
     *
     * 如：
     * ExampleActivity
     * array(
     *    'create' => 'ExampleBundle:Example:create',
     *    'edit' => 'ExampleBundle:Example:edit',
     *    'create' => 'ExampleBundle:Example:create'
     * )
     *
     * @return array<String, String>
     */
    abstract public function registerActions();

    abstract public function registerTemplates();

    /**
     * @param  $action
     * @return String
     */
    final public function getAction($action)
    {
        $map = $this->registerActions();
        return $map[$action];
    }

    final public function getTemplate($code)
    {
        $map = $this->registerTemplates();
        return $map[$code];
    }

    abstract public function getMetas();

    abstract public function judge($question, $answer);

    abstract public function filter($fields, $mode);

    abstract public function isNeedCheck();

    protected function commonFilter($fields, $mode)
    {
        if (!empty($fields['target']) && $fields['target'] > 0) {
            $fields['lessonId'] = $fields['target'];
            unset($fields['target']);
        }
        $fields = ArrayToolkit::filter($fields, array(
            'type'          => '',
            'stem'          => '',
            'difficulty'    => 'simple',
            'userId'        => 0,
            'answer'        => array(),
            'analysis'      => '',
            'metas'         => array(),
            'score'         => 0,
            'categoryId'    => 0,
            'parentId'      => 0,
            'copyId'        => 0,
            'target'        => '',
            'courseId'      => 0,
            'lessonId'      => 0,
            'subCount'      => 0,
            'finishedTimes' => 0,
            'passedTimes'   => 0,
            'userId'        => 0,
            'updatedTime'   => 0,
            'createdTime'   => 0
        ));

        if ($mode == 'create') {
            $fields['createdTime'] = time();
        } else {
            $fields['updatedTime'] = time();
        }

        return $fields;
    }

    /**
     * @return Biz
     */
    protected function getBiz()
    {
        return $this->biz;
    }
}
