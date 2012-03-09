<?php


function league_admin_leagues() {
  if (!user_access('administer league')) {
    drupal_access_denied();  
    return;
  }

  $leagues = _league_fetch_leagues();

  $content .= '<table border="0" class="league">';
  $content .= '<tr><th>' . t('Name') . '</th><th>' . t('Description') . '</th><th>' . ('Rules') . '</th><th>' . t('Homepage') . '</th><th>&nbsp;</th></tr>';

  $i=0;
  foreach ($leagues as $league) {
  if ( ($i%2) == 0) {
      $tdClass = "league-even";
    } 
    else {
      $tdClass = "league-odd";
    }

    $line = sprintf('<tr class="%s"><td><a href="?q=admin/league/%d/races">%s</a></td><td>%s</td><td>%s</td><td><a href="%s">%s</a></td><td><a href="?q=admin/league/%d/edit">' . t('Edit') . '</a></td></tr>',
      $tdClass,
      $league->id,
      $league->name,
      $league->description,
      "rules",
      $league->homepage,
      $league->homepage,
      $league->id,
      $league->id);

    $content .= $line;
  }
  $content .= '</table>';
  return $content;  
}

/*
function league_admin_leagues_add($id = NULL) {
 return drupal_get_form('league_admin_leagues_form', $id);
}
*/

function league_admin_leagues_form($form, &$form_state, $id = NULL) {
  
  if (isset($id)) {
    $values = league_admin_leagues_values($id);

    if ($_POST['op'] == t('Delete')) {
      drupal_goto('admin/league/' . $id . '/delete');
    }

  }

  $form = array();
  
  $form['name'] = array(
    '#type' => 'textfield', 
    '#title' => t('Name'),
    '#cols' => 32, 
    '#required' => TRUE,
    '#default_value' => $values['name']);
  
  $form['description'] = array(
    '#type' => 'textfield', 
    '#title' => t('Description'),
    '#cols' => 40,
    '#default_value' => $values['description']);
    
  $result = db_query("SELECT * FROM {league_rules}");
  $rules = array();
  foreach ($result as $row) {
    $rules[$row->id] = $row->name;
  }  
  
  $form['rules'] = array(
    '#type' => 'select', 
    '#title' => t('Rules'),
    '#required' => TRUE,
    '#default_value' => $values['rules'],
    '#options' => $rules);
    
  $form['servers'] = array(
    '#type' => 'textfield', 
    '#title' => t('Servers'),
    '#required' => TRUE,
    '#default_value' => $values['servers']);

  $form['number_counting_results'] = array(
      '#type' => 'textfield', 
      '#title' => t('Number of counting results'),
      '#required' => TRUE,
      '#default_value' => $values['number_counting_results']);

  $form['homepage'] = array(
    '#type' => 'textfield', 
    '#title' => t('Homepage'),
    '#cols' => 40,
    '#default_value' => $values['homepage']);


  $form['rookies'] = array(
     '#type' => 'textfield', 
      '#title' => t('Rookies'),
      '#cols' => 100,
      '#default_value' => $values['rookies']);
      
  $form['name_pattern'] = array(
    '#type' => 'textfield', 
    '#title' => t('Pattern to replace the lfs world name.'), 
    '#default_value' => $values['name_pattern'], 
    '#description' => t("The profile field names must here be in {}"), 
    '#maxlength' => '200', '#size' => '30');
        

    $form['block_name_pattern'] = array(
      '#type' => 'textfield', 
      '#title' => t('Pattern to replace the lfs world name in the standings block'), 
      '#default_value' => $values['block_name_pattern'], 
      '#description' => t("The profile field names must here be in {}"), 
      '#maxlength' => '200', '#size' => '30');
    
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

function league_admin_leagues_form_validate($form, &$form_state) {
  if ( !is_numeric($form_state['values']['servers']) ) {
    form_set_error('servers', t('The servers must be a numeric value'));
  }

  if ( !is_numeric($form_state['values']['number_counting_results']) ) {
    form_set_error('number_counting_results', t('The "Number of counting results" must be a numeric value'));
  }
}


function league_admin_leagues_form_submit($form, &$form_state) {
  global $user;
  if (!user_access('administer league')) {
    drupal_access_denied();
    return;
  }
  
  $edit = $form_state['values'];
  
	$fields = array(
		'name' => $edit['name'],
		'description' => $edit['description'],
		'homepage' => $edit['homepage'], 
		'rules_id' => $edit['rules'], 
		'servers' => $edit['servers'], 
		'rookies' => $edit['rookies'], 
		'number_counting_results' => $edit['number_counting_results'], 
		'name_pattern' => $edit['name_pattern'], 
		'block_name_pattern' => $edit['block_name_pattern']
	);
	
  if ($edit['id'] > 0) {
		db_update('league_leagues')
			->fields($fields)
			->condition('id', $edit['id'])
			->execute();	
  } else {
			db_insert('league_leagues')
				->fields($fields)
				->execute();	
  }
  $form_state['redirect'] = 'admin/league';
}

function league_admin_leagues_delete($form, &$form_state, $id = NULL) {
  if (!isset($id)) {
    drupal_not_found();
    return;
  }

  $form = array();
  $form['id'] = array('#type' => 'value', '#value' => $id);

  return confirm_form($form,
    t('Are you sure you want to delete this league entry?'),
    $_GET['destination'] ? $_GET['destination'] : 'admin/league',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
}


function league_admin_leagues_delete_submit($form, &$form_state) {
  
  if (!user_access('administer league')) {
    drupal_access_denied();  
    return;
  }
	
	db_delete('league_leagues')
		->condition('id', $form_state['values']['id'])
		->execute();

  $form_state['redirect'] = 'admin/league';
}

function league_admin_leagues_values($id = -1) {
  $values = array();
  
  if ($id > 0) {
    $result = db_query("SELECT * FROM {league_leagues} WHERE id = :id ", array(':id' => $id) );

    foreach ($result as $row) {
      $values['id'] = $row->id;
      $values['name'] = $row->name;
      $values['description'] = $row->description;
      $values['homepage'] = $row->homepage;
      $values['rules'] = $row->rules_id;
      $values['servers'] = $row->servers;
      $values['rookies'] = $row->rookies;
      $values['number_counting_results'] = $row->number_counting_results;
      $values['name_pattern'] = $row->name_pattern;
      $values['block_name_pattern'] = $row->block_name_pattern;
    }
  }
  return $values;
}
