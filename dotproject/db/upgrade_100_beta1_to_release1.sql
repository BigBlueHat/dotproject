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
