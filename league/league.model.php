<?php 

class League {
  public $id;
  public $name;
  public $description;
  public $homepage;
  public $ruleId;
  public $rookies;
  public $numberCountingResults;
  public $namePattern;
  public $blockNamePattern;

  public function __construct($id = -1, $name ="", $description = "", $homepage ="", $ruleId = -1, $rookies = "", $numberCountingResults = "", $namePattern = "", $blockNamePattern = "") {

    $this->id = $id;
    $this->name = $name;
    $this->description = $description;
    $this->homepage = $homepage;
    $this->ruleId = $ruleId;
    $this->rookies = $rookies;
    $this->numberCountingResults = $numberCountingResults;
    $this->namePattern = $namePattern;
    $this->blockNamePattern = $blockNamePattern;
  }
  
}

class Rule
{
  public $id;
  public $name;
  public $main_race_points;
  public $main_race_fastest_lap;
  public $sprint_race_points;
  public $sprint_race_fastest_lap;
  public $poleposition_points;
  public $sprint_poleposition_points;

  public function __construct(
    $id = -1,
    $name = "",
    $main_race_points = "",
    $main_race_fastest_lap = 0,
    $sprint_race_points = "",
    $sprint_race_fastest_lap = 0,
    $poleposition_points = 0,
    $sprint_poleposition_points = 0)
  {
    $this->id = $id;
    $this->name = $name;
    $this->main_race_points = $main_race_points;
    $this->main_race_fastest_lap = $main_race_fastest_lap;
    $this->sprint_race_points = $sprint_race_points;
    $this->sprint_race_fastest_lap = $sprint_race_fastest_lap;
    $this->poleposition_points = $poleposition_points;
    $this->sprint_poleposition_points = $sprint_poleposition_points;
  }
}

class Driver {
  public $uid;
  public $name;
  public $lfsworldName;
  public $id;
    
  public function __construct($uid = -1, $name = "", $lfsworldName) {
    $this->uid = $uid;
    $this->name = $name;
    $this->lfsworldName = $lfsworldName;
  }
  
  public function __toString() {
    if ($this->uid > 0) {
      return '<a href="?q=user/' . $this->uid . '">' . $this->name . '</a>';
    }
    return $this->name;
  }
}

class Result {
  
  public $id;
  public $position;
  public $driver;
  public $car;
  public $time;
  public $fastestLap;
  public $laps;
  public $pitstops;
  public $points;
  public $confirmationFlags;
  public $hasFastestLap;
  public $hasPolePosition;
  public $penalty;
  
  public function __construct($driver = null) {
    $this->driver = $driver;
  }
  
  public function __toString() {
    $result = "Result[";
    $result .= "id: " . $this->id . ", ";
    $result .= "position: " . $this->position . ", ";
    $result .= "driver: " . $this->driver . ", ";
    $result .= "car: " . $this->car . ", ";
    $result .= "time: " . $this->time . ", ";
    $result .= "fastestLap: " . $this->fastestLap . ", ";
    $result .= "laps: " . $this->laps . ", ";
    $result .= "pitstops: " . $this->pitstops . ", ";
    $result .= "points: " . $this->points . ", ";
    $result .= "confirmationFlags: " . $this->confirmationFlags . ", ";
    $result .= "hasFastestLap: " . $this->hasFastestLap . ", ";
    $result .= "hasPolePosition: " . $this->hasPolePosition . ", ";
    $result .= "penalty: " . $this->penalty;
    $result .= "]";
    return $result;
  }
}

class Race {
  public $id;
  public $name;
  public $date;
  public $leagueId;
  
  public function __construct($id = null, $name = null, $date = null, $leagueId = null) {
    $this->id = $id;
    $this->name = $name;
    $this->date = $date;
    $this->leagueId = $leagueId;
  }
  
  public function __toString() {
    $result = "Race[";
    $result .= "id: " . $this->id . ", ";
    $result .= "name: " . $this->name . ", ";
    $result .= "date: " . $this->date . ", ";
    $result .= "leagueId: " . $this->leagueId . "]";
    return $result;
  }
  
}

class RaceEntry {
  public $id;
  public $entryId;
  public $leagueId;
  public $name;
  public $date;
  public $track;
  public $laps;
  public $qualifingMinutes;
  public $weather;
  public $wind;
  public $type;
  public $server;
  
  public function __construct($id = null, $entryId = null, $leagueId = null) {
    $this->id = $id;
    $this->entryId = $entryId;
    $this->leagueId = $leagueId;
  }
  
  public function __toString() {
    $result = "RaceEntry[";
    $result .= "id: " . $this->id . ", ";
    $result .= "entryId: " . $this->entryId . ", ";
    $result .= "leagueId: " . $this->leagueId . ", ";
    $result .= "name: " . $this->name . ", ";
    $result .= "date: " . $this->date . ", ";
    $result .= "track: " . $this->track . ", ";
    $result .= "laps: " . $this->laps . ", ";
    $result .= "qualifingMinutes: " . $this->qualifingMinutes . ", ";
    $result .= "weather: " . $this->weather . ", ";
    $result .= "wind: " . $this->wind . ", ";
    $result .= "type: " . $this->type . ", ";
    $result .= "server: " . $this->server . ", ";
  
    $result .= "]";
    return $result;
  }
}

?>