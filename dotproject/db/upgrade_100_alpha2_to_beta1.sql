# $Id$
#
# Upgrade dotProject DB Schema
# Version 1.0 alpha 2 to beta 1
#
# NOTE: This will NOT upgrade 1.0 alpha1 to beta 1
#       You must apply the 1.0 alpha 1 to alpha 2 upgrade script first
#
# !                  W A R N I N G                !
# !BACKUP YOU DATABASE BEFORE APPLYING THIS SCRIPT!
# !                  W A R N I N G                !
#

# fix to convert password field to md5 based string
ALTER TABLE `users` CHANGE `user_password` `user_password` VARCHAR(32) NOT NULL DEFAULT '';

# fixes to provide more generic duration type handling
UPDATE `tasks` SET task_duration_type = 1 WHERE task_duration_type = 'hours';
UPDATE `tasks` SET task_duration_type = 24 WHERE task_duration_type = 'days';

ALTER TABLE `tasks` CHANGE `task_duration_type` `task_duration_type` int(11) NOT NULL DEFAULT 1;

INSERT INTO sysvals (sysval_key_id,sysval_title,sysval_value) VALUES("1", "TaskDurationType", "1|hours\n24|days");

# these can wait until release
#ALTER TABLE `companies` ADD `company_module` INT UNSIGNED DEFAULT "0" NOT NULL AFTER company_id;
#ALTER TABLE `projects` ADD `project_module` INT UNSIGNED DEFAULT "0" NOT NULL AFTER project_id;
#ALTER TABLE `events` ADD `event_module` INT UNSIGNED DEFAULT "0" NOT NULL AFTER event_id;

#
# Changes to the Events table
# Convert unix timestamp fields to mysql datetime formats
#
ALTER TABLE `events` CHANGE `event_start_date` `event_start_date` VARCHAR(20);
ALTER TABLE `events` CHANGE `event_end_date` `event_end_date` VARCHAR(20);

UPDATE `events` SET `event_start_date`=FROM_UNIXTIME(`event_start_date`);
UPDATE `events` SET `event_end_date`=FROM_UNIXTIME(`event_end_date`);

ALTER TABLE `events` CHANGE `event_start_date` `event_start_date` DATETIME default null;
ALTER TABLE `events` CHANGE `event_end_date` `event_end_date` DATETIME default null;

#
# Added support for an event type
#
ALTER TABLE `events` ADD `event_type` TINYINT(3) DEFAULT "0" NOT NULL;
INSERT INTO sysvals (sysval_key_id,sysval_title,sysval_value) VALUES("1", "EventType", "0|General\n1|Appointment\n2|Meeting\n3|All\nDay Event\n4|Anniversary\n5|Reminder");
