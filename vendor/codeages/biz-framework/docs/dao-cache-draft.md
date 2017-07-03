Dao Cache
=========

```php
<?php

/**
 * @CacheStrategy("Row")
 */
class ExampleDaoImpl
{
    /**
     * @RowCache
     */
    public function getByField1($field1)
    {
        
    }
    
    /**
     * @RowCache
     */
    public function getByField2AndField3($field1, $field2)
    {

    }
    
    /**
     * @RowCache
     */
    public function getByField2AndField4($field2, $field4)
    {

    }
}
```

**query:**

before:
```
get dao:table_name:method_name:arg1,arg2...argn : dao:table_name:primary_method_name:primary_id
get dao:table_name:primary_method_name:primary_value : {object}
```

after:

set dao:table_name:primary_method_name:primary_value:rel_keys += rel_keys




**update:**

```

```

Cache File:

```php
<?php

return [
    '{dao_class_name}' => [
        'strategy' => 'row',
        'primary_query_method' => 'get',
        'update_rel_query_methods' => [
            'field1' => ['getByField1'],
            'field2' => ['getByField2AndField3', 'getByField2AndField4'],
            'field3' => ['getByField2AndField3'],
            'field4' => ['getByField2AndField4'],
        ],
        'cache_key_of_arg_index' => [
            'getByField1' => [1],
            'getByField2AndField3' => [1, 2],
            'getByField2AndField4' => [1, 2],
        ],
        'cache_key_of_field_name' => [
            'getByField1' => ['field1'],
            'getByField2AndField3' => ['field2', 'field3'],
        ]
    ],
];
```

