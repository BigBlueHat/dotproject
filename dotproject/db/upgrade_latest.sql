# $Id$
#
# Upgrade dotProject DB Schema
# From Version 1.0.2 to Current CVS Version
#
# NOTE: This will NOT upgrade older releases to release 1.0.2
#       You must apply older upgrade script first
#
# !                  W A R N I N G                !
# !BACKUP YOU DATABASE BEFORE APPLYING THIS SCRIPT!
# !                  W A R N I N G                !
#
# add task_departments and contacts to projects table
ALTER TABLE `projects` ADD `project_departments` CHAR( 100 ) ;
ALTER TABLE `projects` ADD `project_contacts` CHAR( 100 ) ;
ALTER TABLE `projects` ADD `project_priority` tinyint(4) default '0';
ALTER TABLE `projects` ADD `project_type` SMALLINT DEFAULT '0' NOT NULL;

#
#Add permissions selection criteria for each module.  
#
ALTER TABLE `modules` ADD `permissions_item_table` CHAR( 100 ) ;
ALTER TABLE `modules` ADD `permissions_item_field` CHAR( 100 ) ;
ALTER TABLE `modules` ADD `permissions_item_label` CHAR( 100 ) ;
UPDATE modules SET permissions_item_table='files', permissions_item_field='file_id', permissions_item_label='file_name' WHERE mod_directory='files';
UPDATE modules SET permissions_item_table='users', permissions_item_field='user_id', permissions_item_label='user_username' WHERE mod_directory='users';
UPDATE modules SET permissions_item_table='projects', permissions_item_field='project_id', permissions_item_label='project_name' WHERE mod_directory='projects';
UPDATE modules SET permissions_item_table='tasks', permissions_item_field='task_id', permissions_item_label='task_name' WHERE mod_directory='tasks';
UPDATE modules SET permissions_item_table='companies', permissions_item_field='company_id', permissions_item_label='company_name' WHERE mod_directory='companies';
UPDATE modules SET permissions_item_table='forums', permissions_item_field='forum_id', permissions_item_label='forum_name' WHERE mod_directory='forums';

#
#add percentage resource allocation
#
ALTER TABLE `user_tasks` ADD COLUMN perc_assignment int(11) NOT NULL default '100';

ALTER TABLE `users` ADD `user_contact` int(11) NOT NULL default '0';
ALTER TABLE `contacts` ADD `contact_fax` varchar(30) NOT NULL default '0';
ALTER TABLE `contacts` ADD `contact_aol` varchar(30) NOT NULL default '0';

ALTER TABLE `tasks` ADD `task_type` SMALLINT DEFAULT '0' NOT NULL ;

ALTER TABLE `files` ADD `file_category` int(11) NOT NULL default '0';
INSERT INTO `sysvals` VALUES (null, 1, 'FileType', '0|Unknown\n1|Document\n2|Application');

# Just some TaskTypes examples
INSERT INTO `sysvals` VALUES (null, 1, 'TaskType', '0|Unknown\n1|Administrative\n2|Operative');
INSERT INTO `sysvals` VALUES (null, 1, 'ProjectType', '0|Unknown\n1|Administrative\n2|Operative');
INSERT INTO `syskeys` VALUES (2, 'CustomField', 'Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Field\'s name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>', 0, '\n', '|');
INSERT INTO `syskeys` VALUES("3", "ColorSelection", "Hex color values for type=>color association.", "0", "\n", "|");
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("3", "ProjectColors", "Web|FFE0AE\nEngineering|AEFFB2\nHelpDesk|FFFCAE\nSystem Administration|FFAEAE");

CREATE TABLE `task_contacts` (
  `task_id` INT(10) NOT NULL,
  `contact_id` INT(10) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `task_departments` (
  `task_id` INT(10) NOT NULL,
  `department_id` INT(10) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `project_contacts` (
  `project_id` INT(10) NOT NULL,
  `contact_id` INT(10) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `project_departments` (
  `project_id` INT(10) NOT NULL,
  `department_id` INT(10) NOT NULL
) TYPE=MyISAM;

# 20040727
# add user specific task priority
#
ALTER TABLE `user_tasks` ADD `user_task_priority` tinyint(4) default '0';

# 20040728
# converted taskstatus to sysvals
#
INSERT INTO `sysvals` VALUES (null, 1, 'TaskStatus', '0|Active\n-1|Inactive');

# 20040808
# do not show events on non-working days
#
ALTER TABLE `events` ADD `events_cwd` tinyint(3) default '0';

# 20040815
# increase various field lengths
#
ALTER TABLE `contacts` CHANGE `contact_address1` `contact_address1` varchar(60) default null ;
ALTER TABLE `contacts` CHANGE `contact_address2` `contact_address2` varchar(60) default null ;
ALTER TABLE `users` CHANGE `user_username` `user_username` varchar(255) default null ;

# 20040819
# invent task assign maximum
#
ALTER TABLE `user_preferences` CHANGE `pref_name` `pref_name` VARCHAR( 72 ) NOT NULL;
INSERT INTO `user_preferences` VALUES("0", "TASKASSIGNMAX", "100");

#20040820
# added ProjectStatus of Template
#
UPDATE `sysvals` SET `sysval_value` = '0|Not Defined 1|Proposed 2|In Planning 3|In Progress 4|On Hold 5|Complete 6|Template' WHERE `sysval_title` = 'ProjectStatus' LIMIT 1 ;
