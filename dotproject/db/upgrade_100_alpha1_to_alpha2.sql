# $Id$
#
# Upgrade dotProject DB Schema
# Version 1.0 alpha 1 to alpha 2

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
ALTER TABLE `tasks` CHANGE `task_percent_complete` `task_percent_complete` TINYINT(4)  DEFAULT "0";
ALTER TABLE `projects` CHANGE `project_percent_complete` `task_percent_complete` TINYINT(4)  DEFAULT "0";

