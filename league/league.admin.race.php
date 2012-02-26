<?php


function league_admin_leagues_races($leagueId) {

  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }

  
  $queryString = sprintf("SELECT races.id as id, races.name as raceName, races.date as raceDate, leagues.name as leagueName " .
    "FROM {league_races} as races, {league_leagues} as leagues " .
    "WHERE races.league_id = leagues.id " .
    "AND leagues.id = %d " .
    "ORDER BY leagueName, raceDate",  $leagueId);
    
  $result = db_query($queryString);

  $i = 0;
  while ($row = db_fetch_object($result)) {
    
    if ($i == 0) {
      $content .= '<h3>' . $row->leagueName . '</h3>';
      $content .= '<table class="league"><tr><th>'. t('Name') . "</th><th>". t('Date') . "</th><th></th></tr>";
    }
    
    if ( ($i%2) == 0) {
      $content .= '<tr class="league-even">';
    } 
    else {
      $content .= '<tr class="league-odd">';
    }
    $content .= '<td>' . $row->raceName . '</td>';
    $content .= '<td>' . $row->raceDate . '</td>';
    $content .= '<td><a href="?q=admin/league/' . $leagueId . '/races/' . $row->id . '/edit">' . t("Edit") . '</a></td>';
    $content .= '</tr>';
    $i++;
  }

  $content .= "</table>";

  return $content;
}



function league_admin_leagues_races_add($id = NULL) {
 
 return drupal_get_form('league_admin_leagues_races_add_form', $id);
    
}

function league_admin_leagues_races_form($form_state, $leagueId = NULL, $id = NULL) {
  
  #echo "league id: " . $leagueId;
  #echo "  id: " .  $id;
  
  if (isset($id)) {
    $values =  league_admin_leagues_races_values($id);

    if ($_POST['op'] == t('Delete')) {
      drupal_goto('admin/league/' . $leagueId . '/races/' . $id . '/delete');
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

   if (!$values['league_id'])
   {
     $values['league_id'] = $leagueId;
   }

   $form['league_id'] = array(
      '#type' => 'select', 
      '#title' => t('League'),
      '#required' => TRUE,
      '#default_value' => $values['league_id'],
      '#options' => $leagues);
   
    if (!$values['date']) {
      $values['date'] = date('Y-m-d');
    }
   
  $form['date'] = array(
     '#type' => 'textfield', 
     '#title' => t('date'),
     '#cols' => 40,
     '#default_value' => $values['date']);
  
  if (!$values['time']) {
    $values['time'] = date('H') . ":00";
  }
     
  $form['time'] = array(
     '#type' => 'textfield', 
     '#title' => t('time'),
     '#cols' => 40,
     '#default_value' => $values['time']);  

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

function league_admin_leagues_races_values($id) {
  

  $result = db_query("SELECT * FROM {league_races} WHERE id= %d", $id);
    
  $values = array();

  if ($row = db_fetch_object($result)) {
    $values['id'] = $row->id;
    $values['league_id'] = $row->league_id;
    $values['name'] = $row->name;
    list($date, $time) = split(' ', $row->date);
    $values['date'] = $date;
    $values['time'] = $time;
  }

  return $values;
}

function league_admin_leagues_races_form_validate($form, &$form_state) {
  if ( !preg_match("/\d{4}-\d{2}-\d{2}/", $form_state['values']['date']) ) {
    form_set_error('date', t('This must be a valid date (YYYY-MM-DD)'));
  } elseif ( !preg_match("/\d{2}:\d{2}/", $form_state['values']['time']) ) {
    form_set_error('time', t('This must be a valid time (HH:MM).'));
  }
}

function league_admin_leagues_races_form_submit($form, &$form_state) {
  global $user;
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }

  $edit = $form_state['values'];
  if ($edit['id'] > 0) {
    $result = db_query("UPDATE {league_races} SET league_id = %d, name = '%s', date = '%s' WHERE id = %d", 
      $edit['league_id'], 
      $edit['name'], 
      $edit['date'] . ' ' . $edit['time'],
      $edit['id']);
  } else {
    $result = db_query("INSERT INTO {league_races} ". 
     "(id, league_id, name, date) " . 
     " VALUES('', %d, '%s', '%s')", 
      $edit['league_id'], 
      $edit['name'],
      $edit['date'] . ' ' . $edit['time']);
 }

  $form_state['redirect'] = 'admin/league/' . $edit['league_id'] . '/races';
}

function league_admin_leagues_races_delete($form_state, $leagueId = NULL, $id = NULL) {  
  
  if (!isset($id) && !isset($leagueId)) {
    drupal_not_found();
    return;
  }

  $form = array();
  $form['id'] = array('#type' => 'value', '#value' => $id);
  $form['league_id'] = array('#type' => 'value', '#value' => $leagueId);

  return confirm_form($form,
    t('Are you sure you want to delete this race entry?'),
    $_GET['destination'] ? $_GET['destination'] : 'admin/league/' . $leagueId . '/races',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
}

function league_admin_leagues_races_delete_submit($form, &$form_state) {
  
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }
  
  db_query("DELETE FROM {league_races} WHERE id = %d", $form_state['values']['id']);
  $result = db_query("SELECT id FROM {league_races_entries} WHERE race_id = %d",  $form_state['values']['id']);
  $raceEntryIds = array();
    while ($row = db_fetch_object($result)) {
      $raceEntryIds[] = $row->id;
    }
  
  foreach ($raceEntryIds as $raceEntryId) {
    db_query("DELETE FROM {league_races_entries} WHERE id = %d", $raceEntryId);
    db_query("DELETE FROM {league_laps} WHERE raceEntry_id = %d", $raceEntryId);
    db_query("DELETE FROM {league_drivers} WHERE raceEntry_id = %d", $raceEntryId);
    db_query("DELETE FROM {league_results} WHERE raceEntry_id = %d", $raceEntryId);
  }
  $form_state['redirect'] = 'admin/league/' .  $form_state['values']['league_id'] . '/races';
}

function league_admin_results() {
  
}

?>