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
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES (null, 'task_reminder_control', 'false', 'task_reminder', 'checkbox');
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES (null, 'task_reminder_days_before', '1', 'task_reminder', 'text');
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES (null, 'task_reminder_repeat', '100', 'task_reminder', 'text');

# 20050603
# This seemed to have been lost in one of the other updates.
UPDATE `config` SET `config_name` = 'check_task_dates' WHERE `config_name` = 'check_tasks_dates';

# 20050620
# Adding new type for tasks collapse/expand
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES (null, 'tasks_ajax_list', 'true', '', 'checkbox');

# 20050629
# New authentication method - HTTP Basic Auth
INSERT INTO config_list (`config_id`, `config_list_name`)
  SELECT config_id, 'http_ba'
	FROM config
	WHERE config_name = 'auth_method';
	

# 20050730
# Converting deprecated inactive projects to new dPsysVal '7|Archived'
#UPDATE `sysvals` SET `sysval_value` = '0|Not Defined\n1|Proposed\n2|In Planning\n3|In Progress\n4|On Hold\n5|Complete\n6|Template\n7|Archived' WHERE `sysval_title` = 'ProjectStatus' LIMIT 1;
#UPDATE `projects` SET `project_status` = '7' WHERE `project_active` = '0';
#ALTER TABLE `projects` DROP `project_active`;

# 20050804
# fix for stale users in users access log when users dont logoff
ALTER TABLE `sessions` ADD `session_user` INT DEFAULT '0' NOT NULL AFTER `session_id` ;

# 20050807
# cookie session name as a config option
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES (null, 'session_name', 'dotproject', 'session', 'text');

#20051005ge
#softcode for user_type
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` )
VALUES (null, '1', 'UserType', '0|Default User\n1|Administrator\n2|CEO\n3|Director\n4|Branch Manager\n5|Manager\n6|Supervisor\n7|Employee' );

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

#20060304
# added optional task numbers
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES (null, 'show_task_numbers', 'false', '', 'checkbox');

#20060311
# added notification email
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES (null, 'notification_email', '', '', '');

#20060311
# Check task dates fix
UPDATE `config` SET `config_name` = 'check_task_dates' WHERE `config_name` = 'check_tasks_dates';

#20060314
# Add country to company details
ALTER TABLE companies ADD company_country varchar(100) NOT NULL default '' AFTER company_zip;

#20060319
# Check task dates fix
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES(null, 'page_size', '25', '', 'text');

#20060320
# Force unique billing codes per company
ALTER TABLE `billingcode` ADD UNIQUE (`billingcode_name` ,`company_id`);

#20060402
# Add Event URL
ALTER TABLE `events` ADD `event_url` VARCHAR( 255 ) AFTER `event_description` ;

#20060402
# Add event_task field
ALTER TABLE `events` ADD `event_task` INT(11) AFTER `event_project` ;

#20060430
# Add a user preference to the user display
INSERT INTO `user_preferences` VALUES('0', 'USERFORMAT', 'last');

#20060430
# terms and conditions link
INSERT INTO `config` (config_id, config_name, config_value, config_group, config_type) 
	VALUES(null, 'site_terms', '...', '', 'textarea');

#20060503
# iconsets support
INSERT INTO `user_preferences` VALUES('0', 'ICONSTYLE', '');

#20060530
# Regrouping system config site
UPDATE `config` SET `config_group` = 'localisation' WHERE `config_name` = 'host_locale';
UPDATE `config` SET `config_group` = 'localisation' WHERE `config_name` = 'currency_symbol';
UPDATE `config` SET `config_group` = 'localisation' WHERE `config_name` = 'locale_warn';
UPDATE `config` SET `config_group` = 'localisation' WHERE `config_name` = 'locale_alert';

UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'show_all_task_assignees';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'show_task_numbers';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'direct_edit_assignment';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'restrict_task_time_editing';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'check_task_dates';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'check_overallocation';

UPDATE `config` SET `config_group` = 'interface' WHERE `config_name` = 'cal_day_view_show_minical';
UPDATE `config` SET `config_group` = 'interface' WHERE `config_name` = 'tasks_ajax_list';
UPDATE `config` SET `config_group` = 'interface' WHERE `config_name` = 'host_style';
UPDATE `config` SET `config_group` = 'interface' WHERE `config_name` = 'page_size';
UPDATE `config` SET `config_group` = 'interface' WHERE `config_name` = 'default_view_a';
UPDATE `config` SET `config_group` = 'interface' WHERE `config_name` = 'default_view_m';
UPDATE `config` SET `config_group` = 'interface' WHERE `config_name` = 'default_view_tab';

UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'daily_working_hours';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_day_start';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_day_end';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_day_increment';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_working_days';

UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'index_max_file_size';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_default';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_application/msword';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_text/html';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_application/pdf';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'files_ci_preserve_attr';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'files_show_versions_edit';

UPDATE `config` SET `config_group` = 'gantt' WHERE `config_name` = 'enable_gantt_charts';
UPDATE `config` SET `config_group` = 'gantt' WHERE `config_name` = 'reset_memory_limit';

UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_host';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_port';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_version';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_base_dn';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_user_filter';

UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'email_prefix';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'notification_email';

UPDATE `config` SET `config_group` = 'auth' WHERE `config_name` = 'admin_user';
UPDATE `config` SET `config_group` = 'auth' WHERE `config_name` = 'username_min_len';
UPDATE `config` SET `config_group` = 'auth' WHERE `config_name` = 'password_min_len';

UPDATE `config` SET `config_group` = 'site' WHERE `config_name` = 'page_title';
UPDATE `config` SET `config_group` = 'site' WHERE `config_name` = 'site_domain';
UPDATE `config` SET `config_group` = 'site' WHERE `config_name` = 'site_terms';
UPDATE `config` SET `config_group` = 'site' WHERE `config_name` = 'company_name';

UPDATE `config` SET `config_group` = 'projects' WHERE `config_name` = 'restrict_color_selection';

UPDATE `config` SET `config_group` = 'debug' WHERE `config_name` = 'display_debug';
UPDATE `config` SET `config_group` = 'debug' WHERE `config_name` = 'debug';

#20060531
# preparing for ticketsmith move to dotmods
DELETE FROM `config` WHERE `config_name` = 'link_tickets_kludge';

#20060531
# change over to automatic system as used in system config
ALTER TABLE `user_preferences` ADD `pref_group` VARCHAR( 255 ) NOT NULL ,
ADD `pref_type` VARCHAR( 255 ) NOT NULL ;

INSERT INTO `user_preferences` ( `pref_user` , `pref_name` , `pref_value` , `pref_group` , `pref_type` ) 
	VALUES ('0', 'CURRENCYFORM', 'en_AU', 'l10n', 'select');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`, `pref_group`, `pref_type`) 
	VALUES ('0', 'EVENTFILTER', 'all', '', 'select');
INSERT INTO `user_preferences` ( `pref_user` , `pref_name` , `pref_value` , `pref_group` , `pref_type` ) 
	VALUES ('0', 'MAILALL', 'false', 'tasks', 'checkbox');
INSERT INTO `user_preferences` ( `pref_user` , `pref_name` , `pref_value` , `pref_group` , `pref_type` ) 
	VALUES ('0', 'TASKLOGEMAIL', '0', 'tasklog', '');
INSERT INTO `user_preferences` ( `pref_user` , `pref_name` , `pref_value` , `pref_group` , `pref_type` ) 
	VALUES ('0', 'TASKLOGSUBJ', '', 'tasklog', '');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`, `pref_group`, `pref_type`) 
	VALUES ('0', 'TASKLOGNOTE', 'false', 'tasklog', 'checkbox');

UPDATE `user_preferences` SET `pref_group` = 'l10n', `pref_type` = 'select', `pref_value` = 'en_AU' WHERE `pref_name` = 'LOCALE';
UPDATE `user_preferences` SET `pref_group` = 'l10n', `pref_type` = 'select' WHERE `pref_name` = 'SHDATEFORMAT';
UPDATE `user_preferences` SET `pref_group` = 'l10n', `pref_type` = 'select' WHERE `pref_name` = 'TIMEFORMAT';
UPDATE `user_preferences` SET `pref_group` = 'ui', `pref_type` = 'select' WHERE `pref_name` = 'UISTYLE';
UPDATE `user_preferences` SET `pref_group` = 'ui', `pref_type` = 'select' WHERE `pref_name` = 'ICONSTYLE';
UPDATE `user_preferences` SET `pref_group` = 'ui', `pref_type` = 'select' WHERE `pref_name` = 'USERFORMAT';
UPDATE `user_preferences` SET `pref_type` = 'select' WHERE `pref_name` = 'TABVIEW';

CREATE TABLE `user_prefs_list` (
`pref_list_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`pref_name` VARCHAR( 255 ) NOT NULL ,
`pref_list_name` VARCHAR( 255 ) NOT NULL ,
INDEX ( `pref_name` )
) TYPE = MYISAM ;

INSERT INTO `user_prefs_list` ( `pref_list_id` , `pref_name` , `pref_list_name` ) 
	VALUES (NULL , 'USERFORMAT', 'first'), (NULL , 'USERFORMAT', 'last'), 
	(NULL , 'USERFORMAT', 'user');

#20060601
# made sure session id field is big enough
ALTER TABLE `sessions` CHANGE `session_id` `session_id` VARCHAR( 64 ) NOT NULL ;

#20060605
# adding some further user preferences options
INSERT INTO `user_preferences` ( `pref_user` , `pref_name` , `pref_value` , `pref_group` , `pref_type` )
VALUES (
'0', 'TASKNOTIFYBYDEF', 'true', 'tasks', 'checkbox'
);

#20060717
# Add system variable to set tasks default for dependency tracking
INSERT INTO `config` ( `config_id` , `config_name` , `config_value` , `config_group` , `config_type` ) VALUES (NULL , 'dependency_tracking_default', 'false', 'tasks', 'checkbox');

#20060722
# Add user preference for timezone
INSERT INTO `user_preferences` ( `pref_user` , `pref_name` , `pref_value` , `pref_group` , `pref_type` )
	VALUES ('0', 'TIMEZONE', 'UTC', 'l10n', 'select');

#200601003
# Add field storing the 'set start dates based on dep...' checkbox state
ALTER TABLE `tasks` ADD `task_dep_reset_dates` TINYINT( 1 ) NULL DEFAULT '0';
