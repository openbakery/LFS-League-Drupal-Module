<?php

function league_admin_teams() {
  if (!user_access("access administration pages")) {
    return message_access();  
  }
  
  $content = '<a href="?q=admin/league/teams/add">' . t("Add new team") . '</a><p/>';
  
  $result = db_query("SELECT teams.id as id, teams.name as name, leagues.name as league_name " . 
    "FROM {league_teams} as teams, {league_leagues} as leagues " . 
    "WHERE teams.league_id = leagues.id ORDER BY league_name, name"
  );
  
  
  $content .= "<table>";
  $content .= "<tr>";
  $content .= '<th>' . t('Name'). '</th>';
  $content .= '<th>' . t('League'). '</th>';
  $content .= '<th>&nbsp</th>';
  $content .= '<th>&nbsp</th>';
  $content .= '</tr>';


   $i=0;
   while ($row = db_fetch_object($result)) {
  	 $content .= "<tr>";
     $content .= '<td>' . $row->name. '</td>';
     $content .= '<td>' . $row->league_name. '</td>';
     $content .= '<td><a href="?q=admin/league/teams/edit/' . $row->id . '">' . t("Edit") . '</a></td>';
     $content .= '<td><a href="?q=admin/league/teams/drivers/' . $row->id . '">' . t("Drivers") . '</a></td>';
     $content .= '</tr>';
  }
  $content .= "</table>";
  return $content;
}

function league_admin_teams_add($id = NULL) {
 return drupal_get_form('league_admin_teams_form', $id);   
}

function league_admin_teams_form($id = NULL) {

	if (isset($id)) {
		$values = league_admin_teams_values($id);

		if ($_POST['op'] == t('Delete')) {
      drupal_goto('admin/league/teams/delete/'. $id);
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
  while ($row = db_fetch_object($result)) {
  	$leagues[$row->id] = $row->name;
  }
  
  $form['league_id'] = array(
     '#type' => 'select', 
     '#title' => t('League'),
     '#required' => TRUE,
     '#default_value' => $values['league_id'],
     '#options' => $leagues);

 
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


function league_admin_teams_form_submit($form_id, $edit) {
  global $user;
  if (!user_access("access administration pages")) {
    return message_access();  
  }
  
  if ($edit['id'] > 0) {
    
    db_query("UPDATE {league_teams} SET name = '%s', league_id = %d " .  
      " WHERE id = %d", 
      $edit['name'], 
      $edit['league_id'], 
      $edit['id']); 
       
  } else {
    $result = db_query("INSERT INTO {league_teams} ". 
     "(id, name, league_id) " . 
     " VALUES('', '%s', %d)", $edit['name'], $edit['league_id']);
 }
  
  return "admin/league/teams";
}

function league_admin_teams_delete($id) {  
	if (!isset($id)) {
    drupal_not_found();
    return;
  }

  $form = array();
  $form['id'] = array('#type' => 'value', '#value' => $id);

  return confirm_form($form,
    t('Are you sure you want to delete this team entry?'),
    $_GET['destination'] ? $_GET['destination'] : 'admin/league/teams',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
	
}

function league_admin_teams_delete_submit($form_id, $form_values) {
  
  if (!user_access("access administration pages")) {
    return message_access();  
  }
  
  db_query("DELETE FROM {league_teams} WHERE id = %d", $form_values['id']);

  return 'admin/league/teams';
}

function league_admin_teams_values($id) {
  

  $result = db_query("SELECT * FROM {league_teams} WHERE id=%d", $id);
    
  $values = array();

  if ($row = db_fetch_object($result)) {
    $values['id'] = $row->id;
    $values['name'] = $row->name;
    $values['league_id'] = $row->league_id;
  }

  return $values;
}


function league_admin_teams_drivers($id = NULL) {
  if (!user_access("access administration pages")) {
    return message_access();  
  }
  
  if ($id == NULL) {
    return '<a href="?q=admin/league/teams">' . t("Back") . "</a>";
  }
  
  $content = '<a href="?q=admin/league/teams/drivers/add/' . $id . '">' . t("Add new driver") . '</a><p/>';
  
  $result = db_query("SELECT * FROM {league_teams_drivers} " . 
    "WHERE team_id = %d", $id);
  
  
  $content .= "<table>";
  $content .= "<tr>";
  $content .= '<th>' . t('LFSWorld Name'). '</th>';
  $content .= '<th>' . t('Active'). '</th>';
  $content .= '<th>&nbsp</th>';
  $content .= '</tr>';


   $i=0;
   while ($row = db_fetch_object($result)) {
  	 $content .= "<tr>";
     $content .= '<td>' . $row->lfsworld_name. '</td>';
     $content .= '<td>' . $row->active. '</td>';
     $content .= '<td><a href="?q=admin/league/teams/drivers/edit/' . $row->id . '">' . t("Edit") . '</a></td>';
     $content .= '</tr>';
  }
  $content .= "</table>";
  return $content;
}

function league_admin_teams_drivers_add($id) {
 return drupal_get_form('league_admin_teams_drivers_form', NULL, $id);   
}

function league_admin_teams_drivers_form($id = NULL, $team_id = NULL) {
	if (isset($id)) {
		$values = league_admin_teams_drivers_values($id);
	}

 $form = array();

  $form['lfsworld_name'] = array(
    '#type' => 'textfield', 
    '#title' => t('LFSWorld Name'),
    '#cols' => 32, 
    '#required' => TRUE,
    '#default_value' => $values['lfsworld_name']);
    
  $form['active'] = array(
    '#type' => 'checkbox', 
    '#title' => t('Active'),
    '#default_value' => $values['active'],
    '#options' => $active);

  if (isset($id)) {
	   $form['id'] = array('#type' => 'value', '#value' => $values['id']);
     $form['team_id'] = array('#type' => 'value', '#value' => $values['team_id']);
	} else {
    $form['team_id'] = array('#type' => 'value', '#value' => $team_id);
  }
  $form['submit'] = array(
    '#type' => 'submit', 
    '#value' => t('Save'),
    '#default_value' => $values['name']);
	
	return $form;
}


function league_admin_teams_drivers_form_submit($form_id, $edit) {
  global $user;
  if (!user_access("access administration pages")) {
    return message_access();  
  }
  
  if ($edit['id'] > 0) {
    
    db_query("UPDATE {league_teams_drivers} SET lfsworld_name = '%s', active = %d" .  
      " WHERE id = %d", 
      strtolower($edit['lfsworld_name']), 
      $edit['active'], 
      $edit['id']); 
       
  } else {
    $result = db_query("INSERT INTO {league_teams_drivers} ". 
     "(id, team_id, lfsworld_name, active) " . 
     " VALUES('', %d, '%s', %d)", $edit['team_id'], strtolower($edit['lfsworld_name']), $edit['active']);
 }
  
  return "admin/league/teams/drivers/" . $edit['team_id'];
}

function league_admin_teams_drivers_values($id) {
  

  $result = db_query("SELECT * FROM {league_teams_drivers} WHERE id=%d", $id);
    
  $values = array();

  if ($row = db_fetch_object($result)) {
    $values['id'] = $row->id;
    $values['lfsworld_name'] = $row->lfsworld_name;
    $values['active'] = $row->active;
    $values['team_id'] = $row->team_id;
  }

  return $values;
}

function league_team_drivers_values($race_id) {

  $query = "SELECT team_drivers.lfsworld_name, teams.id " . 
    "FROM {league_teams} AS teams, {league_teams_drivers} AS team_drivers, {league_races} AS races " .
    "WHERE races.id = %d AND races.league_id = teams.league_id AND team_drivers.team_id = teams.id";

  $result = db_query($query, $race_id);
  $values = array();
  while ($row = db_fetch_object($result)) {
    $values[$row->lfsworld_name] = $row->id;
  }
  return $values;
}

function league_teams_names($id) {
  $query = "SELECT teams.id, teams.name " . 
    "FROM {league_teams} AS teams " .
    "WHERE teams.league_id = %d";
  
  $result = db_query($query, $id);
   $values = array();
   while ($row = db_fetch_object($result)) {
     $values[$row->id] = $row->name;
   }
   return $values;
}


function league_teams_standings($id) {
  
  $content .= '<table border="0" class="league" >';
  $content .= '<tr><th>' . t('Pos') . '</th><th>'. t('Team') . '</th><th>' . t('Points') . '</th>';
  
  $races = _get_league_races($id);
  
  $resultArray = league_get_result($id);
  
  $driverResults = $resultArray['driverResults'];
  $driverRacePoints =  $resultArray['driverRacePoints'];
  
  $names = league_teams_names($id);

  $teamPoints = array();
  
  while (list($key, $result) = each($driverResults)) {
    foreach (array_values($races) as $race) {
      if ($driverRacePoints[$key][$race['id']. "_team_id"]) {
        $teamPoints[$driverRacePoints[$key][$race['id']. "_team_id"]] += $driverRacePoints[$key][$race['id']];
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


?>