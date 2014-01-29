<?php

	$site_path = realpath(dirname(__FILE__)."/../");

	if (!defined('SITE_PATH'))
		define ('SITE_PATH', $site_path);

	set_include_path(SITE_PATH);
 
	define('DO_NOT_DELEGATE', true);

	require $site_path."/includes/init.php";

	$statistics = autoloader_get_model_or_class('statistics');

	$items = array();
	$items[] = array('type'=>'total', 'name'=>'Registrations', 'file'=>'registrations.php', 'once_in'=>24*60*60);

	foreach ($items as $item) 
	{
		$s = $statistics->get_by_name($item['name']);
		if (!$s || !$s->id)
		{
			echo "Adding new statistic item for '".$item['name']."' script\n";
			$s = $statistics->add($item['name'], $item['type']);
			$init = true;
			$need_run = true;
		}
		else
		{
			$last_run = $s->get_last_value_datetime();
			if ($last_run && $last_run > time() - $item['once_in'])
			{
				echo "Don't need to run '".$item['name']."' script\n";
				$need_run = false;
			}
			else
				$need_run = true;
			$init = false;
		}

		if ($need_run)
		{
			echo "Running '".$item['name']."' script\n";
			require("statistics/".$item['file']);
		}
	}

