<?php
namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\Exception\ResponseException;
use QiQiuYun\SDK\Exception\SDKException;

class XAPIService extends BaseService
{
    protected $baseUri = 'http://xapi.qiqiuyun.net';

    protected $defaultLang = 'zh-CN';

    /**
     * 提交“观看视频”的学习记录
     * @param $actor
     * @param $object
     * @param $result
     * @param bool $isPush
     * @return array|mixed
     */
    public function watchVideo($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/acrossx/verbs/watched',
            'display' => array(
                'zh-CN' => '观看了',
                'en-US' => 'watched'
            )
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'https://w3id.org/xapi/acrossx/activities/video',
                'name' => array(
                    $this->defaultLang => $object['name'],
                ),
                'extensions' => array (
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description'],
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['id']) ? 0 : $object['resource']['id'],
                        'name' => empty($object['resource']['name']) ? '' : $object['resource']['name']
                    )
                )
            )
        );

        $statement['result'] = array(
            'duration' => $this->convertTime($result['duration']),
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成任务”的学习记录
     *
     * @return
     */
    public function finishActivity($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了',
                'en-US' => 'completed'
            )
        );

        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'https://w3id.org/xapi/acrossx/activities/video',
                'name' => array(
                    $this->defaultLang => $object['name']
                ),
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description']

                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['id']) ? 0 : $object['resource']['id'],
                        'name' => empty($object['resource']['name']) ? '' : $object['resource']['name']
                    )
                )
            )
        );

        $statement['result'] = array(
            'success' => true
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成任务的弹题”的学习记录
     *
     * @return
     */
    public function finishActivityQuestion($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/answered',
            'display' => array(
                'zh-CN' => '回答'
            )
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'http://adlnet.gov/expapi/activities/interaction',
                'interactionType' => $object['type'],
                'description' => array(
                    $this->defaultLang => $object['stem'],
                ),
                'correctResponsesPattern' => $object['answer'],
                'choices' => $object['choices'],
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description'],
                    ),
                    'http://xapi.edusoho.com/extensions/activity' => array(
                        'id' => $object['activity']['id'],
                        'title' => $object['activity']['title']
                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['id']) ? 0 : $object['resource']['id'],
                        'name' => empty($object['resource']['name']) ? '' : $object['resource']['name']
                    )
                )
            )
        );

        $statement['result'] = array(
            'score' => $result['score'],
            'success' => true,
            'response' => $result['response'],
            'duration' => $this->convertTime($result['duration'])
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }
    
    /**
     * 提交“完成作业”的学习记录
     *
     * @return
     */
    public function finishHomework($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了'
            )
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'http://xapi.edusoho.com/activities/homework',
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description'],
                    ),
                )
            ),
        );
        $statement['result'] = array(
            'success' => true
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成练习”的学习记录
     *
     * @return
     */
    public function finishExercise($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了'
            )
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'http://xapi.edusoho.com/activities/testpaper',
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description'],
                    ),
                )
            ),
        );
        $statement['result'] = array(
            'success' => true
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“完成考试”的学习记录
     *
     * @return
     */
    public function finishTestpaper($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/completed',
            'display' => array(
                'zh-CN' => '完成了'
            )
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'http://xapi.edusoho.com/activities/examination',
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description'],
                    ),
                )
            ),
        );
        $statement['result'] = array(
            'success' => true,
            'score' => $result['score']
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“记笔记”的学习记录
     *
     * @return
     */
    public function writeNote($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'https://w3id.org/xapi/adb/verbs/noted',
            'display' => array(
                'zh-CN' => '记录'
            )
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'https://w3id.org/xapi/acrossx/activities/video',
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description']

                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['id']) ? 0 : $object['resource']['id'],
                        'name' => empty($object['resource']['name']) ? '' : $object['resource']['name']
                    )
                )
            ),
        );
        $statement['result'] = array(
            'response' => $result['content']
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交“提问题”的学习记录
     *
     * @return
     */
    public function askQuestion($actor, $object, $result, $isPush = true)
    {
        $statement = array();
        $statement['actor'] = $actor;
        $statement['verb'] = array(
            'id' => 'http://adlnet.gov/expapi/verbs/asked',
            'display' => array(
                'zh-CN' => '提问了'
            )
        );
        $statement['object'] = array(
            'id' => $object['id'],
            'defination' => array(
                'type' => 'https://w3id.org/xapi/acrossx/activities/video',
                'extensions' => array(
                    'http://xapi.edusoho.com/extensions/course' => array(
                        'id' => $object['course']['id'],
                        'title' => $object['course']['title'],
                        'description' => $object['course']['description']

                    ),
                    'http://xapi.edusoho.com/extensions/resource' => array(
                        'id' => empty($object['resource']['id']) ? 0 : $object['resource']['id'],
                        'name' => empty($object['resource']['name']) ? '' : $object['resource']['name']
                    )
                )
            )
        );
        $statement['result'] = array(
            'response' => $result['title']
        );

        return $isPush ? $this->pushStatement($statement) : $statement;
    }

    /**
     * 提交学习记录
     * @param $statement
     * @return mixed
     * @throws ResponseException
     */
    public function pushStatement($statement)
    {
        $statement['context'] = array(
            'extensions' => array (
                'http://xapi.edusoho.com/extensions/school' => $this->options['school'],
            )
        );

        $statement['timestamp'] = $this->getTime(null);


        $rawResponse = $this->client->request('POST', '/statements', array(
            'json' => array($statement),
            'headers' => array(
                'Authorization' => 'Signature '.$this->makeSignature(),
            )
        ));

        $response = json_decode($rawResponse->getBody(), true);
        if (isset($response['error'])) {
            throw new ResponseException($rawResponse);
        }

        return $statement;
    }

    private function getActivityType($minType)
    {
        switch ($minType) {
            case 'audio':
                $activityType = 'http://activitystrea.ms/schema/1.0/audio';
                break;
            case 'course':
                $activityType = 'http://adlnet.gov/expapi/activities/course';
                break;
            case 'document':
                $activityType = 'https://w3id.org/xapi/acrossx/activities/document';
                break;
            case 'examination':
                $activityType = 'http://xapi.edusoho.com/activities/examination';
                break;
            case 'homework':
                $activityType = 'http://xapi.edusoho.com/activities/homework';
                break;
            case 'interaction':
                $activityType = 'http://adlnet.gov/expapi/activities/interaction';
                break;
            case 'live':
                $activityType = 'http://xapi.edusoho.com/activities/live';
                break;
            case 'testpaper':
                $activityType = 'http://xapi.edusoho.com/activities/testpaper';
                break;
            case 'video':
                $activityType = 'https://w3id.org/xapi/acrossx/activities/video';
                break;
            default:
                throw new SDKException('Please input correct type');
        }

        return $activityType;

    }

    private function getExtensionId($type)
    {
        switch ($type) {
            case 'activity':
                $id = 'http://xapi.edusoho.com/extensions/activity';
                break;
            case 'course':
                $id = 'http://xapi.edusoho.com/extensions/course';
                break;
            case 'duration':
                $id = 'http://id.tincanapi.com/extension/duration';
                break;
            case 'ending-point':
                $id = 'http://id.tincanapi.com/extension/ending-point';
                break;
            case 'resource':
                $id = 'http://xapi.edusoho.com/extensions/resource';
                break;
            case 'school':
                $id = 'http://xapi.edusoho.com/extensions/school';
                break;
            case 'starting-point':
                $id = 'http://id.tincanapi.com/extension/starting-point';
                break;
            default:
                throw new SDKException('Please input correct type');
        }

        return $id;
    }

    protected function makeSignature()
    {
        $deadline = strtotime(date('Y-m-d H:0:0', strtotime('+2 hours')));
        $signingText = $this->auth->getAccessKey()."\n".$deadline;
        $signingText = $this->auth->getAccessKey().':'.$deadline.':'.$this->auth->sign($signingText);
        return $signingText;
    }

    protected function getTime($timestamp, $format = 'iso8601')
    {
        switch ($format) {
            case 'iso8601':
                $result = $this->get_iso8601_time($timestamp);
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
                $result = $this->time_to_iso8601_duration($time);
                break;
            default:
                $result = $time;
        }

        return $result;
    }

    protected function time_to_iso8601_duration($time)
    {
        $units = array(
            "Y" => 365*24*3600,
            "D" => 24*3600,
            "H" => 3600,
            "M" => 60,
            "S" => 1,
        );

        $str = "P";
        $isTime = false;

        foreach ($units as $unitName => &$unit) {
            $quot  = intval($time / $unit);
            $time -= $quot * $unit;
            $unit  = $quot;
            if ($unit > 0) {
                if (!$isTime && in_array($unitName, array("H", "M", "S"))) {
                    $str .= "T";
                    $isTime = true;
                }
                $str .= strval($unit) . $unitName;
            }
        }

        return $str;
    }

    protected function get_iso8601_time($timestamp = null)
    {
        return empty($timestamp) ? date('c') : date('c', $timestamp);
    }
}
