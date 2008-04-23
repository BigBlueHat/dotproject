DROP TABLE IF EXISTS `dotproject`.`companies`;
CREATE TABLE  `dotproject`.`companies` (
  `company_id` int(10) NOT NULL auto_increment,
  `company_module` int(10) NOT NULL default '0',
  `company_name` varchar(100) default '',
  `company_phone1` varchar(30) default '',
  `company_phone2` varchar(30) default '',
  `company_fax` varchar(30) default '',
  `company_address1` varchar(50) default '',
  `company_address2` varchar(50) default '',
  `company_city` varchar(30) default '',
  `company_state` varchar(30) default '',
  `company_zip` varchar(11) default '',
  `company_primary_url` varchar(255) default '',
  `company_owner` int(11) NOT NULL default '0',
  `company_description` text,
  `company_type` int(3) NOT NULL default '0',
  `company_email` varchar(255) default NULL,
  `company_custom` longtext,
  PRIMARY KEY  (`company_id`),
  KEY `idx_cpy1` (`company_owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;