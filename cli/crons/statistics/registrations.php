<?php

	if ($init)
	{
		$all_count = 0;
		//// Init now
		for ($i = 365; $i > 0; $i--)
		{
			$start_time = time() - $i * 60 * 60 * 24;
			$end_time = $start_time + 60 * 60 * 24 - 1;

			$count = $s->db->getone("SELECT COUNT(*) as cnt FROM users WHERE registration_date <= '".(int)$end_time."' ");
			if ($count > $all_count)
				$all_count = $count;

			if ($all_count > 0)
				$s->set((int)$count, 1, $end_time);
		}

		$s->archive();
	}

	$last_calculated = (int)$s->get_last_value_datetime();
	$count = $s->db->getone("SELECT COUNT(*) as cnt FROM users WHERE registration_date <= '".time()."' ");
	$s->set((int)$count, 1);