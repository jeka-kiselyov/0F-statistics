<?php

 class statistics_test extends PHPUnit_Framework_0F
 {
    public function test_creation()
	{
		$statistic = $this->statistics->add('Test');
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('total', $statistic->type); // default statistic class is total

		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		$statistic = $this->statistics->add('Test increment', 'increment');	// create increment type entity
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('increment', $statistic->type); // check type

		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		$statistic = $this->statistics->add('Test average', 'average');	// create average type entity
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('average', $statistic->type); // check type
		
		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		$statistic = $this->statistics->add('Test items', 'items');	// create items type entity
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('Test items', $statistic->name); // check name also, forgot about it
		$this->assertEquals('items', $statistic->type); // check type
		
		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

	}

	public function test_total()
	{
		$statistic = $this->statistics->add('Test total');
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('total', $statistic->type); // default statistic class is total

		$statistic->set(2); // 2 - starting value;
		$this->assertEquals('2', $statistic->get_value()); // current value == 2

		$last_updated = $statistic->get_last_value_datetime();
		$this->assertGreaterThan(time() - 2, $last_updated); // should be last updated in last second

		$statistic->set(5); // updated value = 5
		$this->assertEquals('5', $statistic->get_value()); // current value == 5

		$all_period_values = $statistic->get_period_values();
		$this->assertEquals(2, count($all_period_values)); // two values items. 2 and 5

		$found1 = false;
		$found2 = false;
		foreach ($all_period_values as $period_value) 
		{
			$this->assertInstanceOf('statistics_value', $period_value); // created entity is instance of statistic_value class
			if ($period_value->value == '2')
				$found1 = true;
			elseif ($period_value->value == '5')
				$found2 = true;
		}

		$this->assertTrue($found1);        // 2 is found
		$this->assertTrue($found2);        // 5 is found

		$statistic_id = $statistic->id;

		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		/////// check that period values also removed
		$items = new Collection('statistics_value', "SELECT * FROM statistics_values WHERE statistic_id = '".(int)$statistic_id."' ");
		$this->assertEquals(0, count($items)); // all period values should be removed
	}

	public function test_increment()
	{
		$statistic = $this->statistics->add('Test increment', 'increment'); /// !!!! different type
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('increment', $statistic->type); // increment

		$statistic->set(2); // 2 - starting value;
		$this->assertEquals('2', $statistic->get_value()); // current value == 2

		$last_updated = $statistic->get_last_value_datetime();
		$this->assertGreaterThan(time() - 2, $last_updated); // should be last updated in last second

		$statistic->set(5); // updated value = 5
		$this->assertEquals('5', $statistic->get_value()); // current value == 5

		$all_period_values = $statistic->get_period_values();
		$this->assertEquals(2, count($all_period_values)); // two values items. 2 and !!!! 3 as it's increment type, not total

		$found1 = false;
		$found2 = false;
		$val1 = false;
		$val2 = false;
		foreach ($all_period_values as $period_value) 
		{
			$this->assertInstanceOf('statistics_value', $period_value); // created entity is instance of statistic_value class
			if ($period_value->value == '2')
			{
				$val1 = $period_value;
				$found1 = true;
			}
			elseif ($period_value->value == '3') //////////////////////// !!!!!!!!!!!!  3
			{
				$found2 = true;
				$val2 = $period_value;
			}	
		}

		$this->assertTrue($found1);        // 2 is found
		$this->assertTrue($found2);        // 3 is found !!!!!!!!!!!!!!!!!

		$statistic_id = $statistic->id;

		//// try to combine values for increment
		$combined = $statistic->get_combined($val1, $val2);
		$this->assertInstanceOf('statistics_value', $combined); // entity is instance of statistic_value class
		$this->assertEquals(5, $combined->value); // 2 + 3
		$this->assertEquals($statistic_id, $combined->statistic_id); // already assigned to statistic, ready to save!

		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		/////// check that period values also removed
		$items = new Collection('statistics_value', "SELECT * FROM statistics_values WHERE statistic_id = '".(int)$statistic_id."' ");
		$this->assertEquals(0, count($items)); // all period values should be removed

	}

	public function test_average()
	{
		$statistic = $this->statistics->add('Test average', 'average'); /// !!!! different type
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('average', $statistic->type); // average

		////// Average has additional parameter - count of items used for calculation. This is using later for combining values for few periods
		$statistic->set(0.8, 5); // 0.8 - starting value, calculated by 5 items
		$this->assertEquals('0.8', $statistic->get_value()); // current value == 0.8

		$last_updated = $statistic->get_last_value_datetime();
		$this->assertGreaterThan(time() - 2, $last_updated); // should be last updated in last second

		$statistic->set(0.1, 2); // updated value = 0.1, calculated by 2 items
		$this->assertEquals('0.1', $statistic->get_value()); // current value == 0.1

		$all_period_values = $statistic->get_period_values();
		$this->assertEquals(2, count($all_period_values)); // two values items. 0.8 and 0.1

		$found1 = false;
		$found2 = false;
		$val1 = false;
		$val2 = false;
		foreach ($all_period_values as $period_value) 
		{
			$this->assertInstanceOf('statistics_value', $period_value); // created entity is instance of statistic_value class
			if ($period_value->value == '0.8' && $period_value->count == 5)
			{
				$found1 = true;

				//// now lets move one statistic period somewhere to past. We need this to check how periods are combined lil later.
				$time_in_past = $period_value->datetime - 3600;
				$period_value->datetime = $time_in_past;
				$period_value->save();
				$val1 = $period_value;
			}
			elseif ($period_value->value == '0.1' && $period_value->count == 2)
			{
				$found2 = true;
				$val2 = $period_value;
			}
		}

		$this->assertTrue($found1);        // 0.8 is found
		$this->assertTrue($found2);        // 0.1 is found 

		$statistic_id = $statistic->id;

		//// try to combine values for average
		$combined = $statistic->get_combined($val1, $val2);
		$this->assertInstanceOf('statistics_value', $combined); // entity is instance of statistic_value class
		$this->assertEquals(0.6, $combined->value); // average of 5 * 0.8 and 2 * 0.1 is 0.6
		/// pick most recent datetime
		if ($val2->datetime > $val1->datetime)
		{
			$most_recent = $val2->datetime;
			$this->assertGreaterThan($val1->datetime, $most_recent); // check that we moved it to past correctly
		} else {
			$most_recent = $val1->datetime; 
			$this->assertGreaterThan($val2->datetime, $most_recent); // check that we moved it to past correctly
		}
		$this->assertEquals($most_recent , $combined->datetime); // comebined datetime should be datetime of most recent one
		$this->assertEquals($statistic_id, $combined->statistic_id); // already assigned to statistic, ready to save!


		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		/////// check that period values also removed
		$items = new Collection('statistics_value', "SELECT * FROM statistics_values WHERE statistic_id = '".(int)$statistic_id."' ");
		$this->assertEquals(0, count($items)); // all period values should be removed

	}

	public function test_items()
	{
		$statistic = $this->statistics->add('Test items', 'items'); /// !!!! different type
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('items', $statistic->type); // items

		$items = array();
		$items[] = 'item1';
		$items[] = 'item2';
		$items[] = 'item3';

		$statistic->set($items);

		$values = $statistic->get_value();

		$this->assertTrue(count($values) == 3);
		foreach ($values as $value) 
		{
			/// all items should be added
			$this->assertTrue(isset($value['value']) && isset($value['count']));  /// count = 1 by default, if we didn't pass it, and we did not.
			$this->assertTrue($value['count'] == 1);
			$this->assertTrue(in_array($value['value'], $items));
		}

		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		///////// Add it again and try with count != default (1)
		$statistic = $this->statistics->add('Test items', 'items'); /// !!!! different type
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('items', $statistic->type); // items

		$items = array();
		$items['item1'] = array('value'=>'item1', 'count'=>3); /// Array keys are not important here. I've added it just to check it easier 10 lines below
		$items['item2'] = array('value'=>'item2', 'count'=>4);
		$items['item3'] = array('value'=>'item3', 'count'=>5);
		$items['item4'] = array('value'=>'item4', 'count'=>2);

		$statistic->set($items);

		$values = $statistic->get_value();

		$this->assertTrue(count($values) == 4);
		foreach ($values as $value) 
		{
			/// all items should be added
			$this->assertTrue(isset($value['value']) && isset($value['count']));  /// count = 1 by default, if we didn't pass it, and we did not.
			$this->assertTrue($value['count'] > 1);
			$this->assertTrue(isset($items[$value['value']]));
		}

		//// Add few more with different timestamp
		$items = array();
		$items['item5'] = array('value'=>'item5', 'count'=>1);
		$items['item6'] = array('value'=>'item6', 'count'=>4);
		$items['item7'] = array('value'=>'item7', 'count'=>2);

		$older_timestamp = time() - 24*60*60;

		$statistic->set($items, null, $older_timestamp);

		/// Now there's an important thing. We've added value to the past, so current one should be the one we've added earlier ( with 4 items )
		$values = $statistic->get_value();
		$this->assertTrue(count($values) == 4, 'Adding value to the past does not work');



		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

	}

	public function test_archiving_increment()
	{
		$statistic = $this->statistics->add('Test archiving', 'increment');
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('increment', $statistic->type); // increment

		//// create 100 values
		$count_of_test_value = 10; /// x3
		$total_value = 0;
		$added_count = 0;

		$current_time = 0;

		for ($i = 0; $i < $count_of_test_value; $i++)
		{
			$datetime = time() - 60*60*64*24 - 24*60*60*rand(0,300); /// move it somewhere to past
			$value = rand(0,10);
			$is_added = $statistic->set($total_value+$value, 1, $datetime); /// can be not added, if timestamp < last added value timestamp

			if ($is_added)
				$added_count++;
			if ($is_added)
				$total_value+=$value;
		}

		for ($i = 0; $i < $count_of_test_value; $i++)
		{
			$datetime = time() - 60*60*48 - 60*60*rand(0,300); /// move it somewhere to past
			$value = rand(0,10);
			$is_added = $statistic->set($total_value+$value, 1, $datetime);

			if ($is_added)
				$added_count++;
			if ($is_added)
				$total_value+=$value;
		}

		for ($i = 0; $i < $count_of_test_value; $i++)
		{
			$datetime = time() - 60*60*2 - 60*rand(0,300); /// move it somewhere to past
			$value = rand(0,10);
			$is_added =  $statistic->set($total_value+$value, 1, $datetime);

			if ($is_added)
				$added_count++;
			if ($is_added)
				$total_value+=$value;
		}

		$all_period_values = $statistic->get_period_values();
		$this->assertEquals($added_count, count($all_period_values)); // Count of items

		$total_value_to_check = 0;
		foreach ($all_period_values as $value) 
		{
			$total_value_to_check+=$value->value;
		}
		$this->assertEquals($total_value_to_check, $total_value); 	// As you understand, this simple check works only for `increment` type

		$cur_time = time();
		$statistic->archive();

		$all_period_values = $statistic->get_period_values();

		/// Now check that there're no more than one item older 2 hours in every hour.
		$periods = array();
		$time_to_start_archive_by_hour = $cur_time - 2*60*60;
		$time_to_start_archive_by_day = $cur_time - 48*60*60;
		$time_to_start_archive_by_month = $cur_time - 62*24*60*60;
		$total_value_to_check = 0;

		foreach ($all_period_values as $value) 
		{
			$total_value_to_check+=$value->value;
			$this->assertInstanceOf('statistics_value', $value); // entity is instance of statistic_value class
			if ($value->datetime < $time_to_start_archive_by_hour && $value->datetime >= $time_to_start_archive_by_day)
			{
				$period_start = $value->datetime - ($value->datetime % (60*60));
				$period_end = $period_start + 60*60 - 1;
				$periods[''.$period_start.'-'.$period_end][] = $value;
			}
			elseif ($value->datetime < $time_to_start_archive_by_day && $value->datetime >= $time_to_start_archive_by_month)
			{
				$period_start = $value->datetime - ($value->datetime % (24*60*60));
				$period_end = $period_start + 24*60*60 - 1;
				$periods[''.$period_start.'-'.$period_end][] = $value;
			}
			elseif ($value->datetime < $time_to_start_archive_by_month)
			{
				$period_start = mktime(0, 0, 0, date('n',$value->datetime), 1, date('Y',$value->datetime));
				$period_end = mktime(23, 59, 59, date('n',$value->datetime), date('t',$value->datetime), date('Y',$value->datetime));
				$periods[''.$period_start.'-'.$period_end][] = $value;				
			}
		}

		$this->assertEquals($total_value_to_check, $total_value); 	// As you understand, this simple check works only for `increment` type
		foreach ($periods as $period) 
		{
			$this->assertTrue(count($period) == 1); 
		}

		$statistic_id = $statistic->id;
		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		/////// check that period values also removed
		$items = new Collection('statistics_value', "SELECT * FROM statistics_values WHERE statistic_id = '".(int)$statistic_id."' ");
		$this->assertEquals(0, count($items)); // all period values should be removed
	}

	public function test_archiving_total()
	{
		$statistic = $this->statistics->add('Test archiving total', 'total');
		$this->assertInstanceOf('statistic', $statistic); // created entity is instance of statistic class
		$this->assertGreaterThan(0, (int)$statistic->id); // saved to database. Id is integer
		$this->assertEquals('total', $statistic->type); // increment

		//// create 100 values
		$count_of_test_value = 10; /// x3
		$total_value = 0;

		for ($i = 0; $i < $count_of_test_value; $i++)
		{
			$datetime = time() - 60*60*2 - 60*rand(0,300); /// move it somewhere to past
			$value = rand(0,10);
			$total_value+=$value;
			$statistic->set($total_value, 1, $datetime);

			$datetime = time() - 60*60*48 - 60*60*rand(0,300); /// move it somewhere to past
			$value = rand(0,10);
			$total_value+=$value;
			$statistic->set($total_value, 1, $datetime);

			$datetime = time() - 60*60*64*24 - 24*60*60*rand(0,300); /// move it somewhere to past
			$value = rand(0,10);
			$total_value+=$value;
			$statistic->set($total_value, 1, $datetime);
		}

		$all_period_values = $statistic->get_period_values();
		$this->assertEquals($count_of_test_value*3, count($all_period_values)); // Count of items

		$cur_time = time();

		$periods_values = array();
		$time_to_start_archive_by_hour = $cur_time - 2*60*60;
		$time_to_start_archive_by_day = $cur_time - 48*60*60;
		$time_to_start_archive_by_month = $cur_time - 62*24*60*60;

		foreach ($all_period_values as $value) 
		{
			$period_start = false;
			$period_end = false;
			if ($value->datetime < $time_to_start_archive_by_hour && $value->datetime >= $time_to_start_archive_by_day)
			{
				$period_start = $value->datetime - ($value->datetime % (60*60));
				$period_end = $period_start + 60*60 - 1;
			}
			elseif ($value->datetime < $time_to_start_archive_by_day && $value->datetime >= $time_to_start_archive_by_month)
			{
				$period_start = $value->datetime - ($value->datetime % (24*60*60));
				$period_end = $period_start + 24*60*60 - 1;
			}
			elseif ($value->datetime < $time_to_start_archive_by_month)
			{
				$period_start = mktime(0, 0, 0, date('n',$value->datetime), 1, date('Y',$value->datetime));
				$period_end = mktime(23, 59, 59, date('n',$value->datetime), date('t',$value->datetime), date('Y',$value->datetime));
			}

			if ($period_start && $period_end)
			{
				if (!isset($periods_values[''.$period_start.'-'.$period_end]))
					$periods_values[''.$period_start.'-'.$period_end] = $value->value;
				else
				if ($value->value > $periods_values[''.$period_start.'-'.$period_end])
					$periods_values[''.$period_start.'-'.$period_end] = $value->value;
			}
		}

		$statistic->archive();

		$all_period_values = $statistic->get_period_values();

		$periods_values_to_check = array();

		foreach ($all_period_values as $value) 
		{
			$period_start = false;
			$period_end = false;
			if ($value->datetime < $time_to_start_archive_by_hour && $value->datetime >= $time_to_start_archive_by_day)
			{
				$period_start = $value->datetime - ($value->datetime % (60*60));
				$period_end = $period_start + 60*60 - 1;
			}
			elseif ($value->datetime < $time_to_start_archive_by_day && $value->datetime >= $time_to_start_archive_by_month)
			{
				$period_start = $value->datetime - ($value->datetime % (24*60*60));
				$period_end = $period_start + 24*60*60 - 1;
			}
			elseif ($value->datetime < $time_to_start_archive_by_month)
			{
				$period_start = mktime(0, 0, 0, date('n',$value->datetime), 1, date('Y',$value->datetime));
				$period_end = mktime(23, 59, 59, date('n',$value->datetime), date('t',$value->datetime), date('Y',$value->datetime));
			}

			if ($period_start && $period_end)
			{
				if (!isset($periods_values_to_check[''.$period_start.'-'.$period_end]))
					$periods_values_to_check[''.$period_start.'-'.$period_end] = $value->value;
				else
				if ($value->value > $periods_values_to_check[''.$period_start.'-'.$period_end])
					$periods_values_to_check[''.$period_start.'-'.$period_end] = $value->value;

				$periods[''.$period_start.'-'.$period_end][] = $value;				
			}
		}

		foreach ($periods_values as $key => $value)  /// now checking total type. Only max value should be present for every archiving period
		{
			$this->assertTrue(isset($periods_values_to_check[$key])); 
			$this->assertTrue($periods_values_to_check[$key] == $periods_values[$key]); 
		}

		foreach ($periods as $period) 
		{
			$this->assertTrue(count($period) == 1); 
		}

		$statistic_id = $statistic->id;
		$removed = $statistic->delete();
		$this->assertTrue($removed);        // remove function should return true
		$this->assertFalse($statistic->id); // when removed, id == false

		/////// check that period values also removed
		$items = new Collection('statistics_value', "SELECT * FROM statistics_values WHERE statistic_id = '".(int)$statistic_id."' ");
		$this->assertEquals(0, count($items)); // all period values should be removed
	}
 }
