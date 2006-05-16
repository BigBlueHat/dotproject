<?php
/*
 * Name:      History
 * Directory: history
 * Version:   0.1
 * Class:     user
 * UI Name:   History
 * UI Icon:
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'History';
$config['mod_version'] = '0.6';
$config['mod_directory'] = 'history';
$config['mod_setup_class'] = 'CSetupHistory';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'History';
$config['mod_ui_icon'] = '';
$config['mod_description'] = 'A module for tracking changes';

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupHistory {   

	function install() {
		$sql = " ( " .
		  "history_id int(10) unsigned NOT NULL auto_increment," .
		  "history_date datetime NOT NULL default '0000-00-00 00:00:00'," .
		  "history_user int(10) NOT NULL default '0'," .
                  "history_action varchar(10) NOT NULL default 'modify', " .
                  "history_item int(10) NOT NULL," .
		  "history_table varchar(15) NOT NULL default ''," .
		  "history_project int(10) NOT NULL default '0'," .
		  "history_name varchar(255)," .
		  "history_changes text," .
		  "history_description text," .
		  "PRIMARY KEY  (history_id)," .
			'KEY `index_history_item` (history_item) ' . 
			'KEY `index_history_table` (history_table) ' .
			'INDEX `index_history_item_table` (`history_item`, `history_table`) ' .  
		  ") TYPE=MyISAM";
		$q = new DBQuery;
		$q->createTable('history');
		$q->createDefinition($sql);
		$q->exec();
		$q->clear();
		return db_error();
	}
	
	function remove() {
		$q = new DBQuery;
		$q->dropTable('history');
		$q->exec();
		$q->clear();
		return db_error();
	}
	
	function upgrade($old_version) {
		$q = new DBQuery;
		switch ($old_version) {
			case '0.1':
				$q->alterTable('history');
				$q->addField('history_table', 'varchar(15) NOT NULL default \'\'');
				$q->addField('history_action', 'varchar(10) NOT NULL default \'modify\'');
				$q->dropField('history_module');
				$q->exec();
				$q->clear();
			case '0.2':
				$q->alterTable('history');
				$q->addField('history_item', 'int(10) NOT NULL');
				$q->exec();
				$q->clear();
			case '0.3':
				$q->alterTable('history');
				$q->addField('history_name', 'varchar(255)');
				$q->addField('history_changes', 'text');
				$q->exec();
				$q->clear();
			case '0.4':
				$q->alterTable('history');
				$q->addIndex('index_history_table', '(history_table)');
				$q->exec();
				$q->clear();
				$q->alterTable('history');
				$q->addIndex('index_history_item', '(history_item)');
				$q->exec();
				$q->clear();
			case '0.5':
				$q->alterTable('history');
				$q->addIndex('index_history_item_table', '(history_item, history_table)');
				$q->exec();
				$q->clear();
				break;
		}
		return db_error();
	}
}

?>
