# $Id$
#
# Upgrade dotProject DB Schema
# Version 1.0 to release 1.01
#
# NOTE: This will NOT upgrade older releases to release 1.0
#       You must apply older upgrade script first
#
# !                  W A R N I N G                !
# !BACKUP YOU DATABASE BEFORE APPLYING THIS SCRIPT!
# !                  W A R N I N G                !
#

# 11/Sep/2003
# add forum_message field for 'last edited by'
ALTER TABLE `forum_messages` ADD `message_editor` int(11) NOT NULL default '0';

