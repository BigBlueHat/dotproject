# $Id$
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
# ATTENTION:
# The following tables have been dropped from the schema
# Uncomment the lines to drop them if desired
#

#DROP TABLE `localization`;
#DROP TABLE `eventlog`;
#DROP TABLE `attendees`;
#DROP TABLE `attendees`;

#
# Structure for new table 'departments'
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


#
# Table changes 12 Dec 2002 (aje)
#
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
INSERT INTO user_preferences VALUES("0", "UISTYLE", "default");

#
# Table changes 16 Dec 2002
# Allowing forum_moderated field to hold the user id of the moderator
#
ALTER TABLE `forums` CHANGE `forum_moderated` `forum_moderated` INT DEFAULT "0" NOT NULL;

# AJE (2/Jan/2003): New preference
#INSERT INTO user_preferences VALUES("0", "UISTYLE", "default");

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

#
# AJE (24/Jan/2003)
# ---------
# N O T E !
#
# MODULES TABLE IS STILL IN DEVELOPMENT STAGE
#

#
# Table structure for table 'modules'
#
DROP TABLE IF EXISTS modules;
CREATE TABLE `modules` (
  `mod_id` int(11) NOT NULL auto_increment,
  `mod_name` varchar(64) NOT NULL default '',
  `mod_directory` varchar(64) NOT NULL default '',
  `mod_version` varchar(10) NOT NULL default '',
  `mod_setup_class` varchar(64) NOT NULL default '',
  `mod_type` varchar(64) NOT NULL default '',
  `mod_active` int(1) unsigned NOT NULL default '0',
  `mod_ui_name` varchar(20) NOT NULL default '',
  `mod_ui_icon` varchar(64) NOT NULL default '',
  `mod_ui_order` tinyint(3) NOT NULL default '0',
  `mod_ui_active` int(1) unsigned NOT NULL default '0',
  `mod_description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`mod_id`,`mod_directory`)
) TYPE=MyISAM;

#
# Dumping data for table 'modules'
#
INSERT INTO modules VALUES("1", "Companies", "companies", "1.0.0", "", "core", "1", "Companies", "money.gif", "1", "1", "");
INSERT INTO modules VALUES("2", "Projects", "projects", "1.0.0", "", "core", "1", "Projects", "projects.gif", "2", "1", "");
INSERT INTO modules VALUES("3", "Tasks", "tasks", "1.0.0", "", "core", "1", "Tasks", "tasks.gif", "3", "1", "");
INSERT INTO modules VALUES("4", "Calendar", "calendar", "1.0.0", "", "core", "1", "Calendar", "calendar.gif", "4", "1", "");
INSERT INTO modules VALUES("5", "Files", "files", "1.0.0", "", "core", "1", "Files", "folder.gif", "5", "1", "");
INSERT INTO modules VALUES("6", "Contacts", "contacts", "1.0.0", "", "core", "1", "Contacts", "contacts.gif", "6", "1", "");
INSERT INTO modules VALUES("7", "Forums", "forums", "1.0.0", "", "core", "1", "Forums", "communicate.gif", "7", "1", "");
INSERT INTO modules VALUES("8", "Tickets", "ticketsmith", "1.0.0", "", "core", "1", "Tickets", "ticketsmith.gif", "8", "1", "");
INSERT INTO modules VALUES("9", "User Administration", "admin", "1.0.0", "", "core", "1", "User Admin", "admin.gif", "9", "1", "");
INSERT INTO modules VALUES("10", "System Administration", "system", "1.0.0", "", "core", "1", "System Admin", "system.gif", "10", "1", "");
INSERT INTO modules VALUES("11", "Departments", "departments", "1.0.0", "", "core", "1", "Departments", "users.gif", "11", "0", "");
INSERT INTO modules VALUES("12", "Help", "help", "1.0.0", "", "core", "1", "Help", "dp.gif", "12", "0", "");
INSERT INTO modules VALUES("13", "Public", "public", "1.0.0", "", "core", "1", "Public", "users.gif", "13", "0", "");

#
#  Alter tasks table 1/February/2003
#
ALTER TABLE `tasks` ADD `task_duration_type` VARCHAR(6)  DEFAULT 'hours' NOT NULL AFTER task_duration;

#
# ! WARNING !
# BACKUP DATA BEFORE APPLYING THE NEXT UPDATE INSTRUCTIONS
# UNCOMMENT AND APPLY WHEN SAFE

# UPDATE tasks SET task_duration_type = 'days' WHERE task_duration >= 24.0;
# UPDATE tasks SET task_duration = task_duration/24.0 WHERE task_duration >= 24.0;

# AJE (17/Feb/2003)

#
# Table structure for table 'syskeys'
#

DROP TABLE IF EXISTS syskeys;
CREATE TABLE `syskeys` (
  `syskey_id` int(10) unsigned NOT NULL auto_increment,
  `syskey_name` varchar(48) NOT NULL default '',
  `syskey_label` varchar(255) NOT NULL default '',
  `syskey_type` int(1) unsigned NOT NULL default '0',
  `syskey_sep1` char(2) default '\n',
  `syskey_sep2` char(2) NOT NULL default '|',
  PRIMARY KEY  (`syskey_id`),
  UNIQUE KEY `idx_syskey_name` (`syskey_id`)
) TYPE=MyISAM;

#
# Table structure for table 'sysvals'
#

DROP TABLE IF EXISTS sysvals;
CREATE TABLE sysvals (
  sysval_id int(10) unsigned NOT NULL auto_increment,
  sysval_key_id int(10) unsigned NOT NULL default '0',
  sysval_title varchar(48) NOT NULL default '',
  sysval_value text NOT NULL,
  PRIMARY KEY  (sysval_id)
) TYPE=MyISAM;

#
# Table structure for table 'sysvals'
#

INSERT INTO syskeys VALUES("1", "SelectList", "Enter values for list", "0", "\n", "|");
INSERT INTO sysvals VALUES("1", "1", "ProjectStatus", "0|Not Defined\r\n1|Proposed\r\n2|In Planning\r\n3|In Progress\r\n4|On Hold\r\n5|Complete");

#
# Add "is provider" flag
# and "email" in companies table
# (22/Feb/2003)
#

ALTER TABLE companies ADD COLUMN company_type INT(3) NOT NULL DEFAULT 0;
ALTER TABLE companies ADD COLUMN company_email varchar(30);

INSERT INTO sysvals (sysval_key_id,sysval_title,sysval_value) VALUES("1", "CompanyType", "0|Not Applicable\n1|Client\n2|Vendor\n3|Supplier\n4|Consultant\n5|Government\n6|Internal");
# ROLES TABLES: AJE 26/Feb/2003

#
# Table structure for table 'roles'
#

DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
  role_id int(10) unsigned NOT NULL auto_increment,
  role_name varchar(24) NOT NULL default '',
  role_description varchar(255) NOT NULL default '',
  role_type int(3) unsigned NOT NULL default '0',
  role_module int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (role_id)
) TYPE=MyISAM;

#
# Table structure for table 'user_roles'
#

DROP TABLE IF EXISTS user_roles;
CREATE TABLE user_roles (
  user_id int(10) unsigned NOT NULL default '0',
  role_id int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;

# 28/Feb/2003 eddieajau
# Give company address a bit more room
ALTER TABLE `companies` CHANGE `company_address1` `company_address1` VARCHAR(50) DEFAULT "";
ALTER TABLE `companies` CHANGE `company_address2` `company_address2` VARCHAR(50) DEFAULT "";

# 19/Mar/2003 eddieajau
# Alterations to the task log table
# This adds better information capture/support for other pluggins to use information

ALTER TABLE `task_log` ADD `task_log_hours` FLOAT DEFAULT "0" NOT NULL;
ALTER TABLE `task_log` ADD `task_log_date` DATETIME;
ALTER TABLE `task_log` ADD `task_log_costcode` VARCHAR(8) NOT NULL default '';
ALTER TABLE `task_log` DROP `task_log_parent`;

# copy across task comments to task log table
INSERT INTO task_log (task_log_task, task_log_name, task_log_description, task_log_creator, task_log_date)
SELECT comment_task, comment_title, comment_body, comment_user, comment_date FROM task_comments;

# uncomment when satisfied data has been copied successfully
#DROP TABLE task_comments;

# fix mis-spelt field
ALTER TABLE `tasks` CHANGE `task_precent_complete` `task_percent_complete` TINYINT(4)  DEFAULT "0";
ALTER TABLE `projects` CHANGE `project_precent_complete` `project_percent_complete` TINYINT(4)  DEFAULT "0";

