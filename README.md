0F-statistics
=============

Module for 0F 

Installation
----

Copy it over fresh 0F install and add lines below to schema file:

```php

    $schema['statistics'] = array();
	$schema['statistics']['engine'] = 'InnoDB';
	$schema['statistics']['charset'] = 'utf8_general_ci';

	$schema['statistics']['fields'] = array(
		'id'                   => array('type'=>"INTEGER", 'primaryKey'=>true, 'autoIncrement'=>true),
		'name'                 => array('type'=>"STRING"),
		'type'                 => array('type'=>"ENUM('total','average','items','increment')"),
		'current_value'        => array('type'=>"STRING"),
		'most_recent_datetime' => array('type'=>"INTEGER", 'defaultValue'=>"0")
	);

	$schema['statistics_values'] = array();
	$schema['statistics_values']['engine'] = 'InnoDB';
	$schema['statistics_values']['charset'] = 'utf8_general_ci';

	$schema['statistics_values']['fields'] = array(
		'id'           => array('type'=>"INTEGER", 'primaryKey'=>true, 'autoIncrement'=>true),
		'statistic_id' => array('type'=>"INTEGER(5)"),
		'datetime'     => array('type'=>"INTEGER"),
		'value'        => array('type'=>"STRING"),
		'count'        => array('type'=>"INTEGER", 'defaultValue'=>"0")
	);
```

##### Check other things in PHPUnit test file (app/tests/php/statistics_test.php)

License
----

MIT


**Free Software, Hell Yeah!**
