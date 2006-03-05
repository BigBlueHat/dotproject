<?php
$config = array();
$config['mod_name'] = 'Tickets';
$config['mod_version'] = '2.0';
$config['mod_directory'] = 'ticketsmith';
$config['mod_setup_class'] = 'CSetupTickets';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Tickets';
$config['mod_ui_icon'] = '';
$config['mod_description'] = 'A module for tracking tickets';

class CSetupTickets {   

	function install() {
		$sql = " (
  ticket int(10) unsigned DEFAULT '0' NOT NULL auto_increment,
  author varchar(100) DEFAULT '' NOT NULL,
  recipient varchar(100) DEFAULT '' NOT NULL,
  subject varchar(100) DEFAULT '' NOT NULL,
  attachment tinyint(1) unsigned DEFAULT '0' NOT NULL,
  timestamp int(10) unsigned DEFAULT '0' NOT NULL,
  type varchar(15) DEFAULT '' NOT NULL,
  assignment int(10) unsigned DEFAULT '0' NOT NULL,
  parent int(10) unsigned DEFAULT '0' NOT NULL,
  activity int(10) unsigned DEFAULT '0' NOT NULL,
  priority tinyint(1) unsigned DEFAULT '1' NOT NULL,
  cc varchar(100) DEFAULT '' NOT NULL,
  body text NOT NULL,
  PRIMARY KEY (ticket),
  KEY parent (parent),
  KEY type (type)
)";
		$q = new DBQuery;
		$q->createTable('tickets');
		$q->createDefinition($sql);
		$q->exec();
		$q->clear();
		
		
		return db_error();
	}
	
	function remove() {
		$q = new DBQuery;
		$q->dropTable('tickets');
		$q->exec();
		$q->clear();
		return db_error();
	}
	
	function upgrade($old_version) {
		$q = new DBQuery;
		switch ($old_version) {
		case '1.0.0':
			$q->addTable('sysvals');
			$q->addInsert('sysval_key_id', 1);
			$q->addInsert('sysval_title', 'TicketsStatus');
			$q->addInsert('sysval_value', 'Open|Open
Processing|Processing
Closed|Closed
Deleted|Deleted');
			$q->exec();
			$q->clear();

			$q->addTable('sysvals');
			$q->addInsert('sysval_key_id', 1);
			$q->addInsert('sysval_title', 'TicketsPriority');
			$q->addInsert('sysval_value', '0|Low
1|Normal
2|High
3|Highest
4|Showstopper');
			$q->exec();
			$q->clear();
		}
		
		return true;
	}
}