# $Id$
#
# Upgrade dotProject DB Schema
# Version 1.0 alpha 2 to beta 1

# Fix misspelled table field names [modified to specify incorrect field names then new field names]
ALTER TABLE `users` CHANGE `user_password` `user_password` VARCHAR(32) NOT NULL DEFAULT '';
