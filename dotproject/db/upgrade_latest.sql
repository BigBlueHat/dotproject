#
# $Id$
# 
# DO NOT USE THIS SCRIPT DIRECTLY - USE THE INSTALLER INSTEAD.
#
# All entries must be date stamped in the correct format.
#

# 2005 04 04 - minical config var restaured 
INSERT INTO `config` VALUES ('', 'cal_day_view_show_minical', 'true', '', 'checkbox');

# 2005 04 05 - temporarily reset the memory limit for gantt charts
INSERT INTO `config` ( `config_id` , `config_name` , `config_value` , `config_group` , `config_type` )
VALUES ('', 'reset_memory_limit', '8M', '', 'text');