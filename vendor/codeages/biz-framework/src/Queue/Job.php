<?php

namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Context\Biz;

interface Job
{
    /**
     * 任务执行返回状态码：完成
     */
    const FINISHED = 0;

    /**
     * 任务执行返回状态码：失败
     */
    const FAILED = 1;

    /**
     * 任务执行返回状态码：失败并重试
     */
    const FAILED_RETRY = 2;

    /**
     * 任务默认执行超时时间(单位：秒)
     */
    const DEFAULT_TIMEOUT = 60;

    /**
     * 任务执行优先级：最高
     */
    const HIGHEST_PRIORITY = 1;

    /**
     * 任务执行优先级：高
     */
    const HIGH_PRIORITY = 100;

    /**
     * 任务执行优先级：默认
     */
    const DEFAULT_PRIORITY = 1000;

    /**
     * 任务执行优先级：低
     */
    const LOW_PRIORITY = 2000;

    /**
     * 任务执行优先级：最低
     */
    const LOWEST_PRIORITY = 10000;

    public function execute();

    public function getId();

    public function setId($id);

    public function getBody();

    public function setBody($body);

    public function getMetadata($key = null, $default = null);

    public function setMetadata($spec = null, $value = null);

    public function setBiz(Biz $biz);
}
