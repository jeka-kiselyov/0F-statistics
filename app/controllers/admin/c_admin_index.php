<?php

class controller_admin_index extends admin_controller
{

  function index()
  {
  	$stats = array();

    if ($stats = $this->cache->get('admin_index_stats'))
    {

    } else {
      $stats_1 = $this->statistics->get_by_name('Registrations'); /// See cli/crons/statistic.php to add new items

      if ($stats_1)
      for ($i = 0; $i < 365; $i++)
      {
        $datetime = time() - 24*60*60*$i;
        $registrations = $stats_1->get_value_for_the_time($datetime);

        if ($registrations)
          $stats[] = array('time'=>$datetime, 'registrations'=>$registrations);
      }

      $this->cache->set($stats, 'admin_index_stats', array(), 24*60*60);
    }

  	$this->ta('stats', $stats);
  }
}