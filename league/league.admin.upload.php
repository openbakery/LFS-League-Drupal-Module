<?php
/**
 * @league-teams
 * file that holds functions to upload gstats files
 *
 * 
 * 
 */

$drivers = array();
$raceEntryId = 0;

function league_admin_upload_form($form, &$form_state, $leagueId) {

  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }

  $queryString = "SELECT leagues.name AS league, races.name AS race, races.id AS id, leagues.servers AS servers " .
    "FROM {league_races} AS races, {league_leagues} AS leagues " .
    "WHERE races.league_id = leagues.id ".
    "AND leagues.id = :leagueId";
  
  $result = db_query($queryString, array(':leagueId' => $leagueId));
  $racesArray = array();
  foreach ($result as $row) {
    $racesArray[$row->id] = $row->league . ' - ' . $row->race;
    $servers = $row->servers;
  }
  
  end($racesArray);
  $form['race'] = array(
    '#type' => 'select', 
    '#title' => t('Race'),
    '#required' => TRUE,
    '#default_value' => key($racesArray),
    '#options' => $racesArray);

  $serversArray = array();
  for($i=0;$i<$servers;$i++) {
    $serversArray[] = ($i+1);
  }

  $form['server'] = array(
    '#type' => 'select', 
    '#title' => t('Server'),
    '#required' => TRUE,
    '#default_value' => 1,
    '#options' => $serversArray);   


    $form['type_options'] = array(
      '#type' => 'value',
      '#value' => _league_race_entry_types()
    );
    
  $form['type'] = array(
    '#type' => 'select', 
    '#title' => t('Type'),
    '#default_value' => $values['type'],
    '#options' => $form['type_options']['#value']);



  $form['#attributes'] = array("enctype" => "multipart/form-data");

  $form['uploaded_file'] = array(
    '#type' => 'file', 
    '#title' => t('File to upload'), 
    '#description' => t('Click "Browse..." to select a file to upload.'));


  $form['submit'] = array(
    '#type' => 'submit', 
    '#value' => t('Save'),
    '#default_value' => $values['name']);

  return $form;
}

function league_admin_upload_form_submit($form, &$form_state) {

  $directory = 'public://upload/league/';
  file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
  if($file = file_save_upload('uploaded_file', array('file_validate_extensions' => array('rcsv')), $directory, FALSE)) {
    $message = t('The attached file was successfully uploaded');
  	drupal_set_message($message);
  }	else	{
  	drupal_set_message(t('The attached file failed to upload. Please try again'));
  	return;
  }
  

  $raceEntryId = league_insert_stats_data($file->uri, 
    $form_state['values']['race'], 
    $form_state['values']['server'], 
    $form_state['values']['type']['type_options']); 

  $form_state['redirect'] = "league/results/" . $raceEntryId;
}

function _league_insert_race($line = "", $raceId, $server, $type) {
  #Track; Laps; QualifyingMinutes; NumberRacers; Weather; Wind
  #AS7;40;0;0;9;BRIGHT_CLEAR;NONE
  #echo $raceId . ", " . $name . ", " . $server . ", " . $date . ", " . $time . ", " . $type . "<br>";
  
  global $raceEntryId;
  
  $token = explode(";", $line);
  
  $fields = array(
    'race_id' => $raceId,
    'track' => $token[0],
    'laps' => $token[1],
    'qualifing_minutes' => $token[2],
    'weather' => 0, //$token[4],
    'wind' => 0, //$token[5],
    'type' => $type,
    'server' => $server
  );

  $raceEntryId = db_insert('league_races_entries')
    ->fields($fields)
    ->execute();

  return $raceEntryId;
  #echo "raceId--->" . $raceId . "<br>";
}

function _league_insert_driver($line = "", $raceId, $server, $type) {
  #LFSWorldName;Nickname;CarName;startingPosition;Plate
  
  global $drivers;
  global $raceEntryId;
  
  list($lfsworld_name, $nickname, $car, $starting_position, $plate) = explode(";", $line);
  
  $teams = _league_team_drivers_values($raceId);
  
  $fields = array(
    'raceEntry_id' => $raceEntryId,
    'uid' => 0,
    'lfsworld_name' => $lfsworld_name,
    'nickname' => $nickname,
    'starting_position' => $starting_position,
    'car' => $car,
    'plate' => $plate,
    'team_id' => $teams[strtolower($lfsworld_name)],
  );
  
  $driverId = db_insert('league_drivers')
    ->fields($fields)
    ->execute();
 
  $drivers[$lfsworld_name] = $driverId;
}

function _league_insert_result($line = "", $raceId, $server, $type) {
  #lfsWorldName;totalPosition;resultPosition;racetime;hours;bestLapTime;lapsCompleted;pitStops;flags;confirmationFlags
  #lfsWorldName;position;racetime;bestLapTime;lapsCompleted;pitStops;flags;confirmationFlags
  
  global $drivers;
  global $raceEntryId;
  
  list($lfsworld_name, $position, $race_time, $fastest_lap, $laps, $pitstops, $confirmation_flags) = explode(";", $line);
  
  $driverId = $drivers[$lfsworld_name];
  
  $fields = array(
    'raceEntry_id' => $raceEntryId,
    'driver_id' => $driverId,
    'position' => $position,
    'race_time' => $race_time,
    'fastest_lap' => $fastest_lap,
    'laps' => $laps,
    'pitstops' => $pitstops,
    'confirmation_flags' => $confirmation_flags
  );
  
  $driverId = db_insert('league_results')
    ->fields($fields)
    ->execute();
  
}

function _league_insert_lap($line = "", $raceId, $server, $type) {
   #number;time;split1;split2;split3;split4;totalTime;position;pit;
   #penalty;numberStops;rearLeft;rearRight;frontLeft;frontRight;work;pitStopTime;takeOverNewUserName;oldPenalty;newPenalty
   
  if ($type == 2) {
    // is qualifying
    return;
  }

  global $drivers;
  global $raceEntryId;
   
  $token = explode(";", $line);
  list(
    $lfsworld_name, 
    $number, 
    $time, 
    $split1, 
    $split2, 
    $split3, 
    $split4, 
    $overallTime, 
    $position,
    $pit,
    $penalty,
    $numberStops,
    $rearLeft,
    $rearRight,
    $frontLeft, 
    $frontRight,
    $work, 
    $pitStopTime,
    $takeOverNewUserName,
    $oldPenalty,
    $newPenalty
  ) = explode(";", $line);
  
  
  $driverId = $drivers[$lfsworld_name];
  
  if (trim($pit) == 'true') {
    $pit = 1;
  } else {
    $pit = 0;
  }
  
  if ($penalty == '') $penalty = 0;
  if ($numberStops == '') $numberStops = 0;
  if ($rearLeft == '') $rearLeft = 255;
  if ($rearRight == '') $rearRight = 255;
  if ($frontLeft == '') $frontLeft = 255;
  if ($frontRight == '') $frontRight = 255;
  if ($work == '') $work = 0;
  if ($pitStopTime == '') $pitStopTime = 0;
  if ($oldPenalty == '') $oldPenalty = 0;
  if ($newPenalty == '') $newPenalty = 0;
  
  $fields = array(
    'driver_id' => $driverId,
    'raceEntry_id' => $raceEntryId,
    'number' => $number,
    'time' => $time,
    'split1' => $split1,
    'split2' => $split2,
    'split3' => $split3,
    'split4' => $split4,
    'overallTime' => $overallTime,
    'position' => $position,
    'pit' => $pit,
    'penalty' => $penalty,
    'numberStops' => $numberStops,
    'rearLeft' => $rearLeft,
    'rearRight' => $rearRight,
    'frontLeft' => $rearRight,
    'frontRight' => $rearRight,
    'work' => $work,
    'pitStopTime' => $pitStopTime,
    'takeOverNewUserName' => $takeOverNewUserName,
    'oldPenalty' => $oldPenalty,
    'newPenalty' => $newPenalty
  );
  
  db_insert('league_laps')
    ->fields($fields)
    ->execute();
  
}

function _league_insert_flags($line = "", $raceId, $server, $type) {
  #lfsworldName;lapNumber ;type;duration

  if ($type == 2) {
    // is qualifying
    return;
  }
  
  global $drivers;
  global $raceEntryId;
   
  list($lfsworld_name, $lap_number, $type, $duration) = explode(";", $line);
  
  $driverId = $drivers[$lfsworld_name];
  
  $fields = array(
    'driver_id' => $driverId,
    'raceEntry_id' => $raceEntryId,
    'lap_number' => $lap_number,
    'type' => $type,
    'duration' => $duration,
  );
  
  db_insert('league_flags')
    ->fields($fields)
    ->execute();
}

function _league_insert_nothing($line = "") {
}

function league_insert_stats_data($uploadfile, $raceId, $server, $type) {
  //echo $raceId . ", " . $name . ", " . $server . ", " . $date . ", " . $time . ", " . $type . "<br>";

  global $raceEntryId;
  
  $SECTION_NAME = "RACECONTROL-SECTION:";

  $lines = file($uploadfile);
  
  #print_r($lines);
  #echo  "<br><br><br>" . $SECTION_NAME . "<br><br><br>";
  
// Loop through our array, show HTML source as HTML source; and line numbers too.
  foreach ($lines as $line_num => $line) {
      
    static $insertFunction = '_league_insert_nothing';
    if (strlen($line) > 0 && $line[0] != '#') {
        
      #echo $line . "<br>";
      $gstatsPosition = strpos($line, $SECTION_NAME);
      #echo $gstatsPosition . "<br>";
      if ($gstatsPosition === false) {
        #echo $insertFunction . "->" . $line . "<br>";
        $insertFunction($line, $raceId, $server, $type);
      } else {
        $gstatsSection = trim(substr($line, strlen($SECTION_NAME), strlen($line)));
        #echo $gstatsSection . "<br>";
        if ($gstatsSection == "RACE") {
          $insertFunction = '_league_insert_race';
        } else if ($gstatsSection == "DRIVER") {
          $insertFunction = '_league_insert_driver';
        } else if ($gstatsSection == "RESULTS") {
          $insertFunction = '_league_insert_result';
        } else if ($gstatsSection == "LAPS") {
          $insertFunction = '_league_insert_lap';
        } else if ($gstatsSection == "FLAGS") {
          $insertFunction = '_league_insert_flags';
        }
      }
    } 
  }
  return $raceEntryId;
}


