<?php
/**
 * @league-driver
 * file to get details for the driver
 *
 * 
 * 
 */

function league_get_race_driver_detail_data($id) {
  //$query = "SELECT driver_id, split1, split2, split3, split4 FROM {league_laps} {league_driver} WHERE raceEntry_id=%d AND time > 0 GROUP BY driver_id";

  $result = db_query("SELECT * FROM {league_drivers} AS drivers, {league_laps} AS laps " .
    "WHERE laps.driver_id = drivers.id AND drivers.id = :id ORDER BY laps.number",
	array(':id' => $id));

  $laps = array();

  $fastestLap = 600000000;
  $averageLap = 0;
  $i = 0;
  foreach ($result as $row) {
    if (!$name) {
      $name = strtolower($row->lfsworld_name);
      $startingPosition = $row->starting_position;
      $car = $row->car;
    }
    $lap['time'] = $row->time;
    if ($lap['time'] < $fastestLap) {
      $fastestLap = $lap['time'];
    }
    $averageLap += $lap['time'];
    $lap['split1'] = $row->split1;
    $lap['split2'] = $row->split2;
    $lap['split3'] = $row->split3;
    $lap['split4'] = $row->split4;
    $lap['position'] = $row->position;

    $laps[$row->number] = $lap;
    $i++;
   }
   $averageLap = intval(floor($averageLap / $i));
   
   $driverDetail = array();
   $driverDetail['laps'] = $laps;
   $driverDetail['fastestLap'] = $fastestLap;
   $driverDetail['averageLap'] = $averageLap;
   
   return $driverDetail;
}

function league_get_race_driver_detail($id) {
  
  if ($id) {
    $names = league_get_profile_names();
    
  
    //$query = "SELECT driver_id, split1, split2, split3, split4, pit FROM {league_laps} {league_driver} WHERE raceEntry_id=" . $id . " AND time > 0 GROUP BY driver_id";

    $result = db_query("SELECT * FROM {league_drivers} AS drivers, {league_laps} AS laps " .
      "WHERE laps.driver_id = drivers.id AND drivers.id = :driver_id ORDER BY laps.number", array(':driver_id' =>$id) );

    $laps = array();
  
    $fastestLap = 600000000;
    $averageLap = 0;
    $i = 0;
    
    
    foreach ($result as $row) {
      if (!$name) {
        $driver = null;
        if ($names) {
          $driver = $names[strtolower($row->lfsworld_name)];
        }
        if (!$driver) {
          $driver = new Driver(-1, strtolower($row->lfsworld_name), $row->lfsworld_name);
        }
        $startingPosition = $row->starting_position;
        $car = $row->car;
      }
      $lap['time'] = $row->time;
      if ($lap['time'] < $fastestLap) {
        $fastestLap = $lap['time'];
      }
      $averageLap += $lap['time'];
      $lap['split1'] = $row->split1;
      $lap['split2'] = $row->split2;
      $lap['split3'] = $row->split3;
      $lap['split4'] = $row->split4;
      $lap['position'] = $row->position;
      $lap['pit'] = $row->pit;
      
      $laps[$row->number] = $lap;
      $i++;
     }
     $averageLap = intval(floor($averageLap / $i));


     $content .= '<div class="league-item"><span class="league-label">' . t('Name') . ': </span>' . $driver . '</div>';
     $content .= '<div class="league-item"><span class="league-label">' . t('Car') . ': </span>' . $car . '</div>';
     $content .= '<div class="league-item"><span class="league-label">' . t('Starting Position') . ': </span>' . $startingPosition . '</div>';
     $content .= '<div class="league-item"><span class="league-label">' . t('Fastest Lap') . ': </span>' . league_get_time($fastestLap) . '</div>';
     $content .= '<div class="league-item"><span class="league-label">' . t('Average Lap') . ': </span>' . league_get_time($averageLap) . '</div>';
   
     $content .= '<table border="0" class="league" >';
     $content .= '<tr><th>' . t('Lap') . '</th><th>' . t('Position') . '</th><th>' . t('Time') . '</th><th>' . t('Gain') . '</th><th>' . t('Loss Fastest') . '</th><th>' . t('Gain Average') . '</th><th>' . t('Split 1') . '</th><th>' . t('Gain') . '</th><th>' . t('Split 2') . '</th><th>' . t('Gain') . '</th>';
   
     if ($laps[0]['split3']) {
       $content .= '<th>' . t('Split 3') . '</th><th>&nbsp;</th>';
     }
     if ($laps[0]['split4']) {
       $content .= '<th>' . t('Split 4') . '<th>&nbsp;</th></th>';
     }
     $content .= '<th>' . t('Pit') . '</th>';
     $content .= '</tr>';
   
     $i=0;
     foreach( $laps as $key => $lap )
     {
         if ( ($i%2) == 0) {
           $tdClass = "league-even";
         } else {
           $tdClass = "league-odd";
         }
     
        $timeGain = "";
        $split1Gain = "";
        $split2Gain = "";
        $split3Gain = "";
        $split4Gain = "";
      
        if ($i>0) {
          $timeGain = $lap['time'] - $laps[$i]['time'];
          $fastestLoss = $lap['time'] - $fastestLap;
          $averageGain = $lap['time'] - $averageLap;
          $split1Gain = $lap['split1'] - $laps[$i]['split1'];
          $split2Gain = $lap['split2'] - $laps[$i]['split2'];
          $split3Gain = $lap['split3'] - $laps[$i]['split3'];
          $split4Gain = $lap['split4'] - $laps[$i]['split4'];
        
        }
     
        $content .= sprintf('<tr class="%s"><td>%d</td><td>%s</td><td>%s</td><td class="%s">%s</td><td>%s</td><td class="%s">%s</td><td>%s</td><td class="%s">%s</td><td>%s</td><td class="%s">%s</td>',
           $tdClass,
           ($i+1),
           $lap['position'],
           league_get_time(intval($lap['time'])),
           _league_get_split_gain_hightlight($timeGain),
           league_get_time($timeGain, true),
           league_get_time($fastestLoss, true),
           _league_get_split_gain_hightlight($averageGain),
           league_get_time($averageGain, true),
           league_get_time(intval($lap['split1'])),
           _league_get_split_gain_hightlight($split1Gain),
           league_get_time($split1Gain, true),
           league_get_time(intval($lap['split2'])),
           _league_get_split_gain_hightlight($split2Gain),
           league_get_time($split2Gain, true));
         
         if ($laps[0]['split3']) {
           $content .= '<td>' . league_get_time(intval($lap['split3'])) . '</td>';
           $content .= '<td>' . league_get_time($split3Gain) . '</td>';
         }
         if ($laps[0]['split4']) {
           $content .= '<td>' . league_get_time(intval($lap['split4'])) . '</td>';
           $content .= '<td>' . league_get_time($split4Gain) . '</td>';
         }
         if ($lap['pit'] > 0) {
           $content .= '<td>Pit Stop</td>';
         } else {
            $content .= '<td>&nbsp</td>';
         }
        $content .= "</tr>\n";
       $i++;
     }
   
     $content .= '</table>';
   }
   return $content;
   
}

function league_get_driver_results($lfsworld_name) {
  
  $query = "SELECT entry.id AS race_entry_id, league.id AS league_id, league.name AS league_name, races.name AS race_name, races.date AS date, entry.track AS track, result.position AS position" . 
    " FROM {league_drivers} AS driver, {league_races_entries} AS entry, {league_results} AS result, " .
    " {league_races} AS races, {league_leagues} AS league " .
    " WHERE driver.raceEntry_id = entry.id AND result.raceEntry_id = entry.id AND entry.race_id = races.id " . 
    " AND driver.id = result.driver_id AND races.league_id = league.id AND result.confirmation_flags = 2 " .
    " AND lfsworld_name = :lfsworld_name" .
    " ORDER BY races.league_id DESC, races.date";
  
  $result = db_query($query, array(':lfsworld_name' => $lfsworld_name) );
  
  $values = array();
  
  $i = 0;
  
  foreach ($result as $row) {
    $values[] = $row;
  }
  return $values;
}

function _league_get_split_gain_hightlight($splitGain) {
  if (intval($splitGain) > 0) {
    return "league-loss";
  }
  return "league-gain";
}
