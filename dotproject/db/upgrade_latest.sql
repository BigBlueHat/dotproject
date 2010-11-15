#
# $Id$
# 
# DO NOT USE THIS SCRIPT DIRECTLY - USE THE INSTALLER INSTEAD.
#
# All entries must be date stamped in the correct format.
#
# 20100706
# adding tls support option for ldap
INSERT INTO `config` VALUES (0, 'ldap_start_tls', 'false', 'ldap', 'checkbox');

# 20101115
# Add an index for looking users up based on tasks
ALTER TABLE `user_tasks` ADD INDEX `task_id` (`task_id`);
