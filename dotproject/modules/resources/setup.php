<?php
$config = array(
	'mod_name' => 'Resources',
	'mod_version' => '1.0.1',
	'mod_directory' => 'resources',
	'mod_setup_class' => 'SResource',
	'mod_type' => 'user',
	'mod_ui_name' => 'Resources',
	'mod_ui_icon' => 'helpdesk.png',
	'mod_description' => '',
	'permissions_item_table' => 'resources',
	'permissions_item_field' => 'resource_id',
	'permissions_item_label' => 'resource_name'
);

if (@$a == 'setup') {
	echo dPshowModuleConfig($config);
}

class SResource {
	function install() {
		if (! db_exec("CREATE TABLE resources (
			resource_id integer not null auto_increment,
			resource_name varchar(255) not null default '',
			resource_key varchar(64) not null default '',
			resource_type integer not null default 0,
			resource_note text not null default '',
			resource_max_allocation integer not null default 100,
			primary key (resource_id),
			key (resource_name),
			key (resource_type)
		)"))
			return db_error();
		if (! db_exec("CREATE TABLE resource_types (
			resource_type_id integer not null auto_increment,
			resource_type_name varchar(255) not null default '',
			resource_type_note text,
			primary key (resource_type_id)
		)"))
			return db_error();
		if (! db_exec("CREATE TABLE resource_tasks (
			resource_id integer not null default 0,
			task_id integer not null default 0,
			percent_allocated integer not null default 100,
			key (resource_id),
			key (task_id, resource_id)
		)"))
			return db_error();
		if (! db_exec("INSERT INTO resource_types (resource_type_name)
		VALUES
		  ('Equipment'),
			('Tool'),
			('Venue')"))
			return db_error();
		return null;
	}

	function remove() {
		db_exec("DROP TABLE resources");
		db_exec("DROP TABLE resource_tasks");
		db_exec("DROP TABLE resource_types");
	}

	function upgrade($old_version) {
		switch ($old_version) {
			case "1.0":
			db_exec("ALTER TABLE resources ADD resource_key varchar(64) not null default ''");
			if ( db_error())
				return false;
				// FALLTHROUGH
			case "1.0.1":
				break;
		}
		return true;
	}
}

?>
