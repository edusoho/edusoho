<?php

function filter($data, $type)
{
	$class = 'Topxia\\Api\\Filter\\' .  ucfirst($type) . 'Filter';
	$filter = new $class();
	return $filter->filter($data);
}