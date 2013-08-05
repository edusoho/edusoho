<?php
namespace Topxia\Service\Common;

class SqlConditionCompiler
{
    public function compile($conditions)
    {
        $wheres = $params = array();
        foreach ($conditions as $where => $param) {
            $where = $this->prepareWhere($where);
            switch ($where['op']) {
                case 'between':
                    $wheres[] = "({$where['field']} >= ? AND {$where['field']} < ?)";
                    $params = array_merge($params, array_values($param));
                    break;
                case 'in':
                    if (!is_array($param)) {
                        throw \InvalidArgumentException('IN查询的值需为数组.');
                    }
                    $marks = str_repeat('?,', count($param) - 1) . '?';
                    $wheres[] = "({$where['field']} IN ({$marks}))";
                    $params = array_merge($params, array_values($param));
                    break;
                default:
                    $wheres[] = "({$where['field']} {$where['op']} ?)";
                    $params[] = $param;
            }
        }
        return array('where' =>  join(' AND ', $wheres) , 'params' => $params);
    }

    protected function compileWhere($where, $param)
    {
        $where = $this->prepareWhere($where);
        switch ($where['op']) {
            case 'between':
                return "({$where['field']} >= ? AND < ?)";
            case 'in':
                if (!is_array($param)) {
                    throw \InvalidArgumentException('IN查询的值需为数组.');
                }
                $marks = str_repeat('?,', count($param) - 1) . '?';
                return "({$where['field']} IN ({$marks}))";
            default:
                return "({$where['field']} {$where['op']} ?)";
        }
        return $where;
    }

    protected function prepareWhere($where)
    {
        $prepared = array();
        $where = explode(':', $where, 2);
        $prepared['field'] = $where[0];
        $prepared['op'] =  empty($where[1]) ? '=' : $where[1];

        $this->validateWhere($prepared);

        return $prepared;
    }

    protected function validateWhere($rule)
    {
        $allowedOperations = array('=', '>', '<', '<>', '>=', '<=', 'between', 'like', 'in');
        if (!in_array($rule['op'], $allowedOperations)) {
                throw \DomainException("{$rule['op']} is not allowed.");
        }
        return true;
    }
}