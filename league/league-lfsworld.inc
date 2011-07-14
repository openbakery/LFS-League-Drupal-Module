<?php


function league_results_lfsworld($id = 0) {
	if (!$names) {
    $names = league_get_profile_names();
  }

	$race = _league_get_race($id);
	

	$raceResult = _league_get_race_result($race, $id, $names);
	//print_r($raceResult);
	
  $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  $output .= "<raceresults>\n";
  
	foreach ($raceResult as $result) {

		$output .= "\t<result>\n";
		$output .= "\t\t<pos>" . $result->position . "</pos>\n";
		$output .= "\t\t<userlist>\n";
		$output .= "\t\t\t<username>" . $result->driver->lfsworldName . "</username>\n";
		$output .= "\t\t</userlist>\n";
		$output .= "\t\t<playerlist>\n";
		$output .= "\t\t\t<playername>" . $result->driver->name . "</playername>\n";
		$output .= "\t\t</playerlist>\n";
		$output .= "\t\t<car>" . $result->car . "</car>\n";
		$output .= "\t\t<racetime>" . $result->time . "</racetime>\n";
		$output .= "\t\t<fastest>" . $result->fastestLap . "</fastest>\n";
		$output .= "\t\t<laps>" . $result->laps . "</laps>\n";
		$output .= "\t\t<points>" . $result->points . "</points>\n";
		$output .= "\t</result>\n";

  }



  $output .= "</raceresults>";
  

  drupal_set_header('Content-Type: text/xml; charset=utf-8');
  print $output;
  module_invoke_all('exit');
  exit;
}


?>