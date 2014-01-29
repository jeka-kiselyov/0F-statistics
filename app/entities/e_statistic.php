<?php

 class statistic extends entity_base
 {
	public function save()
	{
		if (!isset($this->fields['type']) || !$this->fields['type'])
			$this->fields['type'] = 'total';

		$success = parent::save();
		return $success;
	}

	public function delete()
	{
		$this_id = $this->id;
		$success = parent::delete();
		if ($success)
		{
			$this->db->delete('statistics_values', 'statistic_id = ?', array($this_id));
			return true;
		}
		return false;
	}

	public function get_value()
	{
		if ($this->type != 'items')
			return $this->current_value;
		else 
		{
			$items = new collection('statistics_value', "SELECT * FROM statistics_values WHERE statistic_id = '".(int)$this->id."' AND `datetime` = '".(int)$this->current_value."' ");
			$ret = array();
			foreach ($items as $item) 
				$ret[] = array('count'=>$item->count, 'value'=>$item->value);

			return $ret;
		}
	}

	public function get_value_for_the_time($datetime, $interval = false)
	{
		if (!$interval)
			$interval = 24*60*60;

		$interval = (int)$interval;
		$datetime = (int)$datetime;
		$q = "SELECT * FROM statistics_values WHERE 
			statistic_id = '".(int)$this->id."' AND `datetime` >= '".($datetime - $interval)."' AND `datetime` <= '".($datetime + $interval)."' ORDER BY ABS($datetime - `datetime`) DESC";
		$items = new collection('statistics_value', $q);
		if ($items && count($items))
		{
			foreach ($items as $item) 
			{
				if ($this->type != 'items')
				{
					return $item['value'];
				} else {
					/// for items, return all with this datetime;
					$items = new collection('statistics_value', "SELECT * FROM statistics_values WHERE statistic_id = '".(int)$this->id."' AND `datetime` = '".(int)$item['datetime']."' ORDER BY `count` DESC");
					$ret = array();
					foreach ($items as $item) 
						$ret[] = array('count'=>$item->count, 'value'=>$item->value);

					return $ret;
				}

			}
		}


	}

	public function get_period_values()
	{
		$values = $this->statistic_statistics_values;
		return $values;
	}

	public function set($value, $set_count = 1, $timestamp = false)
	{
		if ($this->type == 'total')
			return $this->set_total_value($value, $timestamp);
		elseif ($this->type == 'increment')
			return $this->set_increment_value($value, $timestamp);
		elseif ($this->type == 'average')
			return $this->set_average_value($value, $set_count, $timestamp);
		elseif ($this->type == 'items')
			return $this->set_items_value($value, $timestamp);
	}

	function get_last_value_datetime()
	{
		$values = $this->statistic_statistics_values;
		$values->order_by('datetime', 'DESC');
		$values->limit(0,1);
		if (count($values) > 0)
			return $values->get_entity_by_index(0)->datetime;
		return false;
	}

	function set_period_value($value, $count = 1, $timestamp = false)
	{
		$statistics_value = new statistics_value;
		
		if (!$timestamp)
			$statistics_value->datetime = time();
		else
			$statistics_value->datetime = (int)$timestamp;

		$statistics_value->statistic_id = $this->id;
		$statistics_value->count = (int)$count;
		$statistics_value->value = $value;

		$success = $statistics_value->save();

		if ($success)
		{
			$this->statistic_statistics_values->add_entity($statistics_value);
			return true;
		}
		return false;
	}

	function set_increment_value($value, $timestamp = false)
	{
		if (!$timestamp)
			$timestamp = time();
		
		if ($timestamp >= $this->most_recent_datetime)
		{
			$period_increment_value = $value - $this->current_value;
			$this->set_period_value($period_increment_value, 1, $timestamp);
			$this->current_value = $value;
			$this->most_recent_datetime = $timestamp;
		} else
		{
			//trigger_error("Cannot add value before already added one for increment type", E_USER_NOTICE);
			return false;
		}

		return $this->save();
	}

	function set_total_value($value, $timestamp = false)
	{
		if (!$timestamp)
			$timestamp = time();

		$this->set_period_value($value, 1, $timestamp);
		if ($timestamp >= $this->most_recent_datetime)
		{
			$this->current_value = $value;
			$this->most_recent_datetime = $timestamp;
		}
		return $this->save();
	}

	function set_average_value($value, $count = 1, $timestamp = false)
	{
		if (!$timestamp)
			$timestamp = time();

		if ($timestamp >= $this->most_recent_datetime)
		{
			$this->current_value = $value;
			$this->most_recent_datetime = $timestamp;
		}
		$this->set_period_value($value, $count, $timestamp);
		return $this->save();
	}

	function set_items_value($items, $timestamp = false)
	{
		if (!is_array($items))
			throw new Exception("Items should be an array for 'items' statistic type", 1);

		if (!$timestamp)
			$timestamp = time();
		foreach ($items as $item) 
		{
			$value = $item;
			$count = 1;
			if (is_array($item))
			{
				$value = $item['value'];
				$count = $item['count'];
			}

			$this->set_period_value($value, $count, $timestamp);
		}

		if ($timestamp >= $this->most_recent_datetime)
		{
			$this->current_value = $timestamp;
			$this->most_recent_datetime = $timestamp;
		}

		return $this->save();	
	}

	function get_combined_by_array($period_values)
	{
		if (!is_array($period_values))
			throw new Exception("Argument should be an array of statistics_value entities", 1);
		
		$max_datetime = 0;
		$total_count = 0;
		$new_value = 0;
		foreach ($period_values as $period_value) 
		{
			if ($period_value->statistic_id != $this->id)
				throw new Exception("Can't calculate combined statistic for other statistic item", 1);
			if ($period_value->datetime > $max_datetime)
				$max_datetime = $period_value->datetime;
			$total_count+=$period_value->count;

			if ($this->type == 'total')
			{
				if ($period_value->value > $new_value)
					$new_value = $period_value->value;
			} 
			elseif ($this->type == 'increment')
			{
				$new_value += $period_value->value;
			}
			elseif ($this->type == 'average')
			{
				$new_value += $period_value->value * $period_value->count;
			}
		}

		if ($this->type == 'average')
			$new_value = $new_value / $total_count;

		$combined = new statistics_value;
		$combined->statistic_id = $this->id;
		$combined->datetime = $max_datetime;
		$combined->count = $total_count;

		$combined->value = $new_value;

		return $combined;
	}

	function get_combined($statistics_value_1, $statistics_value_2)
	{
		return $this->get_combined_by_array(array($statistics_value_1, $statistics_value_2));
	}

	function archive()
	{
		// Archive
		// Older > 2 hours - by hour
		// Older > 48 hours - by day
		// Older > 14 days - by week
		// Older > 62 days - by month
		$values = $this->get_period_values();
		$values->order_by('datetime', 'DESC');
		
		$cur_time = time();
		$time_to_start_archive_by_hour = $cur_time - 2*60*60;
		$time_to_start_archive_by_day = $cur_time - 48*60*60;
		$time_to_start_archive_by_month = $cur_time - 62*24*60*60;
		
		$periods = array();

		foreach ($values as $value) 
		{
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

		foreach ($periods as $period) 
		if (count($period) > 1)
		{
			$combined = $this->get_combined_by_array($period);
			$combined->save();
			foreach ($period as $item_to_remove)
				$item_to_remove->delete();
		}

		$this->statistic_statistics_values->clear_entities();
		return true;
	}
 }






