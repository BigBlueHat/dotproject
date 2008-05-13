DROP TABLE IF EXISTS `dotproject`.`relationships`;
CREATE TABLE  `dotproject`.`relationships` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_tbl` varchar(50) NOT NULL,
  `parent_key` varchar(50) NOT NULL,
  `child_module` varchar(50) NOT NULL,
  `child_viewhandler` varchar(50) NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  `title` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Map of parent to child objects';