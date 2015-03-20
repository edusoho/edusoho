<?php
namespace Topxia\Service\Common;

class FieldSerializer 
{
    private $cachedAlgorithm;

    public function serialize(array &$fields, array $serializeFields)
    {
        if (empty($fields) or empty($serializeFields)) {
            return $fields;
        }

        foreach ($serializeFields as $key => $algorithm) {
            if (!array_key_exists($key, $fields)) {
                continue;
            }

            $fields[$key] = $this->getSerializeAlgorithm($algorithm)->serialize($fields[$key]);
        }

        return $fields;
    }

    public function unserialize(array &$fields, array $serializeFields)
    {
        if (empty($fields) or empty($serializeFields)) {
            return $fields;
        }

        foreach ($serializeFields as $key => $algorithm) {
            if (!array_key_exists($key, $fields)) {
                continue;
            }

            $fields[$key] = $this->getSerializeAlgorithm($algorithm)->unserialize($fields[$key]);
        }

        return $fields;
    }

    public function unserializes(array $fieldsList, array $serializeFields)
    {
        foreach ($fieldsList as &$fields) {
            $this->unserialize($fields, $serializeFields);
            unset($fields);
        }
        return $fieldsList;
    }

    private function getSerializeAlgorithm($algorithm)
    {
        if (!isset($this->cachedAlgorithm[$algorithm])) {
            $class = __NAMESPACE__ . '\\' . ucfirst($algorithm) . 'SerializeAlgorithm';
            if (!class_exists($class)) {
                throw new \RuntimeException("serialize algorithm {$algorithm} is not exist.");
            }

            $this->cachedAlgorithm[$algorithm] = new $class();
        }

        return $this->cachedAlgorithm[$algorithm];
    }
}

interface SerializeAlgorithm
{
    public function serialize($value);

    public function unserialize($value);
}

class JsonSerializeAlgorithm implements SerializeAlgorithm
{
    public function serialize($value)
    {
        return json_encode($value);
    }

    public function unserialize($value)
    {
        return json_decode($value, true);
    }
}

class PhpserializeSerializeAlgorithm implements SerializeAlgorithm
{
    public function serialize($value)
    {
        return serialize($value);
    }

    public function unserialize($value)
    {
        return unserialize($value);
    }
}