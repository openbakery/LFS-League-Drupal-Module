<?php


function league_races($leagueId) {
  $content .= '<table border="0" class="league" >';
  $content .= '<tr>';
  $content .= '<th>' . t('Name') . '</th>';
  $content .= '<th>' . t('Track') . '</th>';
  $content .= '<th>' . t('Server') . '</th>';
  $content .= '<th>' . t('Date') . '</th>';
  $content .= '<th>' . ('Laps') . '</th>';
  $content .= '<th>' . ('Type') . '</th>';
  $content .= '<th>' . t('Weather') . '</th>';
  $content .= '<th>' . t('Wind') . '</th>';

  if (user_access('administer league')) {
    $content .= '<th>&nbsp</td>';
  }
  
  $content .= '</tr>';
  
  
  
  if ($leagueId != "") {

        $query = "SELECT * FROM {league_races} as race,  {league_races_entries} as entry " .
          "WHERE race.league_id= %d AND entry.race_id = race.id ORDER BY date, server ";
        $result = db_query($query, $leagueId);

        $i = 1;
        while ($row = db_fetch_object($result)) {
          if ( ($i%2) == 0) {
            $tdClass = "league-even";
          } 
          else {
            $tdClass = "league-odd";
          }

          $line = sprintf("\n\t<tr class=\"%s\"><td><a href=\"?q=league/results/%d\">%s</a> ".
            "</td><td>%s</td><td style=\"text-align:center\">%s</td><td>%s</td><td>%d</td><td>%s</td><td>%d</td><td>%s</td>",
            $tdClass,
            $row->id,
            $row->name,
            league_get_track_name($row->track),
            $row->server + 1,
            substr($row->date, 0, strlen($row->date)-3),
            $row->laps,
            _league_race_entry_type($row->type),
            $row->weather,
            league_get_wind($row->wind));
            
          if (user_access('administer league')) {
            $line .= '<td>' . l("Delete", "admin/league/" . $leagueId . "/races/results/" . $row->id . "/delete") . '</td>';
          }
            
          $content .=  $line;
          $content .= '</tr>';
        }
       
      } 
    
    $content .= '</table>';
  
  
  return $content;
  
}


?>