<?php
/**
 * @league-common
 * here common functions for the league modules can be found
 *
 * 
 * 
 */
 
function getRaceResult() {
	"".""."";
}

function league_get_time_with_hour($hours, $time)
{
  if ($hours > 0) {
    return $hours .":" .league_get_fulltime($time);
  }
  return league_get_fulltime($time);
}


function league_get_fulltime($time) {
  if ($time < 0) {
    $sign = "-";
  }
  if($time > 0){
    $hour = floor($time/(60*60000));
    $min = floor( ($time - $hour*60*60000) /60000 );
    $sek = floor($time/1000)%60;
    $ms = ($time/10)%100;
    if ($hour > 0) {
      return sprintf("%s%d:%02d:%02d.%02d", $sign, $hour, $min, $sek, $ms);
    } else {
      return sprintf("%s%02d:%02d.%02d", $sign, $min, $sek, $ms);
    }
  }
  return "-";
}

function league_get_time($time, $addSign = false, $full = false)
{
  $abstime = abs($time);

  if ($addSign) {
    if ($time < 0) {
      $sign = "-";
    } else {
      $sign = "+";
    }
  } else {
    $sign = "";
  }
  
  if(is_long($abstime)){
    $min = floor($abstime/60000);
    $sek = floor($abstime/1000)%60;
    $ms = ($abstime/10)%100;
    
    
    if ($full || $min > 0) {
      return sprintf("%s%d:%02d.%02d", $sign, $min, $sek, $ms);
    } else {
      return sprintf("%s%d.%02d", $sign, $sek, $ms);
    }
  }
  return "";
}

function league_get_confirmation_penalty($flags) {
  if ($flags & 64) {
    return t('DQF');
  } else if ($flags & 128) {
    return t('DNF');
  } else if ($flags & 16) {
    return t('TP');
  } else if ($flags & 32) {
    return t('TP');
  }
  return "";
}

function league_get_flags($flags) {
  $result;
  if (intval($flags) & 2048) {
    $result = $result . '<span title="' . t('Keyboard no help') . '">k </span>';
  }
  if (intval($flags) & 4096) {
    $result = $result . '<span title="' . t('Keyboard stabilized') . '">ks </span>';
  }
  if (intval($flags) & 1024) {
    $result = $result . '<span title="' . t('Mouse') . '">m </span>';
  }
  if (intval($flags) & 8) {
    $result = $result . '<span title="' . t('Auto gears') . '">ag</span>';
  }
  if (intval($flags) & 512) {
    $result = $result . '<span title="' . t('Auto clutch') . '">ac </span>';
  }
  if (intval($flags) & 4) {
    $result = $result . '<span title="' . t('Gear change blip') . '">gb </span>';
  }
  if (intval($flags) & 2) {
    $result = $result . '<span title="' . t('Gear cut change') . '">gc </span>';
  }
  if (intval($flags) & 64) {
    $result = $result . '<span title="' . t('Break help') . '">bh </span>';
  }
  if (intval($flags) & 128) {
    $result = $result . '<span title="' . t('Throttle help') . '">th </span>';
  }
  return trim($result);
  
  
}

function league_get_wind($wind) {
   switch($wind) {
     case 0:
      return t("no wind");
     case 1:
      return t("weak wind");
     case 2:
      return t("strong wind");
   }
   return "";
}


function league_get_track_name($trackCode) {
  $tracks = array();

	if (strlen($trackCode) >= 3) {
		$track = substr($trackCode, 0, 3);
	}
	
  $tracks["BL1"] = "Blackwood GP Track";
  $tracks["BL2"] = "Blackwood Rallycross";
  $tracks["BL3"] = "Blackwood Car Park";
  $tracks["SO1"] = "South City Classic";
  $tracks["SO2"] = "South City Sprint Track 1";
  $tracks["SO3"] = "South City Sprint Track 2";
  $tracks["SO4"] = "South City Long";
  $tracks["SO5"] = "South City Town Course";
  $tracks["SO6"] = "South City Chicane";
  $tracks["FE1"] = "Fern Bay Club";
  $tracks["FE2"] = "Fern Bay Green";
  $tracks["FE3"] = "Fern Bay Gold";
  $tracks["FE4"] = "Fern Bay Black";
  $tracks["FE5"] = "Fern Bay Rallycross";
  $tracks["FE6"] = "Fern Bay RallyX Green";
  $tracks["AU1"] = "Autocross";
  $tracks["AU2"] = "Skid Pad";
  $tracks["AU3"] = "Drag Strip";
  $tracks["AU4"] = "Eight Lane Drag";
  $tracks["KY1"] = "Kyoto Ring Oval";
  $tracks["KY2"] = "Kyoto Ring National";
  $tracks["KY3"] = "Kyoto Ring Long";
  $tracks["WE1"] = "Westhill International";
  $tracks["AS1"] = "Aston Cadet";
  $tracks["AS2"] = "Aston Club";
  $tracks["AS3"] = "Aston National";
  $tracks["AS4"] = "Aston Historic";
  $tracks["AS5"] = "Aston Grand Prix";
  $tracks["AS6"] = "Aston Grand Touring";
  $tracks["AS7"] = "Aston North";
	
	if (strtolower(substr($trackCode, -1)) == 'r') {
		return $tracks[$track] . " rev.";
	}
	
	
  return $tracks[$track];
}

function _league_get_short_car_name($carname) {
  $cars = array();
  $cars["xf gti"] = "XFG";
  $cars["xr gt"] = "XRG";
  $cars["xr gt turbo"] = "XRT";
  $cars["rb4 gt"] = "RB4";
  $cars["fxo turbo"] = "FXO";
  $cars["mrt5"] = "MRT";
  $cars["uf 1000"] = "UF1";
  $cars["raceabout"] = "RAC";
  $cars["fz50"] = "FZ5";
  $cars["xf gtr"] = "XFR";
  $cars["uf gtr"] = "UFR";
  $cars["formula xr"] = "FOX";
  $cars["formula v8"] = "FO8";
  $cars["bmw sauber"] = "BF1";
  $cars["fxo gtr"] = "FXR";
  $cars["xr gtr"] = "XRR";
  $cars["fz50 gtr"] = "FZR";
  if ($cars[strtolower($carname)]) {
    return $cars[strtolower($carname)];
  }
  return $carname;

}

function _league_string_crop($string, $length=20) {
  if ( strlen($string) > $length) {
    return substr($string, 0, 20) . "...";
  }
  return $string;
}


function _league_race_entry_type($id) {
  $types = _league_race_entry_types();
  return $types[$id];
}

function _league_race_entry_types() {
  return array(t('Main Race'), t('Sprint Race'), t('Qualifying'));
}

function _league_confirmation_flags_options() {
  return array(
    1 => t('Mentioned'), 
    2 => t('Confirmed'), 
    4 => t('Penalty drive-through'),
    8 => t('Penalty Stop and Go'),
    16 => t('Penalty 30'),
    32 => t('Penalty 45'),
    64 => t('Did Not Pit'),
    76 => t('Disqualified'),
    48 => t('Penalty Time'),
    128 => t('Did Not Finish'));
}

function _league_confirmation_flags_values($confirmation_flags) {
  $result = array();
  $options = _league_confirmation_flags_options();
  foreach ($options as $key => $value) {
    if ( ($key & $confirmation_flags) > 0) {
      $result[] = $key;
    }
  }
  return $result;
}

function _league_confirmation_flags_value($values) {
  $result = 0;
  foreach ($values as $key => $value) {
    $result = $result | $key;
  }
  return $result;
}



function _league_team_drivers_values($race_id) {

  $query = "SELECT team_drivers.lfsworld_name, teams.id " . 
    "FROM {league_teams} AS teams, {league_teams_drivers} AS team_drivers, {league_races} AS races " .
    "WHERE races.id = :raceId AND races.league_id = teams.league_id AND team_drivers.team_id = teams.id";

  $result = db_query($query, array(':raceId' => $race_id) );
  $values = array();
  foreach ($result as $row) {
    $values[$row->lfsworld_name] = $row->id;
  }
  return $values;
}

