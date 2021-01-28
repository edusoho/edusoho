<?php

namespace Codeages\Biz\Framework\Dao;

class FieldSerializer implements SerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($method, $value)
    {
        $methods = array(
            'json' => function ($value) {
                if (empty($value)) {
                    return '';
                }

                return json_encode($value);
            },
            'mysql_json' => function ($value) {
                if (empty($value)) {
                    return '{}';
                }

                return json_encode($value);
            },
            'delimiter' => function ($value) {
                if (empty($value)) {
                    return '';
                }

                return '|'.implode('|', $value).'|';
            },
            'php' => function ($value) {
                return serialize($value);
            },
        );

        return $methods[$method]($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($method, $value)
    {
        $methods = array(
            'json' => function ($value) {
                if (empty($value)) {
                    return array();
                }

                return json_decode($value, true);
            },
            'mysql_json' => function ($value) {
                if (empty($value)) {
                    return array();
                }

                return json_decode($value, true);
            },
            'delimiter' => function ($value) {
                if (empty($value)) {
                    return array();
                }

                return explode('|', trim($value, '|'));
            },
            'php' => function ($value) {
                return unserialize($value);
            },
        );

        return $methods[$method]($value);
    }
}
