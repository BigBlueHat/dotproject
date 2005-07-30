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
