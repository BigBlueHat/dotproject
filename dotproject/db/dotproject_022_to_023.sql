#
# dotproject_022_to_023.sql 
#     Database Schema Update Script
#
# CHANGE LOG
#     creation by Andrew Eddie (25 Oct 2002) pre-alpha
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
