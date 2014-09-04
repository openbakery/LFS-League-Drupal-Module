<?php

include(drupal_get_path('module', 'league') .'/league.races.php');


function league_admin_races_results($leagueId) {
  $content .= league_races($leagueId);
  return $content;
}

function result_load($resultId) {
  if (!is_numeric($resultId)) {
    return FALSE;
  }
  
  if ($resultId <= 0)
  {
    return FALSE;
  }
  return $resultId;
}

function league_results_edit_form($form, &$form_state, $resultId, $resultEntryId)
{
  $values = league_admin_results_values($resultEntryId);
  $form = array();
  
  $form['result_id'] = array(
    '#type' => 'hidden', 
    '#value' => $values['result_id']);  

  $form['raceEntry_id'] = array(
     '#type' => 'textfield', 
     '#title' => t('raceEntry_id'),
     '#cols' => 32, 
     '#required' => TRUE,
     '#default_value' => $values['raceEntry_id']);

  $form['driver_id'] = array(
     '#type' => 'textfield', 
     '#title' => t('driver_id'),
     '#cols' => 32, 
     '#required' => TRUE,
     '#default_value' => $values['driver_id']);
     
  $form['position'] = array(
        '#type' => 'textfield', 
        '#title' => t('position'),
        '#cols' => 32, 
        '#required' => TRUE,
        '#default_value' => $values['position']);

  $form['race_time'] = array(
        '#type' => 'textfield', 
        '#title' => t('race_time'),
        '#cols' => 32, 
        '#required' => TRUE,
        '#default_value' => $values['race_time']);

  $form['fastest_lap'] = array(
        '#type' => 'textfield', 
        '#title' => t('fastest_lap'),
        '#cols' => 32, 
        '#required' => TRUE,
        '#default_value' => $values['fastest_lap']);

  $form['laps'] = array(
        '#type' => 'textfield', 
        '#title' => t('laps'),
        '#cols' => 32, 
        '#required' => TRUE,
        '#default_value' => $values['laps']);

  $form['pitstops'] = array(
        '#type' => 'textfield', 
        '#title' => t('pitstops'),
        '#cols' => 32, 
        '#required' => TRUE,
        '#default_value' => $values['pitstops']);


  $form['confirmation_flags_options'] = array(
    '#type' => 'value',
    '#value' => _league_confirmation_flags_options()
  );

  $form['confirmation_flags_options'] = array(
    '#type' => 'select', 
    '#title' => t('Confirmation Flags'),
    '#default_value' => $values['confirmation_flags_options'],
    '#multiple' => TRUE,
    '#options' => $form['confirmation_flags_options']['#value']);

  $form['penalty'] = array(
        '#type' => 'textfield', 
        '#title' => t('penalty'),
        '#cols' => 32, 
        '#required' => FALSE,
        '#default_value' => $values['penalty']);

  $form['nickname'] = array(
        '#type' => 'textfield', 
        '#title' => t('nickname'),
        '#cols' => 32, 
        '#required' => FALSE,
        '#default_value' => $values['nickname']);

  $form['starting_position'] = array(
        '#type' => 'textfield', 
        '#title' => t('starting_position'),
        '#cols' => 32, 
        '#required' => FALSE,
        '#default_value' => $values['starting_position']);

  $form['car'] = array(
        '#type' => 'textfield', 
        '#title' => t('car'),
        '#cols' => 32, 
        '#required' => FALSE,
        '#default_value' => $values['car']);

  $form['plate'] = array(
        '#type' => 'textfield', 
        '#title' => t('plate'),
        '#cols' => 32, 
        '#required' => FALSE,
        '#default_value' => $values['plate']);


  $form['submit'] = array(
     '#type' => 'submit', 
     '#value' => t('Save'),
     '#default_value' => $values['name']);

  return $form;
}

function league_admin_results_values($id) {
  
  $result = db_query("SELECT *, results.id as result_id FROM {league_results} AS results, {league_drivers} AS drivers " . 
    "WHERE results.driver_id = drivers.id AND results.id=:id", array(':id' =>$id) );
    
  $values = array();

 foreach ($result as $row) {
	$values['result_id'] = $row->result_id;
    $values['raceEntry_id'] = $row->raceEntry_id;
    $values['driver_id'] = $row->driver_id;
    $values['position'] = $row->position;
    $values['race_time'] = $row->race_time;
    $values['fastest_lap'] = $row->fastest_lap;
    $values['laps'] = $row->laps;
    $values['pitstops'] = $row->pitstops;
    $values['confirmation_flags_options'] = _league_confirmation_flags_values($row->confirmation_flags);
    $values['nickname'] = $row->nickname;
    $values['starting_position'] = $row->starting_position;
    $values['car'] = $row->car;
    $values['plate'] = $row->plate;
  }
  return $values;
}


function league_results_edit_form_submit($form, &$form_state) {
  global $user;
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }
  
  $edit = $form_state['values'];
  

  
  if ($edit['result_id'] > 0) {
	  $fields = array(
			'raceEntry_id' => $edit['raceEntry_id'], 
			'driver_id' => $edit['driver_id'],
			'position' => $edit['position'], 
			'race_time' => $edit['race_time'], 
			'fastest_lap' => $edit['fastest_lap'],
			'laps' => $edit['laps'],
			'pitstops' => $edit['pitstops'],
			'confirmation_flags' => _league_confirmation_flags_value($edit['confirmation_flags_options']),
			'penalty' => $edit['penalty']
	  );
   
		db_update('league_results')
		  ->fields($fields)
		  ->condition('id', $edit['result_id'])
		  ->execute();

		$fieldsDriver = array(
		  'nickname' => $edit['nickname'], 
			'starting_position' => $edit['starting_position'],
			'car' => $edit['car'], 
			'plate' => $edit['plate']
		);
		db_update('league_drivers')
		  ->fields($fieldsDriver)
		  ->condition('id', $edit['driver_id'])
		  ->execute();
       
  }
  else
  {
    drupal_set_message(t('ID not found'));
  }
  
  $form_state['redirect'] =  "league/results/" . $edit['raceEntry_id'];
}


function league_admin_results_delete($form, &$form_state, $leagueId = NULL, $id = NULL) {  
  
  if (!isset($id)) {
    drupal_not_found();
    return;
  }

  $form = array();
  $form['id'] = array('#type' => 'value', '#value' => $id);
  $form['leagueId'] = array('#type' => 'value', '#value' => $leagueId);

  return confirm_form($form,
    t('Are you sure you want to delete this race entry?'),
    $_GET['destination'] ? $_GET['destination'] : 'admin/league/' . $leagueId . '/races/results',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
  
}


function league_admin_results_delete_submit($form, &$form_state) {
  
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }
	
	db_delete('league_races_entries')
	  ->condition('id', $form_state['values']['id'])
	  ->execute();
	
	db_delete('league_laps')
	  ->condition('raceEntry_id', $form_state['values']['id'])
	  ->execute();

	db_delete('league_drivers')
	  ->condition('raceEntry_id', $form_state['values']['id'])
	  ->execute();
	
	db_delete('league_results')
	  ->condition('raceEntry_id', $form_state['values']['id'])
	  ->execute();

  $form_state['redirect'] = 'admin/league/' . $form_state['values']['leagueId'] . '/races/results';
}

/*
function league_admin_results_add($id = NULL) {
 return drupal_get_form('league_admin_results_add_form', $id);
}
*/

