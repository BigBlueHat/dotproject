#
# dotproject_022_to_023.sql 
#     Database Schema Update Script
#
# CHANGE LOG
#     creation by Andrew Eddie (25 Oct 2002) pre-alpha
#     updated by J. Christopher Pereira (29 Nov 2002)
# 
# Use this schema for updating version 022 to 023
# 
# WARNING:
# This file may be in a state of development flux at the moment.
# Watch out for changes (see above)
#

#
# Table structure for table 'departments'
#

CREATE TABLE departments (
  dept_id int(10) unsigned NOT NULL auto_increment,
  dept_parent int(10) unsigned NOT NULL default '0',
  dept_company int(10) unsigned NOT NULL default '0',
  dept_name tinytext NOT NULL,
  dept_phone varchar(30) default NULL,
  dept_fax varchar(30) default NULL,
  dept_address1 varchar(30) default NULL,
  dept_address2 varchar(30) default NULL,
  dept_city varchar(30) default NULL,
  dept_state varchar(30) default NULL,
  dept_zip varchar(11) default NULL,
  dept_url varchar(25) default NULL,
  dept_desc mediumtext,
  dept_owner int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (dept_id),
  UNIQUE KEY dept_id (dept_id),
  KEY dept_id_2 (dept_id)
) TYPE=MyISAM COMMENT='Department heirarchy under a company';

#
# Table structure for table 'forum_watch'
#

CREATE TABLE forum_watch (
  watch_user int(10) unsigned NOT NULL default '0',
  watch_forum int(10) unsigned default NULL,
  watch_topic int(10) unsigned default NULL
) TYPE=MyISAM COMMENT='Links users to the forums/messages they are watching';

#
# Addition to the forums table to store the id of the last post
# This will mean the forum_last_date is deprecated
#
ALTER TABLE `forums` ADD `forum_last_id` INT UNSIGNED DEFAULT "0" NOT NULL AFTER `forum_last_date`;

#
# Addition to the PROJECTS table to associate a project to a company department
#
ALTER TABLE `projects` ADD `project_department` INT UNSIGNED DEFAULT "0" NOT NULL AFTER `project_company`;

#
# Minor change to the TASKS table to allow for part hours
#
ALTER TABLE `tasks` CHANGE `task_hours_worked` `task_hours_worked` FLOAT DEFAULT "0";

#
# Change to the USERS table for the new departments module
# and allow user defined user types
#
ALTER TABLE `users` ADD `user_department` INT UNSIGNED DEFAULT "0" NOT NULL AFTER `user_company`;
ALTER TABLE `users` CHANGE `user_type` `user_type` TINYINT UNSIGNED DEFAULT "0" NOT NULL;

#
# Events table
#
# The event_project field deprecates the event_parent field
# event_parent is maintained for the moment to prevent errors
#
ALTER TABLE `events` ADD `event_owner` INT UNSIGNED DEFAULT "0";
ALTER TABLE `events` ADD `event_project` INT UNSIGNED DEFAULT "0";
ALTER TABLE `events` ADD `event_private` TINYINT UNSIGNED DEFAULT "0";

#
# Task dependencies table
#
CREATE TABLE task_dependencies (
	dependencies_task_id int(11) NOT NULL,
	dependencies_req_task_id int(11) NOT NULL,
	PRIMARY KEY (dependencies_task_id, dependencies_req_task_id)
);

#
# Change to TASKS table for the new dynamic task flag
#
ALTER TABLE tasks ADD task_dynamic tinyint(1) NOT NULL default 0;

#
# Prepare support for user localisation
#

DROP TABLE `localization`

#
# Table changes 12 Dec 2002 (aje)
#
ALTER TABLE `users` DROP `user_locale`

DROP TABLE IF EXISTS user_preferences;
CREATE TABLE `user_preferences` (
  `pref_user` varchar(12) NOT NULL default '',
  `pref_name` varchar(12) NOT NULL default '',
  `pref_value` varchar(32) NOT NULL default '',
  KEY `pref_user` (`pref_user`,`pref_name`)
) TYPE=MyISAM;

#
# Dumping data for table 'user_preferences'
#
INSERT INTO user_preferences VALUES("0", "LOCALE", "en");
INSERT INTO user_preferences VALUES("0", "TABVIEW", "0");
INSERT INTO user_preferences VALUES("0", "SHDATEFORMAT", "%d/%m/%Y");

#
# Table changes 16 Dec 2002
# Allowing forum_moderated field to hold the user id of the moderator
#
ALTER TABLE `forums` CHANGE `forum_moderated` `forum_moderated` INT DEFAULT "0" NOT NULL

# AJE (2/Jan/2003): New preference 
INSERT INTO user_preferences VALUES("0", "UISTYLE", "default");

#
# AJE (4/Jan/2003)
#

#
# Contacts table
#
ALTER TABLE `contacts` ADD `contact_owner` INT UNSIGNED DEFAULT "0";
ALTER TABLE `contacts` ADD `contact_private` TINYINT UNSIGNED DEFAULT "0";

#
# Projects table
#
ALTER TABLE `projects` ADD `project_private` TINYINT UNSIGNED DEFAULT "0";

#
# Users table
#
ALTER TABLE `users` CHANGE `signature` `user_signature` TEXT;

#
# AJE (6/Jan/2003)
#
INSERT INTO user_preferences VALUES("0", "TIMEFORMAT", "%I:%M %p");
