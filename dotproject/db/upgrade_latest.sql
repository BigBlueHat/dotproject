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

#
#add percentage resource allocation
#
ALTER TABLE `user_tasks` ADD COLUMN perc_assignment int(11) NOT NULL default '100';

ALTER TABLE `tasks` ADD `task_type` SMALLINT DEFAULT '0' NOT NULL ;

# Just some TaskTypes examples
INSERT INTO `sysvals` VALUES (8, 1, 'TaskType', '1|Administrative\r\n2|Operative');
INSERT INTO `syskeys` VALUES (2, 'CustomField', 'Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Field\'s name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>', 0, '\n', '|');
INSERT INTO `syskeys` VALUES("3", "ColorSelection", "Hex color values for type=>color association.", "0", "\n", "|");
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("3", "ProjectColors", "Web|FFE0AE\nEngineering|AEFFB2\nHelpDesk|FFFCAE\nSystem Administration|FFAEAE");
