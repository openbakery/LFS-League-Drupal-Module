


SELECT league.name, races.name, races.date, entry.track, result.position
FROM oelfs_league_drivers AS driver, oelfs_league_races_entries AS entry, oelfs_league_results AS result, 
oelfs_league_races AS races, oelfs_league_leagues AS league
WHERE driver.raceEntry_id = entry.id AND result.raceEntry_id = entry.id AND entry.race_id = races.id 
AND driver.id = result.driver_id AND races.league_id = league.id AND result.confirmation_flags = 2
AND lfsworld_name = 'brilwing'
ORDER BY races.league_id, races.date;


SELECT entry.track
FROM oelfs_league_drivers AS driver, oelfs_league_races_entries AS entry, oelfs_league_results AS result
WHERE driver.raceEntry_id = entry.id AND result.raceEntry_id = entry.id AND lfsworld_name = 'brilwing';

SELECT entry.track
FROM oelfs_league_drivers AS driver, oelfs_league_races_entries AS entry, oelfs_league_results AS result
WHERE driver.raceEntry_id = entry.id AND lfsworld_name = 'brilwing';


-- RACECONTROL-SECTION: RACE
-- #Track; Laps; QualifyingMinutes; NumberRacers; Weather; Wind

-- RACECONTROL-SECTION: DRIVER
-- #LFSWorldName;Nickname;CarName;startingPosition;Plate

-- RACECONTROL-SECTION: RESULTS
-- #lfsWorldName;totalPosition;resultPosition;racetime;hours;bestLapTime;lapsCompleted;pitStops;flags;confirmationFlags

-- RACECONTROL-SECTION: LAPS
-- #number;time;split1;split2;split3;split4;overallTime;position;pit


DROP TABLE IF EXISTS oelfs_league_leagues;

CREATE TABLE oelfs_league_leagues (
  id int(11) NOT NULL auto_increment,
  name text NOT NULL,
  description text NOT NULL,
  servers int NOT NULL default 1,
  homepage text,
  rules_id int,
  PRIMARY KEY (id)
) TYPE=MyISAM AUTO_INCREMENT=0;


DROP TABLE IF EXISTS oelfs_league_races;

CREATE TABLE oelfs_league_races (
  id int(11) NOT NULL auto_increment,
  league_id int(11) NOT NULL,
  name text NOT NULL,
  date DATETIME,
  PRIMARY KEY (id)
) TYPE=MyISAM AUTO_INCREMENT=0;

DROP TABLE IF EXISTS oelfs_league_races_entries;

CREATE TABLE oelfs_league_races_entries (
  id int(11) NOT NULL auto_increment,
  race_id int(11) NOT NULL,
  track CHAR(3) NOT NULL,
  laps int(11),
  qualifing_minutes int(11),
  weather int(11),
  wind int(11),
  sprint int(11),
  server int(11),
  PRIMARY KEY (id)
) TYPE=MyISAM AUTO_INCREMENT=0;

DROP TABLE IF EXISTS oelfs_league_drivers;

CREATE TABLE oelfs_league_drivers (
  id int(11) NOT NULL auto_increment,
  raceEntry_id int(11) NOT NULL,
  uid int(11),
  lfsworld_name text NOT NULL,
  nickname text NOT NULL,
  starting_position int(11),
  car text,
  plate text,
  PRIMARY KEY(id)
) TYPE=MyISAM AUTO_INCREMENT=0;

DROP TABLE IF EXISTS oelfs_league_results;

CREATE TABLE oelfs_league_results (
  id int(11) NOT NULL auto_increment,
  raceEntry_id int(11) NOT NULL,
  driver_id int(11) NOT NULL,
  position int(11) NOT NULL,
  result_position int(11),
  race_time bigint(11),
  hours int(11),
  fastest_lap bigint(11),
  laps bigint(11),
  pitstops int(11),
  flags int(11),
  confirmation_flags int(11),
  PRIMARY KEY  (id)
) TYPE=MyISAM AUTO_INCREMENT=0;


DROP TABLE IF EXISTS oelfs_league_laps;

CREATE TABLE oelfs_league_laps (
  id int(11) NOT NULL auto_increment,
  driver_id int(11) NOT NULL,
  raceEntry_id int(11) NOT NULL,
  number int(11) NOT NULL,
  time bigint(11),
  split1 bigint(11), 
  split2 bigint(11), 
  split3 bigint(11), 
  split4 bigint(11),
  overallTime bigint(11),
  position int(11),
  pit int(1),
  PRIMARY KEY(id)
) TYPE=MyISAM AUTO_INCREMENT=0;

DROP TABLE IF EXISTS oelfs_league_rules;

CREATE TABLE  oelfs_league_rules (
  id int(11) NOT NULL auto_increment,
  main_race_points text NOT NULL,
  main_race_fastest_lap int(11) default '0',
  sprint_race_points text,
  sprint_race_fastest_lap int(11) default '0',
  name text NOT NULL,
  poleposition_points int(11) default '0',
  PRIMARY KEY  (id)
) ENGINE=MyISAM;



ALTER TABLE `oelfs_league_races_entries` MODIFY COLUMN `track` CHAR(3) NOT NULL DEFAULT '';
ALTER TABLE `oelfs_league_drivers` ADD INDEX `raceEntry_id_index`(`raceEntry_id`);
ALTER TABLE `oelfs_league_drivers` ADD INDEX `uid_index`(`uid`);
ALTER TABLE `oelfs_league_laps` ADD INDEX `driver_id_index`(`driver_id`), ADD INDEX `raceEntry_id_index`(`raceEntry_id`);
ALTER TABLE `oelfs_league_leagues` ADD INDEX `rules_id_index`(`rules_id`);
ALTER TABLE `oelfs_league_races` ADD INDEX `league_id_index`(`league_id`);
ALTER TABLE `oelfs_league_races_entries` ADD INDEX `race_id_index`(`race_id`);
ALTER TABLE `oelfs_league_results` ADD INDEX `driver_id_index`(`driver_id`), ADD INDEX `raceEntry_id_index`(`raceEntry_id`);
ALTER TABLE `oelfs_league_races_entries` ADD INDEX `laps_index`(`laps`);






