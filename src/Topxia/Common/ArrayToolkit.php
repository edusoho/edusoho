<?php
namespace Topxia\Common;

class ArrayToolkit
{
	public static function column(array $array, $columnName)
	{
		if (empty($array)) {
			return array();
		}
		
		$column = array();
		foreach ($array as $item) {
            if (isset($item[$columnName])) {
                $column[] = $item[$columnName];
            }
		}

        return $column;
	}

	public static function parts(array $array, array $keys)
	{
		foreach (array_keys($array) as $key) {
			if (!in_array($key, $keys)) {
				unset($array[$key]);
			}
		}
		return $array;
	}

	public static function requireds(array $array, array $keys)
	{
		foreach ($keys as $key) {
			if (!array_key_exists($key, $array)) {
				return false;
			}
		}
		return true;
	}

	public static function changes(array $before, array $after)
	{
		$changes = array('before' => array(), 'after' => array());
		foreach ($after as $key => $value) {
			if (!isset($before[$key])) {
				continue;
			}
			if ($value != $before[$key]) {
				$changes['before'][$key] = $before[$key];
				$changes['after'][$key] = $value;
			}
		}
		return $changes;
	}

    public static function group(array $array, $key)
    {
        $grouped = array();
        foreach ($array as $item) {
            if (empty($grouped[$item[$key]])) {
                $grouped[$item[$key]] = array();
            }
            $grouped[$item[$key]][] = $item;
        }

        return $grouped;
    }

    public static function index (array $array, $name)
    {
        $indexedArray = array();
        if (empty($array)) {
            return $indexedArray;
        }
        
        foreach ($array as $item) {
            if (isset($item[$name])) {
                $indexedArray[$item[$name]] = $item;
                continue;
            }
        }
        return $indexedArray;
    }

    public static function filter(array $array, array $specialValues)
    {
    	$filtered = array();
    	foreach ($specialValues as $key => $value) {
    		if (!array_key_exists($key, $array)) {
    			continue;
    		}

			if (is_array($value)) {
				$filtered[$key] = (array) $array[$key];
			} elseif (is_int($value)) {
				$filtered[$key] = (int) $array[$key];
			} elseif (is_float($value)) {
				$filtered[$key] = (float) $array[$key];
			} elseif (is_bool($value)) {
				$filtered[$key] = (bool) $array[$key];
			} else {
				$filtered[$key] = (string) $array[$key];
			}

			if (empty($filtered[$key])) {
				$filtered[$key] = $value;
			}
    	}

    	return $filtered;
    }

}