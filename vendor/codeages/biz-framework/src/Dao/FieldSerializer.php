<?php

namespace Codeages\Biz\Framework\Dao;

class FieldSerializer implements SerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($name, $value)
    {
        $methods = array(
            'json' => function ($value) {
                if (empty($value)) {
                    return '';
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

        return $methods[$name]($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($name, $value)
    {
        $methods = array(
            'json' => function ($value) {
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

        return $methods[$name]($value);
    }
}
