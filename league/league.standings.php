<?php

function league_standings($id = 0) {
  if ($id == 0)
  {
    drupal_not_found();
    return "";
  }
  
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Driver') . '</th><th>' . t('Points') . '</th>';

  $races = _league_fetch_races($id);
  
  $i = 1;
  foreach ($races as $race) {
    $content .= '<th><div title="' . $race->name . '">' . $i++ . '</div></th>';
  }
  $content .= '</tr>';
  
  $resultArray = league_get_result($id);
  
  $driverResults = $resultArray['driverResults'];
  $driverRacePoints =  $resultArray['driverRacePoints'];
  
  $names = league_get_profile_names($id);
  $i = 1;
  $position = 0;
  $previousPoints = 0;

  while (list($key, $result) = each($driverResults)) {
    
    if ( ($i%2) == 0) {
      $tdClass = "league-even";
    } 
    else {
      $tdClass = "league-odd";
    }
    $name = $key;
    if ($names) {
      $name = $names[$key];
    }

    global $user;
    if ($user->uid == $name->uid) {
     $tdClass = "league-highlight"; 
    }
    
    $positionValue = "";
    if ($previousPoints != $result) {
      $position++;
      $positionValue = $position . ".";
    }
    $previousPoints = $result;

    $line = sprintf("<tr class=\"%s\"><td>%s</td><td>%s</td><td>%s</td>",
      $tdClass,
      $positionValue,
      $name,
      $result
      );


    $content .= $line;
    foreach ($races as $race) {
       $content .= '<td><div title="' . t('Pos') . ': ' .  $driverRacePoints[$key][$race->id . '_position']  . " - Server: " . $driverRacePoints[$key][$race->id . '_server'] . " - " . $race->name . '">' . $driverRacePoints[$key][$race->id];
       
       if ($driverRacePoints[$key][$race->id . "_fastest"]) {
         $content .= "<sup>F</sup>";
       }
       if ($driverRacePoints[$key][$race->id . "_pole"]) {
          $content .= "<sup>P</sup>";
        }
        if ($driverRacePoints[$key][$race->id . "_penalty"]) {
           $content .= "<sup>X</sup>";
         }
       
       $content .= '</div></td>';
     }
     
     $content .= "</tr>\n";
     $i++;
  }
  $content .= '</table>';
  
  $content .= '<div style="margin-top: 1em">';
  $content .= t("F...Fastest Lap") . "<br/>" . t("P...Pole Position") . "<br/>" . t("X...Penalty");
  $content .= "</div>";
  return $content;
}

function league_standings_rookies($id = 0) {
  if ($id == 0)
  {
    drupal_not_found();
    return "";
  }
  
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Driver') . '</th><th>' . t('Points') . '</th></tr>';
  
  $result = db_query("SELECT rookies FROM {league_leagues} WHERE id = :id", array(':id' => $id) );
  foreach ($result as $row) {
    $rookies = preg_split("/,/", strtolower($row->rookies));
  }
 
  
  $resultArray = league_get_result($id);
  
  $driverResults = $resultArray['driverResults'];
 
  $names = league_get_profile_names($id);
  $i = 1;
  while (list($key, $result) = each($driverResults)) {
    if (in_array(strtolower($key), $rookies)) {
      if ( ($i%2) == 0) {
        $tdClass = "league-even";
      } 
      else {
        $tdClass = "league-odd";
      }
      $name = $key;
      if ($names) {
        $name = $names[$key];
      }

      global $user;
      if ($user->uid == $name->uid) {
       $tdClass = "league-highlight"; 
      }
    
      $line = sprintf("<tr class=\"%s\"><td>%d.</td><td>%s</td><td>%s</td></tr>\n",
        $tdClass,
        $i++,
        $name,
        $result
        );

       $content .= $line;
     }
  }
  $content .= '</table>';
  
  return $content;
}

