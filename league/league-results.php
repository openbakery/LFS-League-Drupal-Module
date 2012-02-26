<?php
/**
 * @league-resulsts
 * file that holds functions to calculate the results
 *
 * 
 * 
 */


function league_results($leagueId, $raceId) {
  _league_results_detail($content, $leagueId, $raceId);
  return $content;
} 
 
function _league_results_main(&$content, $leagueId, $id, $names = NULL) {
  if (!$names) {
    $names = league_get_profile_names();
  }
  
  $content = "";
  
  if (!$id)
  {
    return -1;
  }
  
  $raceEntry = _league_fetch_race_entry($id);

  if ($raceEntry->type == 2) {
    $content .= _league_show_qualifying_result($id, $names);
  } else {
    $content .= _league_show_race_result($raceEntry, $id, $names);
  }
  return $raceEntry->type;
}

function _league_show_qualifying_result($id, $names) {
  
  $content .= '<table class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>' . t('Driver') . '</th><th>' . t('Car') . '</th><th>' . t('Time');
  $content .= '</th><th>' . t('Gap') . '</th></tr>';

  $query = "SELECT results.*, drivers.id as driver_id, drivers.lfsworld_name, drivers.car, drivers.starting_position " .
    "FROM {league_results} AS results, {league_drivers} AS drivers " .
    "WHERE results.raceEntry_id = %d  AND drivers.id = results.driver_id ORDER BY results.position";
        
  $result = db_query($query, $id);

  $i = 1;
  while ($row = db_fetch_object($result)) {
  
    if ( ($i%2) == 0) {
      $tdClass = "league-even";
    } else {
      $tdClass = "league-odd";
    }
    
    $lfsworldName = strtolower($row->lfsworld_name);
    
    $name = null;
    if ($names) {
      $name = $names[$lfsworldName];
    }
    if (!$name) {
      $name = new Driver(-1,$lfsworldName,$lfsworldName);
    }
    
    global $user;
    if ($user->uid == $name->uid) {
      $tdClass = "league-highlight";
    }
    
    if ($i == 1) {
      $fastestTime = $row->fastest_lap;
    } else {
      $gap = league_get_time(($row->fastest_lap)-$fastestTime, true);
    }
    
    $content .= sprintf('<tr class="%s"><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>' . "\n",
      $tdClass,
      $row->position,
      $name->name,
      _league_get_short_car_name($row->car),
      league_get_time(intval($row->fastest_lap)),
      $gap
      );

      
      $content .= '</tr>';      
      $i++;
  }



  $content .= '</table>';
  return $content;
}

  
function _league_show_race_result($race, $id, $names) {
  
  $raceResult = _league_fetch_race_entry_result($race, $id, $names);
  $content .= "<div class=\"league-item\"><span class=\"league-label\">" . t('Name:') ."</span>" . $race->name . "</div>";
  $content .=  "<div class=\"league-item\"><span class=\"league-label\">" . t('Track:') ."</span>" . league_get_track_name($race->track) . "</div>";
  $content .=  "<div class=\"league-item\"><span class=\"league-label\">" . t('Duration:') ."</span>" . $race->laps . "</div>";
  $content .=  "<div class=\"league-item\"><span class=\"league-label\">" . t('Conditions:') ."</span>" . league_get_wind($race->wind) . "</div>";
  

  $content .= '<p/>';
  $content .= '<table class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>' . t('Driver') . '</th><th>' . t('Car') . '</th><th>' . t('Race Time');
  $content .= '</th><th>' . t('Fastest Lap') . '</th><th>' . t('Laps') . '</th><th>' . t('Pitstops') . '</th><th>' . t('Points') . '</th><th>' . t('Penalty') . '</th>';

  if (user_access('administer league')) {
    $content .= '<th>&nbsp;</th>';
  }
  $content .= '</tr>';
      
  $i = 1;

  foreach($raceResult as $result) {
  
    if ( ($i%2) == 0) {
      $tdClass = "league-even";
    } else {
      $tdClass = "league-odd";
    }
    
    $lfsworldName = strtolower($result->driver->lfsworldName);
    
    $name = $result->driver;
  
    global $user;
    if ($user->uid == $name->uid) {
      $tdClass = "league-highlight";
    }
    
    $points = $result->points;
    if ($result->hasFastestLap) {
       $points  .= "<sup>F</sup>";
     }
     if ($result->hasPolePosition) {
        $points .= "<sup>P</sup>";
    }
    // is penalty is null than do not display an entry
    if ($result->penalty != 0) {
      $points .= '<sup title="' . t('penalty') . '">(' . $row->penalty . ")</sup>";
    }
    

    
    $line = sprintf('<tr class="%s"><td>%d</td><td><a href="?q=league/driver/detail/%d">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%d</td><td>%s</td><td>%s</td>' . "\n",
      $tdClass,
      $result->position,
      $result->driver->id,
      $result->driver->name,
      $result->car,
      $result->time,
      $result->fastestLap,
      $result->laps,
      $result->pitstops,
      $points,
      league_get_confirmation_penalty($result->confirmationFlags)
      );

      $content .= $line;
      if (user_access('administer league')) {
        $content .= '<td><a href="?q=admin/league/races/' . $id . '/results/' . $result->id . '/edit">' . t('Edit') . '</a></td>';
      }
      
      $content .= '</tr>';      
      $i++;
  }
  
  $content .= '</table>';
  return $content;
}

function league_get_race_result($id, $names) {
  _league_results_main($content, null, $id, null);
  return $content;
}

function _league_results_detail(&$content, $leagueId, $id) {
  $names = league_get_profile_names();
  $type = _league_results_main($content, NULL, $id, $names);
  if ($type == 2) {
    return $content;
  }
  
  
  if (module_exists('league_graph')) {
    $content .= '<a href="?q=league_graph/detailsSelect/' . $id . '">' . t('Driver compare chart') . '</a><br/>';
    $content .= '<br/><a href="?q=league_graph/timesLarge/' . $id . '""><img src="?q=league_graph/times/' . $id . '" alt="Race time progess graph"/></a>';
    $content .= '<br/><a href="?q=league_graph/positionsLarge/' . $id . '""><img src="?q=league_graph/positions/' . $id . '" alt="Lap per lap graph"/></a>';
  }
  
  $content .= '<h2>' . t('Hightest climber') . '</h2>';
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>' . t('Driver') . '</th><th>' . t('Start') . '</th><th>' . t('Finish') . '</th><th>' . t('Gain') . '</th></tr>';
    
  $query = "SELECT drivers.lfsworld_name, drivers.starting_position, results.position, drivers.starting_position-results.position AS gain " .
     "FROM {league_results} AS results, {league_drivers} AS drivers " .
     "WHERE results.raceEntry_id = %d AND drivers.id = results.driver_id " .
     "AND (drivers.starting_position-results.position) > 0 " .
     "ORDER BY gain DESC";
      
  $result = db_query($query, $id);

  $i = 1;
  while ($row = db_fetch_object($result)) {

    if ( ($i%2) == 0) {
      $tdClass = "league-even";
    } else {
      $tdClass = "league-odd";
    }
    
    $name = null;
    if ($names) {
      $name = $names[strtolower($row->lfsworld_name)];
    }
    if (!$name) {
      $name = new Driver(-1, strtolower($row->lfsworld_name),strtolower($row->lfsworld_name));
    }

    global $user;
    if ($user->uid == $name->uid) {
      $tdClass = "league-highlight";
    }
    

    $line = sprintf("<tr class=\"%s\"><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
      $tdClass,
      $i,
      $name->name,
      $row->starting_position,
      $row->position,
      $row->gain);

      $content .= $line;
      $i++;
  }
  
  $content .= '</table>';
  
  $content .= '<h2>'. t('Fastest lap') . '</h2>';
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Driver') . '</th><th>' . t('Time') . '</th><th>' . t('Gap') . '</th><th>' . t('Lap') . '</th></tr>';

  $allDrivers = array();

  $query = "SELECT drivers.id, drivers.lfsworld_name, results.fastest_lap, min(laps.number) as number " . 
    "FROM {league_results} AS results, {league_drivers} AS drivers, {league_laps} AS laps " .
    "WHERE results.raceEntry_id = %d AND drivers.id = results.driver_id ". 
    "AND laps.raceEntry_id = %d AND laps.time = results.fastest_lap AND laps.driver_id = drivers.id " . 
    "GROUP BY drivers.id ORDER BY fastest_lap";
    
  $result = db_query($query, $id, $id);

  $fastestLapByDriver = array();
  $i = 1;
  while ($row = db_fetch_object($result)) {
    $name = null;
    if ($names) {
      $name = $names[strtolower($row->lfsworld_name)];
    }
    if (!$name) {
      $name = new Driver(-1, strtolower($row->lfsworld_name),strtolower($row->lfsworld_name));
    }

    
    $allDrivers[$row->id] = $name;
    if ( ($i%2) == 0) {
      $tdClass = "league-even";
    } else {
      $tdClass = "league-odd";
    }
    
    global $user;
    if ($user->uid == $name->uid) {
      $tdClass = "league-highlight";
    }
    
    
    if ($fastestLap == 0) {
      $fastestLap = $row->fastest_lap;
    }
    
    
    $line = sprintf("<tr class=\"%s\"><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
      $tdClass,
      $i,
      $name->name,
      league_get_time(intval($row->fastest_lap)),
      league_get_time( -($fastestLap-$row->fastest_lap), true),
      $row->number);
      
    $content .=  $line;
    $i++;
    
    $fastestLapByDriver[$row->id] = $row->fastest_lap;
  }
  $content .= '</table>';
      
  //$query = "SELECT driver_id,min(split1) as split1, min(split2) as split2, min(split3) as split3, min(split4) as split4 FROM {league_laps} {league_driver} WHERE raceEntry_id=" . $id . " AND time > 0 GROUP BY driver_id";

  $query = "SELECT driver_id, number, split1, split2, split3, split4 FROM {league_laps} {league_driver} WHERE raceEntry_id=%d " .  
    " AND time > 0 ORDER BY driver_id, number";
  
  $result = db_query($query, $id);

  $split = array();
  $splitLapNumber = array();
  $sumSplit = array();
  $sumSplit[1] = 1;
  $bestPossibleLap = array();
  while ($row = db_fetch_object($result)) {
    if ($split[1][$row->driver_id] == 0 || $split[1][$row->driver_id] > $row->split1) {
      $split[1][$row->driver_id] = $row->split1;
      $splitLapNumber[1][$row->driver_id] = $row->number;
    }
    if ($split[2][$row->driver_id] == 0 || $split[2][$row->driver_id] > $row->split2) {
      $split[2][$row->driver_id] = $row->split2;
      $splitLapNumber[2][$row->driver_id] = $row->number;
    }
    if ($split[3][$row->driver_id] == 0 || $split[3][$row->driver_id] > $row->split3) {
      $split[3][$row->driver_id] = $row->split3;
      $splitLapNumber[3][$row->driver_id] = $row->number;
    }
    if ($split[4][$row->driver_id] == 0 || $split[4][$row->driver_id] > $row->split4) {
      $split[4][$row->driver_id] = $row->split4;
      $splitLapNumber[4][$row->driver_id] = $row->number;
    }
    $bestPossibleLap[$row->driver_id] = $split[1][$row->driver_id] + $split[2][$row->driver_id] + $split[3][$row->driver_id] + $split[4][$row->driver_id];
    $sumSplit[2] += $row->split2;
    $sumSplit[3] += $row->split3;
    $sumSplit[4] += $row->split4;
  }

  $splitNumber = 1;
  while ($sumSplit[$splitNumber] > 0) {

    $content .= '<h2>' . t('Fastest split') . $splitNumber. '</h2>';
    $content .= '<table border="0" class="league" >';
    $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Driver') . '</th><th>' . t('Time') . '</th><th>' . t('Gap') . '</th><th>' . t('Lap') . '</th></tr>';
  
    $splitArray = $split[$splitNumber];
    asort($splitArray);
    $i = 1;
    $fastestLap = 0;
    while (list($key, $time) = each($splitArray)) {

      if ($fastestLap == 0) {
        $fastestLap = $time;
      }
      
      if ( ($i%2) == 0) {
        $tdClass = "league-even";
      } else {
        $tdClass = "league-odd";
      }
      
      global $user;
      if ($user->uid == $allDrivers[$key]->uid) {
        $tdClass = "league-highlight";
      }
      

      $line = sprintf("<tr class=\"%s\"><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
        $tdClass,
        $i,
        $allDrivers[$key]->name,
        league_get_time(intval($time)),
        league_get_time( -($fastestLap-$time), true),
        $splitLapNumber[$splitNumber][$key]);

        $content .= $line;
        $i++;
      }
      $splitNumber++;
      
      $content .= '</table>';
    }  

    $content .= '<h2>'. t('Best possible lap') . '</h2>';
    $content .= '<table border="0" class="league" >';
    $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Driver') . '</th><th>' . t('Time') . '</th><th>' . t('Gap') . '</th><th>' . t('Best Lap') . '</th></tr>';
    
    asort($bestPossibleLap);
    $i=1;
    $fastestLap = 0;
    while (list($key, $time) = each($bestPossibleLap)) {
      
      if ($fastestLap == 0) {
        $fastestLap = $time;
      }
      
      if ( ($i%2) == 0) {
        $tdClass = "league-even";
      } else {
        $tdClass = "league-odd";
      }
      
      global $user;
      if ($user->uid == $allDrivers[$key]->uid) {
        $tdClass = "league-highlight";
      }
      

      $line = sprintf("<tr class=\"%s\"><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
        $tdClass,
        $i,
        $allDrivers[$key]->name,
        league_get_time(intval($time)),
        league_get_time( -($fastestLap-$time), true),
        league_get_time( -($time-$fastestLapByDriver[$key]) , true) );

        $content .= $line;
        $i++;
    }
    
    
  $content .= '</table>';

  $query = "SELECT driver_id, count(type) AS count, type FROM {league_flags} where raceEntry_id = %d ". 
    " GROUP BY driver_id, type ORDER BY type, count DESC";
   
  $result = db_query($query, $id);

  $blueFlags = array();
  $yellowFlags = array();
  while ($row = db_fetch_object($result)) {
    if ($row->type == 1) {
      $blueFlags[$row->driver_id] = $row->count;
    } else {
      $yellowFlags[$row->driver_id] = $row->count;
    }
  }

      
  $content .= '<h2>'. t('Blue flags shown') . '</h2>';
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Driver') . '</th><th>' . t('Flags') . '</th></tr>';

  $i = 1;
  while (list($key, $count) = each($blueFlags)) {  
    $line = sprintf("<tr class=\"%s\"><td>%d</td><td>%s</td><td>%s</td></tr>\n",
      $tdClass,
      $i,
      $allDrivers[$key]->name,
      $count);
      
    $content .=  $line;
    $i++;
  }
  $content .= '</table>';
  
  $content .= '<h2>'. t('Yellow flags caused') . '</h2>';
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Driver') . '</th><th>' . t('Flags') . '</th></tr>';

  $i = 1;
  while (list($key, $count) = each($yellowFlags)) {  
    $line = sprintf("<tr class=\"%s\"><td>%d</td><td>%s</td><td>%s</td></tr>\n",
      $tdClass,
      $i,
      $allDrivers[$key]->name,
      $count);
      
    $content .=  $line;
    $i++;
  }
  $content .= '</table>';


  $content .= '<h2>'. t('Pitstops') . '</h2>';
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>'. t('Driver') . '</th><th>' . t('Wheels') . '</th><th>' . t('Work') . '</th><th>' . t('Time') . '</th></tr>';
  
  $query = "SELECT driver_id, numberStops, rearLeft, rearRight, frontLeft, frontRight, work, pitStopTime " .
    " FROM {league_laps} WHERE pit > 0 AND raceEntry_id = %d ORDER BY driver_id, numberStops";

   
  $result = db_query($query, $id);
  $i = 0;
  $oldDriver = 0;
  while ($row = db_fetch_object($result)) {
    
    if ($oldDriver != $row->driver_id) {
      $i++;
      if ( ($i%2) == 0) {
        $tdClass = "league-even";
      } else {
        $tdClass = "league-odd";
      }
      
      global $user;
      if ($user->uid == $allDrivers[$row->driver_id]->uid) {
        $tdClass = "league-highlight";
      }
      

      $wheel = "";
      if ($row->rearLeft > 0) {
        $wheel .= t('RL') . " ";
      }
      if ($row->rearRight > 0) {
        $wheel .= t('RR') . " ";
      }
      if ($row->frontLeft > 0) {
        $wheel .= t('FL') . " ";
      }
      if ($row->frontRight > 0) {
        $wheel .= t('FR') . " ";
      }
      
      $line = sprintf("<tr class=\"%s\"><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
        $tdClass,
        $allDrivers[$row->driver_id]->name,
        $wheel,
        _league_get_pit_work($row->work),
        league_get_time($row->pitStopTime));
      $oldDriver = $row->driver_id;
    } else {
      $line = sprintf("<tr class=\"%s\"><td></td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
        $tdClass,
        $wheel,
        _league_get_pit_work($row->work),
        league_get_time($row->pitStopTime));
    }
    $content .=  $line;

   
  }
  $content .= '</table>';
  
  return $content;
}


function _league_get_pit_work($work) {
  $workText = "";
  if ($work & 1) {
    return t('Nothing');
  }
  if ($work & 4 || $work & 16 || $work & 64 || $work & 128 || $work & 256 ||  $work & 1024 || $work & 4096) {
    if ($workText != "") {
      $workText .= ", ";
    }
    $workText .= t('Mech. Dam.');
  }
  /*
  if ($work & 8 || $work & 32 || $work & 512 || $work & 2048 || $work & 8192) {
    if ($workText != "") {
      $workText .= ", ";
    }    $workText .= t('Tyre(s) change');
  }
  */
  if ($work & 16384) {
    if ($workText != "") {
      $workText .= ", ";
    }
    $workText .= t('Minor Dam.');
  }
  if ($work & 32768) {
    if ($workText != "") {
      $workText .= ", ";
    }
    $workText .= t('Major Dam.');
  }
  if ($work & 65536) {
    if ($workText != "") {
      $workText .= ", ";
    }
    $workText .= t('Setup') . " ";
  }
  if ($work & 131072) {
    if ($workText != "") {
      $workText .= ", ";
    }
    $workText .= t('Refuel') . " ";
  }
  return $workText;
}


function _league_fetch_race_entry_result($raceEntry, $id, $names) {
  if ($raceEntry == null) {
    return;
  }

  $query = "SELECT results.*, drivers.id as driver_id, drivers.lfsworld_name, drivers.car, drivers.starting_position " .
    "FROM {league_results} AS results, {league_drivers} AS drivers " .
    "WHERE results.raceEntry_id = %d AND drivers.id = results.driver_id ORDER BY results.position";
        
  $queryResult = db_query($query, $id);

  $league_id = $raceEntry->leagueId;
  if (!$league_id) {
    $league_id = 1;
  }

  $racePoints = league_get_result($league_id);
  $driverRacePoints = $racePoints['driverRacePoints'];

  $i = 1;
  $raceResult = array();

  while ($row = db_fetch_object($queryResult)) {
  
    $result = new Result();
    $lfsworldName = strtolower($row->lfsworld_name);
    
    $driver = null;
    if ($names) {
      $driver = $names[$lfsworldName];
    }
    if (!$driver) {
      $driver = new Driver(-1,$lfsworldName,strtolower($row->lfsworld_name));
    }

    $result->driver = $driver;
    
    if ($i == 1) {
      $result->time = league_get_fulltime(intval($row->race_time));
      $winnerTime = $row->race_time;
      $winnerLaps = $row->laps;
    } else {
      if ($winnerLaps != $row->laps) {
        $result->time = '+' . ($winnerLaps - $row->laps) . ' ' . t('Laps');
      } else {
        $result->time = '+' . league_get_time($winnerTime - $row->race_time);
      }
    }
    if ($driverRacePoints[$lfsworldName][$raceEntry->id]) {
      $result->points = $driverRacePoints[$lfsworldName][$raceEntry->id];
    } else {
      $result->points = 0;
    }
    if ($driverRacePoints[$lfsworldName][$raceEntry->id . "_fastest"]) {
       $result->hasFastestLap = true;
     }
     if ($driverRacePoints[$lfsworldName][$raceEntry->id. "_pole"]) {
        $result->hasPolePosition = true;
    }

    // is penalty is null than do not display an entry
    if ($row->penalty != 0) {
      $result->penalty .= $row->penalty;
    }

    $result->id = $row->id;
    $result->car = $row->car;
    $result->position = $i;
    $result->fastestLap = league_get_time($row->fastest_lap);
    $result->laps = $row->laps;
    $result->confirmationFlags = $row->confirmation_flags;
    $result->pitstops = $row->pitstops;
    $result->driver->id = $row->driver_id;
    $raceResult[] = $result;
    $i++;
  }
  
  return $raceResult;
}


?>
