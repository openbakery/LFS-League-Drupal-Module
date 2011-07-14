<?php


$drivers = array();
$raceEntryId = 0;

function league_admin_upload_form() {

	$result = db_query("SELECT leagues.name AS league, races.name AS race, races.id AS id, leagues.servers AS servers " .
  "FROM {league_races} AS races, {league_leagues} AS leagues WHERE races.league_id = leagues.id");
  $racesArray = array();
  while ($row = db_fetch_object($result)) {
    $racesArray[$row->id] = $row->league . ' - ' . $row->race;
		$servers = $row->servers;
  }

 	$form['race'] = array(
    '#type' => 'select', 
    '#title' => t('Race'),
    '#required' => TRUE,
    '#default_value' => $values['race'],
    '#options' => $racesArray);

  $serversArray = array();
  for($i=0;$i<$servers;$i++) {
    $serversArray[] = ($i+1);
  }
  $form['server'] = array(
    '#type' => 'select', 
    '#title' => t('Server'),
    '#required' => TRUE,
    '#default_value' => $values['server'],
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

  $form['filename'] = array(
    '#type' => 'file', 
    '#title' => t('File to upload'), 
    '#description' => t('Click "Browse..." to select a file to upload.'));


  $form['submit'] = array(
    '#type' => 'submit', 
    '#value' => t('Save'),
    '#default_value' => $values['name']);

	return $form;
}

function league_admin_upload_form_submit($form_id, $edit) {
  $fileinfo = file_check_upload('filename');

  if ($fileinfo) {
    file_save_upload('filename', file_directory_path());
  }
  
  $uploadfile =  file_directory_path() . "/" . $fileinfo->filename;
  
  $raceEntryId = league_insert_stats_data($uploadfile, $edit['race'], $edit['server'], $edit['type']['type_options']); 
  //echo "raceId: " . $raceId;
  return "league/results/" . $raceEntryId;
}

function _league_insert_race($line = "", $raceId, $server, $type) {
  #Track; Laps; QualifyingMinutes; NumberRacers; Weather; Wind
  //echo $raceId . ", " . $name . ", " . $server . ", " . $date . ", " . $time . ", " . $type . "<br>";
  
  global $raceEntryId;
  
  $token = explode(";", $line);
  
  
  $query = db_query("INSERT INTO {league_races_entries} VALUES('',%d, '%s',%d,%d,%d,%d,%d,%d)",
    $raceId,
    $token[0],
    $token[1],
    $token[2],
    $token[4],
    $token[5],
    $type,
    $server);
    
  $raceEntryId = mysql_insert_id();
  #echo "raceId--->" . $raceId . "<br>";
}

function _league_insert_driver($line = "", $raceId, $server, $type) {
  #LFSWorldName;Nickname;CarName;startingPosition;Plate
  
  global $drivers;
  global $raceEntryId;
  
  $token = explode(";", $line);
  
  $teams = league_team_drivers_values($raceId);
  
  $result = db_query("INSERT INTO {league_drivers} VALUES('',%d, %d, '%s','%s', %d, '%s', '%s', %d)",
    $raceEntryId,
    0,
    $token[0],
    $token[1],
    $token[3],
    $token[2],
    $token[4],
    $teams[strtolower($token[0])]);
  #echo "insert driver " . $tocken[0]; 
  $driverId = mysql_insert_id();
  $drivers[$token[0]] = $driverId;
}

function _league_insert_result($line = "", $raceId, $server, $type) {
  #lfsWorldName;totalPosition;resultPosition;racetime;hours;bestLapTime;lapsCompleted;pitStops;flags;confirmationFlags
  #lfsWorldName;position;racetime;bestLapTime;lapsCompleted;pitStops;flags;confirmationFlags
  
  global $drivers;
  global $raceEntryId;
  
  $token = explode(";", $line);
  $driverId = $drivers[$token[0]];
  
  
  $result = db_query("INSERT INTO {league_results} " . 
    "(raceEntry_id, driver_id, position, race_time, fastest_lap, laps, pitstops, confirmation_flags) " . 
    " VALUES(%d, %d, %d, %d, %d, %d, %d, %d)",
    $raceEntryId,
    $driverId,
    $token[1],
    $token[2],
    $token[3],
    $token[4],
    $token[5],
    $token[6]);
  
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
  $driverId = $drivers[$token[0]];
  
	if (trim($token[9]) == 'true') {
		$pit = 1;
	} else {
		$pit = 0;
	}
	
	if ($token[10] == '') $token[10] = 0;
	if ($token[11] == '') $token[11] = 0;
	if ($token[12] == '') $token[12] = 255;
	if ($token[13] == '') $token[13] = 255;
	if ($token[14] == '') $token[14] = 255;
	if ($token[15] == '') $token[15] = 255;
	if ($token[16] == '') $token[16] = 0;
	if ($token[17] == '') $token[17] = 0;
	if ($token[19] == '') $token[19] = 0;
	if ($token[20] == '') $token[20] = 0;
	
  $result = db_query("INSERT INTO {league_laps} VALUES('',%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, '%s', %d, %d)",
    $driverId,
    $raceEntryId,
    $token[1],
    $token[2],
    $token[3],
    $token[4],
    $token[5],
    $token[6],
    $token[7],
    $token[8],
    $pit,
    $token[10],
    $token[11],
    $token[12],
    $token[13],
    $token[14],
    $token[15],
    $token[16],
    $token[17],
    $token[18],
    $token[19],
    $token[20]);
  
}

function _league_insert_flags($line = "", $raceId, $server, $type) {
  #lfsworldName;lapNumber ;type;duration

	if ($type == 2) {
		// is qualifying
		return;
	}
  
  global $drivers;
  global $raceEntryId;
   
  $token = explode(";", $line);
  $driverId = $drivers[$token[0]];
  $result = db_query("INSERT INTO {league_flags} VALUES('',%d, %d, %d, %d, %d)",
    $driverId,
    $raceEntryId,
    $token[1],
    $token[2],
    $token[3]);
  
}

function _league_insert_nothing($line = "") {
}

function league_insert_stats_data($uploadfile, $raceId, $server, $type) {
  #echo $raceId . ", " . $name . ", " . $server . ", " . $date . ", " . $time . ", " . $type . "<br>";

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
?>
