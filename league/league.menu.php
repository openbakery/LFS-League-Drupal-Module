<?php


function league_menu() {
  
  $items = array();
  $items['league'] = array(
    'title' => 'Leagues',
    'page callback' => 'league',
    'access arguments' => array('access league content'),
  );

  $items['league/results/%'] = array(
    'title' => 'Result',
    'page callback' => 'league_results',
    'page arguments' => array(2),
    'access arguments' => array('access league content'),
  );


  $items['league/%/standings'] = array(
    'title' => 'Standings',
    'page callback' => 'league_standings',
    'page arguments' => array(1),
    'access arguments' => array('access league content'),
    'file' => 'league.standings.php'
  );

  $items['league/%/standings/rookies'] = array(
    'page callback' => 'league_standings_rookies',
    'page arguments' => array(1),
    'title' => 'Rookie Standings',
    'access arguments' => array('access league content'),
    'type' => MENU_CALLBACK,
    'file' => 'league.standings.php'
  );  

  $items['league/teams/standings'] = array(
    'access arguments' => array('access league content'),
    'page callback' => 'league_teams_standings',
    'title' => 'Team Standings',
    'type' => MENU_CALLBACK
  );

  $items['league/%/races'] = array(
    'access arguments' => array('access league content'),
    'page callback' => 'league_races',
    'page arguments' => array(1),
    'title' => 'Races',
    'file' => 'league.races.php',
    'type' => MENU_CALLBACK
  );

  $items['league/driver/detail'] = array(
    'access arguments' => array('access league content'),
    'page callback' => 'league_driver_detail',
    'title' => 'Driver Detail',
    'type' => MENU_CALLBACK
  );

//************************************************************
//  ADMIN
//*************************************************************

  $items['admin/settings/league'] = array(
    'title' => t('League module settings'),
    'description' => t('League module settings'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin'),
    'access arguments' => array('administer league'),
   );

//************************************************************
//  ADMIN LEAGUE
//*************************************************************
  $items['admin/league'] = array(
    'access callback' => 'user_access',
    'access arguments' => array('administer league'),
    'page callback' => 'league_admin_leagues',
    'title' => 'Leagues',
    'file' => 'league.admin.league.php'
  );


  $items['admin/league/list'] = array(
    'title' => 'List',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'access callback' => 'user_access',
    'access arguments' => array('administer league'),
    'weight' => -10,
    'file' => 'league.admin.league.php'
  );


  $items['admin/league/add'] = array(
    'title' => 'Add',
    'access callback' => 'user_access',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_form'),
    'file' => 'league.admin.league.php',
	'type' => MENU_LOCAL_TASK
  );

  $items['admin/league/%/delete'] = array(
    'title' => 'Delete',
    'access callback' => 'user_access',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_delete', 2),
    'file' => 'league.admin.league.php',
		'type' => MENU_CALLBACK
  );

  $items['admin/league/%/edit'] = array(
    'title' => 'Edit',
    'access callback' => 'user_access',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_form', 2, 3),
    'file' => 'league.admin.league.php',
    'type' => MENU_LOCAL_TASK,
    'weight' => 1
  );

//************************************************************
//  ADMIN RULES
//*************************************************************

  $items['admin/league/rules'] = array(
    'access arguments' => array('administer league'),
    'page callback' => 'league_admin_leagues_rules',
    'title' => 'Rules',
		'type' => MENU_LOCAL_TASK,
		'file' => 'league.admin.rules.php'
  );
  
  $items['admin/league/rules/list'] = array(
    'title' => 'List',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
		'file' => 'league.admin.rules.php'
  );
  

  $items['admin/league/rules/add'] = array(
    'title' => 'Add league rules',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_rules_form'),
    'type' => MENU_LOCAL_TASK,
		'file' => 'league.admin.rules.php'
  );

  $items['admin/league/rules/%/delete'] = array(
    'title' => 'Delete League Rules',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_rules_delete', 3),
    'type' => MENU_CALLBACK,
		'file' => 'league.admin.rules.php'
  );

  $items['admin/league/rules/%/edit'] = array(
    'title' => 'Edit a league rules',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_rules_form', 3),
    'type' => MENU_CALLBACK,
		'file' => 'league.admin.rules.php'
  );

//************************************************************
//  ADMIN RACES
//************************************************************

  $items['admin/league/%'] = array(
    'title' => 'Races',
    'access arguments' => array('administer league'),
    'page callback' => 'league_admin_leagues_races',
    'page arguments' => array(2),
    'file' => "league.admin.race.php",
    'type' => MENU_CALLBACK                    
  );

  $items['admin/league/%/list'] = array(
    'title' => 'List',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
    'page arguments' => array(2),
    'file' => 'league.admin.league.php',
    'weight' => 0
  );


//************************************************************
//  ADMIN RACES ADD - EDIT - DELETE
//************************************************************

  $items['admin/league/%/races/add'] = array(
    'title' => 'Add Race',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_races_form', 2),
    'type' => MENU_LOCAL_TASK,
    'file' => "league.admin.race.php",
    'weight' => 5
  );
  $items['admin/league/%/races/%/delete'] = array(
    'title' => 'League races',
    'access arguments' => array('administer league'),
    'type' => MENU_CALLBACK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_races_delete', 2, 4),
    'file' => "league.admin.race.php"
  );

  $items['admin/league/%/races/%/edit'] = array(
    'title' => 'Edit a league races',
    'access arguments' => array('administer league'),
    'type' => MENU_CALLBACK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_leagues_races_form', 2, 4),
    'file' => "league.admin.race.php"
  );


//************************************************************
//  ADMIN UPLOAD
//************************************************************

  $items['admin/league/%/upload'] = array(
    'title' => 'Upload Racecontrol file',
    'access arguments' => array('administer league'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_upload_form', 2),
    'type' => MENU_LOCAL_TASK,
    'file' => "league.admin.upload.php",
    'weight' => 6
    
  );

//************************************************************
//  ADMIN RACES RESULTS
//************************************************************
  
  $items['admin/league/%/races/results'] = array(
    'title' => 'Results',
    'access arguments' => array('administer league'),
    'page callback' => 'league_admin_races_results',
    'page arguments' => array(2),
    'file' => "league.admin.results.php",
    'type' => MENU_LOCAL_TASK                     
  );
  
  
//  $items['league/results/%result/lfsworld'] = array(
//    'access arguments' => array('administer league'),
//    'page callback' => 'league_results_lfsworld',
//    'title' => 'Results',
//    'type' => MENU_CALLBACK,
//    'file' => 'league.result.admin.php'
//  );

  $items['admin/league/%/races/results/%/delete'] = array(
    'access arguments' => array('administer league'),
    'title' => 'Delete results',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_results_delete', 2, 5),
    'file' => 'league.admin.results.php',
  );
  

  $items['admin/league/%/races/results/%/edit'] = array(
    'title' => 'Edit result',
    'access arguments' => array('administer league'),
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_results_edit_form', 2, 5),
    'file' => 'league.admin.results.php'
  );



//************************************************************
//  ADMIN TEAMS
//************************************************************

  $items['admin/league/%/teams'] = array(
    'access arguments' => array('administer league'),
    'page callback' => 'league_admin_teams',
    'page arguments' => array(2),
    'title' => 'Teams',
    'type' => MENU_LOCAL_TASK,
    'file' => 'league.admin.teams.php',
    'weight' => 6
    );

  $items['admin/league/%/teams/list'] = array(
    'title' => 'List',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
		'file' => 'league.admin.teams.php'
  );


  $items['admin/league/%/teams/add'] = array(
    'title' => 'Add league team',
    'access arguments' => array('administer league'),
    'type' => MENU_LOCAL_TASK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_teams_form', 2),
    'file' => 'league.admin.teams.php'
  );



  $items['admin/league/%/teams/%/delete'] = array(
    'title' => 'League Teams',
    'access arguments' => array('administer league'),
    'type' => MENU_CALLBACK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_teams_delete', 2, 4),
    'file' => 'league.admin.teams.php'
  );

  $items['admin/league/%/teams/%/edit'] = array(
    'title' => 'Edit a league teams',
    'access arguments' => array('administer league'),
    'type' => MENU_CALLBACK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_teams_form', 2, 4),
    'file' => 'league.admin.teams.php'
  );

  $items['admin/league/%/teams/%'] = array(
    'access arguments' => array('administer league'),
    'page callback' => 'league_admin_teams_drivers',
    'page arguments' => array(2, 4),
    'type' => MENU_CALLBACK,
    'title' => 'Team Drivers',
    'file' => 'league.admin.teams.php'
	);
	
	$items['admin/league/%/teams/%/list'] = array(
    'title' => 'List',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
		'file' => 'league.admin.teams.php'
  );
  
  $items['admin/league/%/teams/%/add'] = array(
    'access arguments' => array('administer league'),
    'type' => MENU_LOCAL_TASK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_teams_drivers_form', 2, 4),
    'title' => 'Add driver to team',
    'file' => 'league.admin.teams.php'
  );
  
  $items['admin/league/%/teams/%/%/edit'] = array(
    'access arguments' => array('administer league'),
    'type' => MENU_CALLBACK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_teams_drivers_form', 2, 4, 5),
    'title' => 'Edit a driver of a team',
    'file' => 'league.admin.teams.php'
  );

  $items['admin/league/%/teams/%/%/delete'] = array(
    'access arguments' => array('administer league'),
    'type' => MENU_CALLBACK,
    'page callback' => 'drupal_get_form',
    'page arguments' => array('league_admin_teams_drivers_delete', 2, 4, 5),
    'title' => 'Remove a driver from a team',
    'file' => 'league.admin.teams.php'
    );
  return $items;
}


