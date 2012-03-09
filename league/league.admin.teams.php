<?php
/**
 * @league-teams
 * file that holds functions to manage teams
 *
 * 
 * 
 */
function league_admin_teams($leagueId) {
  if (!user_access('administer league')) {
    drupal_access_denied();  
    return;
  }
  
  //$content = '<a href="?q=admin/league/teams/add">' . t("Add new team") . '</a><p/>';
  
  $result = db_query("SELECT teams.id as id, teams.name as name, leagues.name as league_name " . 
    "FROM {league_teams} as teams, {league_leagues} as leagues " . 
    "WHERE teams.league_id = leagues.id " .
    "AND teams.league_id = :id " .
    "ORDER BY league_name, name",
    array(':id' => $leagueId)
  );
  
  
  $content .= '<table class="league">';
  $content .= "<tr>";
  $content .= '<th>' . t('Name'). '</th>';
  $content .= '<th>' . t('League'). '</th>';
  $content .= '<th>&nbsp</th>';
  $content .= '<th>&nbsp</th>';
  $content .= '</tr>';


   $i=0;
   foreach ($result as $row) {
     if ( ($i%2) == 0) {
       $content .= '<tr class="league-even">';
     } else {
       $content .= '<tr class="league-odd">';
     }
     $content .= '<td>' . $row->name. '</td>';
     $content .= '<td>' . $row->league_name. '</td>';
     $content .= '<td>' . l("Edit", "admin/league/" . $leagueId . "/teams/" . $row->id . "/edit") . "</td>"; 
     $content .= '<td>' . l("Drivers", "admin/league/" . $leagueId . "/teams/" . $row->id . "/drivers") . "</td>"; 
     $content .= '</tr>';
     $i++;
  }
  $content .= "</table>";
  return $content;
}


function league_admin_teams_form($form, &$form_state, $leagueId, $teamId = NULL) {
	
 	if (isset($teamId)) {
  	$values = league_admin_teams_values($teamId);

    if ($_POST['op'] == t('Delete')) {
      drupal_goto('admin/league/' . $leagueId . '/teams/' . $teamId . '/delete');
    }
  }

	$form = array();

  $form['name'] = array(
    '#type' => 'textfield', 
    '#title' => t('Name'),
    '#cols' => 32, 
    '#required' => TRUE,
    '#default_value' => $values['name']);
    
  $result = db_query("SELECT * FROM {league_leagues}");
  $leagues = array();
  foreach ($result as $row) {
    $leagues[$row->id] = $row->name;
  }

	$leagueIdDefault = $values['league_id'];
	if (!$leagueIdDefault)
	{
		$leagueIdDefault = $leagueId;
	}

  $form['league_id'] = array(
     '#type' => 'select', 
     '#title' => t('League'),
     '#required' => TRUE,
     '#default_value' => $leagueIdDefault,
     '#options' => $leagues);

 
  if (isset($teamId)) {
     $form['delete'] = array('#type' => 'submit',
        '#value' => t('Delete'),
        '#weight' => 30,
     );
     $form['team_id'] = array('#type' => 'value', '#value' => $values['id']);
  }

  $form['submit'] = array(
    '#type' => 'submit', 
    '#value' => t('Save'),
    '#default_value' => $values['name']);
  
  return $form;
}


function league_admin_teams_form_submit($form, &$form_state) {
  global $user;
  if (!user_access('administer league')) {
    drupal_access_denied();  
    return;
  }
  
  $edit = $form_state['values'];
  
  $fields = array(
    'name' => $edit['name'], 
    'league_id' => $edit['league_id']
  );
  
  if ($edit['team_id'] > 0) {
    db_update('league_teams')
      ->fields($fields)
      ->condition('id',  $edit['team_id'])
      ->execute();
  } else {
    db_insert('league_teams')
      ->fields($fields)
      ->execute();
 }
  
  $form_state['redirect'] = 'admin/league/' . $edit['league_id'] . '/teams';
}


function league_admin_teams_delete($form, &$form_state, $leagueId = NULL, $id = NULL) {  
  if (!isset($id)) {
    drupal_not_found();
    return;
  }

  $form = array();
  $form['id'] = array('#type' => 'value', '#value' => $id);
  $form['leagueId'] = array('#type' => 'value', '#value' => $leagueId);

  return confirm_form($form,
    t('Are you sure you want to delete this team entry?'),
    $_GET['destination'] ? $_GET['destination'] : 'admin/league/' . $leagueId . '/teams',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
  
}

function league_admin_teams_delete_submit($form, &$form_state) {
  
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }
  
  db_delete('league_teams')
    ->condition('id', $form_state['values']['id'])
    ->execute();
  
  $form_state['redirect'] = 'admin/league/' . $form_state['values']['leagueId'] . '/teams';
}

function league_admin_teams_values($id) {

  $result = db_query("SELECT * FROM {league_teams} WHERE id=:id", array(':id' => $id) );
    
  $values = array();

 foreach ($result as $row) {
   $values['id'] = $row->id;
   $values['name'] = $row->name;
   $values['league_id'] = $row->league_id;
  }

  return $values;
}


function league_admin_teams_drivers($leagueId, $teamId) {
	
  if (!user_access('administer league')) {
    drupal_access_denied();  
    return;
  }
  
  if ($teamId == NULL) {
    drupal_access_denied();
    return;
  }
  
  $result = db_query("SELECT * FROM {league_teams_drivers} " . 
    "WHERE team_id = :teamId", array(":teamId" => $teamId) );
  
  
  $content .= '<table class="league	">';
  $content .= "<tr>";
  $content .= '<th>' . t('LFSWorld Name'). '</th>';
  $content .= '<th>' . t('Active'). '</th>';
  $content .= '<th>&nbsp</th>';
  $content .= '</tr>';


   $i=0;
   foreach ($result as $row) {
     $content .= "<tr>";
     $content .= '<td>' . $row->lfsworld_name . '</td>';
     $content .= '<td>' . $row->active . '</td>';
     $content .= '<td>' . l("Edit", "admin/league/" . $leagueId . "/teams/" . $teamId . "/". $row->id . "/edit") . '</td>';
     $content .= '</tr>';
  }
  $content .= "</table>";
  return $content;
}


function league_admin_teams_drivers_form($form, &$form_state, $leagueId, $teamId, $id = NULL) {

 	if (isset($id)) {
  	$values = league_admin_teams_drivers_values($id);

    if ($_POST['op'] == t('Delete')) {
      drupal_goto('admin/league/' . $leagueId . '/teams/' . $teamId . '/' . $id . '/delete');
    }
  }

 $form = array();

  $form['lfsworld_name'] = array(
    '#type' => 'textfield', 
    '#title' => t('LFSWorld Name'),
    '#cols' => 32, 
    '#required' => TRUE,
    '#default_value' => $values['lfsworld_name']);
  
  if (!$values['active'])
  {
    $values['active'] = true;
  }  
  $form['active'] = array(
    '#type' => 'checkbox', 
    '#title' => t('Active'),
    '#default_value' => $values['active'],
    '#options' => $active);

  if (isset($id)) {
     $form['id'] = array('#type' => 'value', '#value' => $values['id']);
  }
  
  $form['league_id'] = array('#type' => 'value', '#value' => $leagueId);
  $form['team_id'] = array('#type' => 'value', '#value' => $teamId);
  
  if (isset($id)) {
     $form['delete'] = array('#type' => 'submit',
        '#value' => t('Delete'),
        '#weight' => 30,
     );
     $form['id'] = array('#type' => 'value', '#value' => $values['id']);
  }
  
  $form['submit'] = array(
    '#type' => 'submit', 
    '#value' => t('Save'),
    '#default_value' => $values['name']);
  
  return $form;
}


function league_admin_teams_drivers_form_submit($form, &$form_state) {
  global $user;
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }
  
  $values = $form_state['values'];
  
  $fields = array(
	'team_id' => $values['team_id'],
    'lfsworld_name' => strtolower($values['lfsworld_name']),
    'active' => $values['active']
  );
  
  if ($values['id'] > 0) {
    db_update('league_teams_drivers')
      ->fields($fields)
      ->condition('id', $values['id'])
      ->execute();
  } else {
    db_insert('league_teams_drivers')
      ->fields($fields)
      ->execute();
  }
  
  $form_state['redirect'] = 'admin/league/' . $values['league_id'] . '/teams/' . $values['team_id'] . '/drivers';
}

function league_admin_teams_drivers_values($id) {

  $result = db_query("SELECT * FROM {league_teams_drivers} WHERE id= :id", array(':id' => $id) );
    
  $values = array();

  foreach ($result as $row) {
    $values['id'] = $row->id;
    $values['lfsworld_name'] = $row->lfsworld_name;
    $values['active'] = $row->active;
    $values['team_id'] = $row->team_id;
  }

  return $values;
}


function league_admin_teams_drivers_delete($form, &$form_state, $leagueId = NULL, $teamId = NULL, $id = NULL) {  
  if (!isset($id)) {
    drupal_not_found();
    return;
  }

  $form = array();
  $form['id'] = array('#type' => 'value', '#value' => $id);
  $form['leagueId'] = array('#type' => 'value', '#value' => $leagueId);
  $form['teamId'] = array('#type' => 'value', '#value' => $teamId);

  $values = league_admin_teams_drivers_values($id);
  $message = sprintf( t('Are you sure you want to delete the "%s" team driver entry?'), $values['lfsworld_name'] );

  return confirm_form($form,
   	$message,
    $_GET['destination'] ? $_GET['destination'] : 'admin/league/' . $leagueId . '/teams/' . $teamId,
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
  
}

function league_admin_teams_drivers_delete_submit($form, &$form_state) {
  
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }
  
  db_delete('league_teams_drivers')
    ->condition('id', $form_state['values']['id'])
    ->execute();

  $form_state['redirect'] = 'admin/league/' . $form_state['values']['leagueId'] . '/teams/' . $form_state['values']['teamId'];
}

function league_teams_names($id) {
  $query = "SELECT teams.id, teams.name " . 
    "FROM {league_teams} AS teams " .
    "WHERE teams.league_id = :leagueId";
  
  $result = db_query($query, array(':leagueId' => $id) );
   $values = array();
   foreach ($result as $row) {
     $values[$row->id] = $row->name;
   }
   return $values;
}




function league_teams_standings($id) {
  
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Team') . '</th><th>' . t('Points') . '</th>';
  
  $races = _league_fetch_races($id);
  
  $resultArray = league_get_result($id);
  
  $driverResults = $resultArray['driverResults'];
  $driverRacePoints =  $resultArray['driverRacePoints'];
  
  $names = league_teams_names($id);

  $teamPoints = array();
  
  while (list($key, $result) = each($driverResults)) {
    foreach ($races as $race) {
      if ($driverRacePoints[$key][$race->id. "_team_id"]) {
        $teamPoints[$driverRacePoints[$key][$race->id . "_team_id"]] += $driverRacePoints[$key][$race->id];
      }
    }
  }
  
  arsort($teamPoints);
  
  
  $i = 1;
  while (list($team, $points) = each($teamPoints)) {
    if ( ($i%2) == 0) {
      $tdClass = "league-even";
    } else {
      $tdClass = "league-odd";
    }
    if ($names) {
      $name = $names[$team];
    }
    
    $line = sprintf("<tr class=\"%s\"><td>%d.</td><td>%s</td><td>%s</td>",
      $tdClass,
      $i++,
      $name,
      $points
      );

     $content .= $line;
     
     $content .= "</tr>\n";
     
  }
  $content .= '</table>';
  return $content;
}


