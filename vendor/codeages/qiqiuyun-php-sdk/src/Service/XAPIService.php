<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\Exception\ResponseException;
use QiQiuYun\SDK\Exception\SDKException;
use QiQiuYun\SDK\Constants\XAPIActivityTypes;
use QiQiuYun\SDK\Constants\XAPIVerbs;

class XAPIService extends BaseService
{
    protected $host = 'xapi.qiqiuyun.net';

    protected $defaultLang = 'zh-CN';

    /**
     * 提交"听音频"的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function listenAudio($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://activitystrea.ms/schema/1.0/listen',
            'display' => array(
                'zh-CN' => '听了',
                'en-US' => 'listened',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => 'http://activitystrea.ms/schema/1.0/audio',
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['globalId']) ? 0 : $object['resource']['globalId'],
                        'name' => empty($object['resource']['filename']) ? '' : $object['resource']['filename'],
                    ),
                ),
            ),
        );

        $statement['result'] = array(
            'duration' => $this->convertTime($result['duration']),
        );
        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“观看视频”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function watchVideo($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/acrossx/verbs/watched',
            'display' => array(
                'zh-CN' => '观看了',
                'en-US' => 'watched',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => 'https://w3id.org/xapi/acrossx/activities/video',
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['globalId']) ? 0 : $object['resource']['globalId'],
                        'name' => empty($object['resource']['filename']) ? '' : $object['resource']['filename'],
                    ),
                ),
            ),
        );

        $statement['result'] = array(
            'duration' => $this->convertTime($result['duration']),
        );
        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成任务”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function finishActivity($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了',
                'en-US' => 'completed',
            ),
        );

        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['globalId']) ? 0 : $object['resource']['globalId'],
                        'name' => empty($object['resource']['filename']) ? '' : $object['resource']['filename'],
                    ),
                ),
            ),
        );

        $statement['result'] = array(
            'success' => true,
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成任务的弹题”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function finishActivityQuestion($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/answered',
            'display' => array(
                'zh-CN' => '回答了',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => 'http://adlnet.gov/expapi/activities/interaction',
                'interactionType' => $object['type'],
                'description' => array(
                    $this->defaultLang => $object['stem'],
                ),
                'correctResponsesPattern' => $object['answer'],
                'choices' => $object['choices'],
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                    'http://xapi.edusoho.com/extensions/activity' => array(
                        'id' => $object['activity']['id'],
                        'title' => $object['activity']['title'],
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['globalId']) ? 0 : $object['resource']['globalId'],
                        'name' => empty($object['resource']['filename']) ? '' : $object['resource']['filename'],
                    ),
                ),
            ),
        );

        $statement['result'] = array(
            'success' => $result['success'],
            'response' => $result['response'],
            'duration' => empty($result['duration']) ? '' : $this->convertTime($result['duration']),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成作业”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function finishHomework($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => 'http://xapi.edusoho.com/activities/homework',
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                ),
            ),
        );

        if (!empty($result)) {
            $statement['result'] = $result;
        }

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成练习”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function finishExercise($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => 'http://xapi.edusoho.com/activities/exercise',
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                ),
            ),
        );

        if (!empty($result)) {
            $statement['result'] = $result;
        }

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成考试”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function finishTestpaper($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => 'http://xapi.edusoho.com/activities/testpaper',
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                ),
            ),
        );

        if (!empty($result)) {
            $statement['result'] = $result;
        }

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“记笔记”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function writeNote($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/adb/verbs/noted',
            'display' => array(
                'zh-CN' => '记录',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['globalId']) ? 0 : $object['resource']['globalId'],
                        'name' => empty($object['resource']['filename']) ? '' : $object['resource']['filename'],
                    ),
                ),
            ),
        );
        $statement['result'] = array(
            'response' => $result['content'],
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“提问题”的学习记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function askQuestion($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/asked',
            'display' => array(
                'zh-CN' => '提问了',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['globalId']) ? 0 : $object['resource']['globalId'],
                        'name' => empty($object['resource']['filename']) ? '' : $object['resource']['filename'],
                    ),
                ),
            ),
        );
        $statement['result'] = array(
            'response' => $result['title'].'-'.htmlspecialchars_decode($result['content']),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交"观看直播"的记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function watchLive($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/acrossx/verbs/watched',
            'display' => array(
                'zh-CN' => '观看了',
                'en-US' => 'watched',
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => 'http://xapi.edusoho.com/activities/live',
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                        'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                        'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                        'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                        'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                    ),
                ),
            ),
        );

        $statement['result'] = array(
            'duration' => $this->convertTime($result['duration']),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交"搜索"的记录
     *
     * @param $actor
     * @param $object ['id' => '/cloud/search?q=单反&type=course', 'definitionType' => 'course']
     *                ['id' => '/cloud/search?q=单反&type=teacher', 'objectType' => 'Agent']
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array
     *
     * @throws ResponseException
     */
    public function searched($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/acrossx/verbs/searched',
            'display' => array(
                'zh-CN' => '搜索了',
                'en-US' => XAPIVerbs::SEARCHED,
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
            ),
        );

        $statement['result'] = array(
            'response' => $result['response'],
            'extensions' => array(
                'https://w3id.org/xapi/acrossx/extensions/type' => $this->getActivityType($result['type']),
            ),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交"登录"的记录
     *
     * @param $actor
     * @param $object
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     *
     * @return array|mixed
     */
    public function logged($actor, $object = null, $result = null, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/adl/verbs/logged-in',
            'display' => array(
                'zh-CN' => '登录了',
                'en-US' => XAPIVerbs::LOGGED_IN,
            ),
        );
        $statement['object'] = array(
            'id' => $this->auth->getAccessKey(),
            'definition' => array(
                'type' => $this->getActivityType(XAPIActivityTypes::APPLICATION),
                'name' => array(
                    $this->defaultLang => $this->options['school_name'],
                ),
            ),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    public function registered($actor, $object = null, $result = null, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/registered',
            'display' => array(
                'zh-CN' => '注册了',
                'en-US' => XAPIVerbs::REGISTERED,
            ),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    public function rated($actor, $object = null, $result = null, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://id.tincanapi.com/verb/rated',
            'display' => array(
                'zh-CN' => '评分了',
                'en-US' => XAPIVerbs::RATED,
            ),
        );

        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
            ),
        );

        $this->addCourseExtension($object, $statement);

        $statement['result'] = array(
            'score' => array(
                'raw' => $result['score']['raw'],
                'max' => $result['score']['max'],
                'min' => $result['score']['min'],
            ),
            'response' => $result['response'],
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    public function bookmarked($actor, $object = null, $result = null, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/adb/verbs/bookmarked',
            'display' => array(
                'zh-CN' => '收藏了',
                'en-US' => XAPIVerbs::BOOKMARKED,
            ),
        );

        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
            ),
        );

        $this->addCourseExtension($object, $statement);

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    public function shared($actor, $object = null, $result = null, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/shared',
            'display' => array(
                'zh-CN' => '分享了',
                'en-US' => XAPIVerbs::SHARED,
            ),
        );

        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
            ),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交"购买"的记录
     *
     * @param $actor
     * @param $object
     * @param $result
     * @param null $uuid
     * @param null $timestamp
     * @param bool $isPush
     */
    public function purchased($actor, $object, $result, $uuid = null, $timestamp = null, $isPush = true)
    {
        $statement = array();
        if (!empty($uuid)) {
            $statement['id'] = $uuid;
        }
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://activitystrea.ms/schema/1.0/purchase',
            'display' => array(
                'zh-CN' => '购买了',
                'en-US' => XAPIVerbs::PURCHASED,
            ),
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'definition' => array(
                'type' => $this->getActivityType($object['definitionType']),
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
            ),
        );

        $this->addCourseExtension($object, $statement);

        $statement['result'] = array(
            'extensions' => array(
                'http://xapi.edusoho.com/extensions/amount' => $result['amount'],
            ),
        );

        $statement['timestamp'] = $this->getTime($timestamp);

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交学习记录
     *
     * @param $statement
     *
     * @return mixed
     *
     * @throws ResponseException
     * @throws \QiQiuYun\SDK\HttpClient\ClientException
     */
    public function pushStatement($statement)
    {
        return $this->pushStatements(array($statement));
    }

    /**
     * 批量提交学习记录
     *
     * @param $statements
     *
     * @return mixed
     *
     * @throws ResponseException
     * @throws \QiQiuYun\SDK\HttpClient\ClientException
     */
    public function pushStatements($statements)
    {
        $school = array(
            'name' => $this->options['school_name'],
            'url' => $this->options['school_url'],
            'version' => $this->options['school_version'],
        );
        foreach ($statements as &$statement) {
            $statement['context'] = array(
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/school' => $school,
                ),
            );
        }

        return $this->request('POST', '/statements', $statements, array(
            'Authorization' => $this->auth->makeXAPIRequestAuthorization(),
        ));
    }

    /**
     * @param $type
     * @param $value
     *
     * @return array
     */
    public function setting($type, $value)
    {
        $setting = array(
            'setting' => $type,
            'value' => $value,
        );
        $response = $this->request('POST', '/setting', $setting, array(
            'Authorization' => $this->auth->makeXAPIRequestAuthorization(),
        ));

        return $response;
    }

    /**
     * @param $minType
     *
     * @return string
     */
    private function getActivityType($minType)
    {
        return XAPIActivityTypes::getFullName($minType);
    }

    private function addCourseExtension($object, &$statement)
    {
        if (XAPIActivityTypes::COURSE == $object['definitionType']) {
            $statement['object']['definition']['extensions'] = array(
                'http://xapi.edusoho.com/extensions/course' => array(
                    'id' => empty($object['course']['id']) ? 0 : $object['course']['id'],
                    'title' => empty($object['course']['title']) ? '' : $object['course']['title'],
                    'description' => empty($object['course']['description']) ? '' : $object['course']['description'],
                    'price' => empty($object['course']['price']) ? 0 : $object['course']['price'],
                    'tags' => empty($object['course']['tags']) ? '' : $object['course']['tags'],
                ),
            );
        }
    }

    /**
     * @param $type
     *
     * @return string
     *
     * @throws SDKException
     */
    private function getExtensionId($type)
    {
        switch ($type) {
            case 'activity': //活动,非xAPI标准
                $id = 'http://xapi.edusoho.com/extensions/activity';
                break;
            case 'course': //活动所属课程,非xAPI标准
                $id = 'http://xapi.edusoho.com/extensions/course';
                break;
            case 'duration': //遵守ISO8601标准的时间长度
                $id = 'http://id.tincanapi.com/extension/duration';
                break;
            case 'ending-point': //活动发生的终点,遵守ISO8601标准
                $id = 'http://id.tincanapi.com/extension/ending-point';
                break;
            case 'resource': //活动对应的资源,非xAPI标准
                $id = 'http://xapi.edusoho.com/extensions/resource';
                break;
            case 'school': //活动所在的网校,非xAPI标准
                $id = 'http://xapi.edusoho.com/extensions/school';
                break;
            case 'starting-point': //活动发生的起点,遵守ISO8601标准
                $id = 'http://id.tincanapi.com/extension/starting-point';
                break;
            default:
                throw new SDKException('Please input correct type');
        }

        return $id;
    }

    protected function getTime($timestamp, $format = 'iso8601')
    {
        switch ($format) {
            case 'iso8601':
                $result = $this->getIsoTime($timestamp);
                break;
            default:
                $result = $timestamp;
        }

        return $result;
    }

    protected function convertTime($time, $format = 'iso8601')
    {
        switch ($format) {
            case 'iso8601':
                $result = $this->timeToIsoDuration($time);
                break;
            default:
                $result = $time;
        }

        return $result;
    }

    protected function timeToIsoDuration($time)
    {
        $units = array(
            'Y' => 365 * 24 * 3600,
            'D' => 24 * 3600,
            'H' => 3600,
            'M' => 60,
            'S' => 1,
        );

        $str = 'P';
        $isTime = false;

        foreach ($units as $unitName => &$unit) {
            $quot = intval($time / $unit);
            $time -= $quot * $unit;
            $unit = $quot;
            if ($unit > 0) {
                if (!$isTime && in_array($unitName, array('H', 'M', 'S'))) {
                    $str .= 'T';
                    $isTime = true;
                }
                $str .= strval($unit).$unitName;
            }
        }

        return $str;
    }

    protected function getIsoTime($timestamp = null)
    {
        return empty($timestamp) ? date('c') : date('c', $timestamp);
    }

    protected function filterOptions(array $options = array())
    {
        $options = parent::filterOptions($options);
        if (empty($options['school_name'])) {
            throw new SDKException('Option `school_name` is missing.');
        }

        return $options;
    }
}
