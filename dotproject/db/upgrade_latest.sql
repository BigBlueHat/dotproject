#
# $Id$
# 
# DO NOT USE THIS SCRIPT DIRECTLY - USE THE INSTALLER INSTEAD.
#
# All entries must be date stamped in the correct format.
#

# 20050516
UPDATE `config` SET `config_name` = 'admin_user' WHERE `config_name` = 'admin_username';

# 20050519
INSERT INTO `config` VALUES ('', 'task_reminder_control', 'false', 'task_reminder', 'checkbox');
INSERT INTO `config` VALUES ('', 'task_reminder_days_before', '1', 'task_reminder', 'text');
INSERT INTO `config` VALUES ('', 'task_reminder_repeat', '100', 'task_reminder', 'text');

# 20050603
# This seemed to have been lost in one of the other updates.
UPDATE `config` SET `config_name` = 'check_task_dates' WHERE `config_name` = 'check_tasks_dates';

# 20050620
# Adding new type for tasks collapse/expand
INSERT INTO `config` VALUES('', 'tasks_ajax_list', 'true', '', 'checkbox');

# 20050629
# New authentication method - HTTP Basic Auth
INSERT INTO config_list (`config_id`, `config_list_name`)
  SELECT config_id, 'http_ba'
	FROM config
	WHERE config_name = 'auth_method';
	

# 20050730
# Converting deprecated inactive projects to new dPsysVal '7|Archived'
UPDATE `sysvals` SET `sysval_value` = '0|Not Defined\n1|Proposed\n2|In Planning\n3|In Progress\n4|On Hold\n5|Complete\n6|Template\n7|Archived' WHERE `sysval_title` = 'ProjectStatus' LIMIT 1;
UPDATE `projects` SET `project_status` = '7' WHERE `project_active` = '0';
ALTER TABLE `projects` DROP `project_active`;

# 20050804
# fix for stale users in users access log when users dont logoff
ALTER TABLE `sessions` ADD `session_user` INT DEFAULT '0' NOT NULL AFTER `session_id` ;

# 20050807
# cookie session name as a config option
INSERT INTO `config` ( `config_id` , `config_name` , `config_value` , `config_group` , `config_type` )
VALUES ('', 'session_name', 'dotproject', 'session', 'text');

#20051005ge
#softcode for user_type
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` )
VALUES ( '', '1', 'UserType', '0|Default User 1|Administrator 2|CEO 3|Director 4|Branch Manager 5|Manager 6|Supervisor 7|Employee' );

#20051114
# webdav/webcal and icalendar functionality
CREATE TABLE `webcal_projects` (
  `webcal_id` int(11) NOT NULL default '0',
  `project_id` int(11) NOT NULL default '0',
  UNIQUE KEY `webcal_id` (`webcal_id`,`project_id`)
) ENGINE=MyISAM COMMENT='relate webcal resources to project calendars';

CREATE TABLE `webcal_resources` (
  `webcal_id` int(11) NOT NULL auto_increment,
  `webcal_path` varchar(255) default NULL,
  `webcal_port` tinyint(4) NOT NULL default '80',
  `webcal_owner` tinyint(11) NOT NULL default '0',
  `webcal_user` varchar(255) default NULL,
  `webcal_pass` varchar(255) default NULL,
  `webcal_auto_import` int(11) NOT NULL default '0',
  `webcal_auto_publish` tinyint(4) NOT NULL default '0',
  `webcal_auto_show` tinyint(4) NOT NULL default '0',
  `webcal_private_events` tinyint(4) NOT NULL default '0',
  `webcal_purge_events` tinyint(4) NOT NULL default '0',
  `webcal_preserve_id` tinyint(4) NOT NULL default '0',
  `webcal_eq_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`webcal_id`)
) ENGINE=MyISAM COMMENT='webcal resource management' AUTO_INCREMENT=29 ;

#20060203
# Indeces added for optimization purposes.
alter table user_tasks add index index_ut_to_tasks (task_id);
alter table history add index index_history_item (history_item);
