<?php

function _league_fetch_leagues()
{
  $queryResult = db_query("SELECT * FROM {league_leagues}");
  
  $result = array();
  foreach ($queryResult as $row) {
    $result[$row->id] = new League(
      $row->id, 
      $row->name, 
      $row->description, 
      $row->homepage, 
      $row->rules_id, 
      $row->rookies, 
      $row->numberCountingResults, 
      $row->namePattern, 
      $row->blockNamePattern);
  }
  return $result;
}

function _league_fetch_rules()
{
  $queryResult = db_query("SELECT * FROM {league_rules}");
  $result = array();
  foreach ($queryResult as $row) {
    $result[$row->id] = new Rule(
    $row->id,
    $row->name,
    $row->main_race_points,
    $row->main_race_fastest_lap,
    $row->sprint_race_points,
    $row->sprint_race_fastest_lap,
    $row->poleposition_points,
    $row->sprint_poleposition_points);
  }
  return $result;
}

function _league_fetch_races($leagueId) {
  $result = db_query("SELECT * FROM {league_races} WHERE league_id = :leagueId ORDER BY date", array('leagueId' => $leagueId) );
  $races = array();
  foreach ($result as $row) {
    $races[$row->id] = new Race($row->id, $row->name, $row->date, $row->league_id);
  }
  return $races;
}


function _league_fetch_race_entry($id) {
  $query = "SELECT * FROM {league_races} AS races, {league_races_entries} as entries WHERE entries.id = :id AND entries.race_id = races.id";
  $result = db_query($query, array(':id' => $id));

  foreach ($result as $row) {
    $race = new RaceEntry($row->race_id, $row->id, $row->league_id);
    $race->name = $row->name;
    $race->data = $row->date;
    $race->track = $row->track;
    $race->laps = $row->laps;
    $race->qualifingMinutes = $row->qualifing_minutes;
    $race->weather = $row->weather;
    $race->wind = $row->wind;
    $race->type = $row->type;
    $race->server = $row->server;
    return $race;
  }
  
  drupal_set_message(t('No race found for id: %id', array('%id' => $id)));

  return null;
}


