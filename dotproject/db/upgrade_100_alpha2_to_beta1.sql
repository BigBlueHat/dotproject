# $Id$
#
# Upgrade dotProject DB Schema
# Version 1.0 alpha 2 to beta 1

# fix to convert password field to md5 based string
ALTER TABLE `users` CHANGE `user_password` `user_password` VARCHAR(32) NOT NULL DEFAULT '';

# fixes to provide more generic duration type handling
UPDATE `tasks` SET task_duration_type = 1 WHERE task_duration_type = 'hours';
UPDATE `tasks` SET task_duration_type = 24 WHERE task_duration_type = 'days';

ALTER TABLE `tasks` CHANGE `task_duration_type` `task_duration_type` int(11) NOT NULL DEFAULT 1;

INSERT INTO sysvals (sysval_key_id,sysval_title,sysval_value) VALUES("1", "TaskDurationType", "1|hours\n24|days");
