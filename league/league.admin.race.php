<?php


function league_admin_leagues_races($leagueId) {

  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }

  
  $queryString = "SELECT races.id as id, races.name as raceName, races.date as raceDate, leagues.name as leagueName " .
    "FROM {league_races} as races, {league_leagues} as leagues " .
    "WHERE races.league_id = leagues.id " .
    "AND leagues.id = :leagueId " .
    "ORDER BY leagueName, raceDate";
    
  $result = db_query($queryString, array(':leagueId' => $leagueId));

  $i = 0;
 
  
  foreach ($result as $row) {
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
    $content .= '<td>' . $row->racename . '</td>';
    $content .= '<td>' . $row->racedate . '</td>';
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

function league_admin_leagues_races_form($form, &$form_state, $leagueId = NULL, $id = NULL) {
  
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
   foreach ($result as $row) {
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
  

  $result = db_query("SELECT * FROM {league_races} WHERE id= :id", array(':id' => $id) );
    
  $values = array();

 foreach ($result as $row) {
    $values['id'] = $row->id;
    $values['league_id'] = $row->league_id;
    $values['name'] = $row->name;
    list($date, $time) = preg_split('/ /', $row->date);
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
  
  $fields = array(
	  'league_id' => $edit['league_id'],
	  'name' => $edit['name'], 
	  'date' => $edit['date'] . ' ' . $edit['time']
  );
  
  if ($edit['id'] > 0) {
	  db_update('league_races')
	    ->fields($fields)
	    ->condition('id', $edit['id'])
	    ->execute();  
  } else {
	  db_insert('league_races')
	    ->fields($fields)
	    ->execute();
 }

  $form_state['redirect'] = 'admin/league/' . $edit['league_id'] . '/races';
}

function league_admin_leagues_races_delete($form, &$form_state, $leagueId = NULL, $id = NULL) {  
  
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
  
  db_delete('league_races')
    ->condition('id', $form_state['values']['id'])
    ->execute();
  
  $result = db_query("SELECT id FROM {league_races_entries} WHERE race_id = :race_id",  array('race_id' => $form_state['values']['id']) );
  $raceEntryIds = array();
  foreach ($result as $row) {
    $raceEntryIds[] = $row->id;
  }
  
  foreach ($raceEntryIds as $raceEntryId) {
	  
    db_delete('league_races_entries')
      ->condition('id', $raceEntryId)
      ->execute();
	  
    db_delete('league_laps')
      ->condition('raceEntry_id', $raceEntryId)
      ->execute();
	  
    db_delete('league_drivers')
      ->condition('raceEntry_id', $raceEntryId)
      ->execute();

    db_delete('league_results')
      ->condition('raceEntry_id', $raceEntryId)
      ->execute();

  }
  $form_state['redirect'] = 'admin/league/' .  $form_state['values']['league_id'] . '/races';
}

function league_admin_results() {
  
}
