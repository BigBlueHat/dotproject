#
# $Id$
# 
# DO NOT USE THIS SCRIPT DIRECTLY - USE THE INSTALLER INSTEAD.
#
# All entries must be date stamped in the correct format.
#
# 20050316
# Remove config elements that are no longer used.
DELETE FROM `config` where `config_name` = 'cal_day_view_show_minical';
DELETE FROM `config` where `config_name` = 'show_all_tasks';
