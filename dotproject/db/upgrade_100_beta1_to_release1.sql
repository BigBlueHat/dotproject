# $Id$
#
# Upgrade dotProject DB Schema
# Version 1.0 beta 1 to release
#
# NOTE: This will NOT upgrade 1.0 alpha2 to release
#       You must apply the 1.0 alpha 2 to beta 1 upgrade script first
#
# !                  W A R N I N G                !
# !BACKUP YOU DATABASE BEFORE APPLYING THIS SCRIPT!
# !                  W A R N I N G                !
#

# 10/Jul/2003 
# add record access to tasks table
ALTER TABLE `tasks` ADD `task_access` INT(11) NOT NULL DEFAULT '0';

# 30/Aug/2003
# fix lengths of email fields
ALTER TABLE `companies` CHANGE `company_email` `company_email` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `contacts` CHANGE `contact_email` `contact_email` VARCHAR(255) default NULL;
ALTER TABLE `contacts` CHANGE `contact_email2` `contact_email2` VARCHAR(255) default NULL;
ALTER TABLE `users` CHANGE `user_email` `user_email` VARCHAR(255) default '';

# Add notify column to tasks
ALTER TABLE `tasks` ADD `task_notify` INT(11) NOT NULL DEFAULT '0';
