#
# dotproject.sql Database Schema
#
# This script adds kripper's changes
# 
# 29 November 2002

CREATE TABLE task_dependencies (
	task_id int(11) NOT NULL,
	dep_task_id int(11) NOT NULL,
	PRIMARY KEY (task_id, dep_task_id)
);

ALTER TABLE tasks ADD task_dynamic tinyint(1) NOT NULL default 0;
