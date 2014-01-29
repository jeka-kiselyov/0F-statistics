<?php

  class model_statistics extends model_base
  {
    function add($name, $type = 'total')
    {
      $statistic = new statistic();
      $statistic->name = $name;
      $statistic->type = $type;
      $statistic->save();

      return $statistic;
    }

  }