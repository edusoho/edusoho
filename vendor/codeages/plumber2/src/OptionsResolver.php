<?php

namespace Codeages\Plumber;

class OptionsResolver
{
    /**
     * @param array $options
     *
     * @return array
     *
     * @throws PlumberException
     */
    public static function resolve(array $options)
    {
        if (!isset($options['workers'])) {
            throw new PlumberException("Option 'workers' is missing.");
        }

        if (!is_array($options['workers'])) {
            throw new PlumberException("Option 'workers' must be array");
        }

        if (!isset($options['queues'])) {
            throw new PlumberException("Option 'queues' is missing.");
        }

        if (!is_array($options['queues'])) {
            throw new PlumberException("Option 'queues' must be array");
        }

        if (!isset($options['log_path'])) {
            throw new PlumberException("Option 'log_path' is missing.");
        }

        if (!isset($options['pid_path'])) {
            throw new PlumberException("Option 'pid_path' is missing.");
        }

        foreach ($options['workers'] as $i => &$workerOptions) {
            if (!isset($workerOptions['class'])) {
                throw new PlumberException("Option 'workers[$i].class' is missing.");
            }

            if (!class_exists($workerOptions['class'])) {
                throw new PlumberException("Option 'workers[$i].class' {$workerOptions['class']} is not exist.");
            }

            if (!isset($workerOptions['queue'])) {
                throw new PlumberException("Option 'workers[$i].queue' is missing.");
            }

            if (!isset($options['queues'][$workerOptions['queue']])) {
                throw new PlumberException("Option 'workers[$i].queue' {$workerOptions['queue']} is not exist.");
            }

            if (!isset($workerOptions['topic'])) {
                throw new PlumberException("Option 'workers[$i].topic' is missing.");
            }

            if (!is_string($workerOptions['topic'])) {
                throw new PlumberException("Option 'workers[$i].topic' value type must be string.");
            }

            if (strlen($workerOptions['topic']) < 1 || strlen($workerOptions['topic']) > 64) {
                throw new PlumberException("Option 'workers[$i].topic' value length must be between 1 ~ 64");
            }

            $workerOptions['num'] = isset($workerOptions['num']) ? $workerOptions['num'] : 1;

            if (!is_int($workerOptions['num'])) {
                throw new PlumberException("Option 'workers[$i].num' value type must be int.");
            }

            if ($workerOptions['num'] < 1) {
                throw new PlumberException("Option 'workers[$i].num' value must be grate than 1.");
            }

            unset($workerOptions);
        }

        foreach ($options['queues'] as $i => $queueOptions) {
            if (!isset($queueOptions['type'])) {
                throw new PlumberException("Option 'queues[$i].type' is missing.");
            }

            if (!in_array($queueOptions['type'], ['redis', 'beanstalk'])) {
                throw new PlumberException("Option 'queues[$i].type' value must be in [redis, beanstalk].");
            }

            if ('redis' == $queueOptions['type']) {
                if (!isset($queueOptions['host'])) {
                    throw new PlumberException("Option 'queues[$i].host' is missing.");
                }

                if (!isset($queueOptions['port'])) {
                    throw new PlumberException("Option 'queues[$i].port' is missing.");
                }
            }
        }

        return $options;
    }
}
