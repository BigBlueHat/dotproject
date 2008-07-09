-- List of modifications made to the database schema for DP3.
-- Will be merged into installer when schema is final.

DROP TABLE IF EXISTS `relationships`;
CREATE TABLE  `relationships` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_tbl` varchar(50) NOT NULL,
  `parent_key` varchar(50) NOT NULL,
  `child_module` varchar(50) NOT NULL,
  `child_viewhandler` varchar(50) NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  `title` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Map of parent to child objects';
INSERT INTO `relationships` VALUES (null, 'companies', 'company_id', 'contacts', 'ContactsSubList', 1, 'Contacts');

--
-- Table structure for table `memberships`
--

DROP TABLE IF EXISTS `memberships`;
CREATE TABLE `memberships` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL,
  `parent_type` varchar(128) NOT NULL,
  `child_id` int(11) NOT NULL,
  `child_type` varchar(128) NOT NULL,
  `is_member` tinyint(4) NOT NULL,
  `is_resource` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `child_id` (`child_type`,`child_id`),
  KEY `parent_id` (`parent_type`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `ous`
--

DROP TABLE IF EXISTS `ous`;
CREATE TABLE `ous` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL COMMENT 'OU Name',
  `description` text COMMENT 'OU Description',
  `postaladdress` text COMMENT 'Dollar seperated lines',
  `st` varchar(50) default NULL COMMENT 'State',
  `street` varchar(255) default NULL,
  `postofficebox` varchar(50) default NULL,
  `postalCode` varchar(20) default NULL,
  `mail` varchar(255) default NULL COMMENT 'E-mail address',
  `phonelist` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Organizational units';

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE `people` (
  `id` int(11) NOT NULL auto_increment,
  `displayname` varchar(255) default NULL,
  `mail` varchar(255) default NULL COMMENT 'E-mail address',
  `givenname` varchar(255) default NULL COMMENT 'First name',
  `description` text COMMENT 'Description',
  `userpassword` varchar(255) default NULL COMMENT 'Password (MD5)',
  `postaladdress` text COMMENT 'Dollar seperated address',
  `postalcode` varchar(20) default NULL COMMENT 'Zip code',
  `postofficebox` varchar(20) default NULL COMMENT 'PO Box',
  `street` varchar(255) default NULL,
  `title` varchar(20) default NULL,
  `sn` varchar(255) default NULL,
  `initials` varchar(20) default NULL,
  `uid` varchar(255) default NULL,
  `labeledURI` varchar(255) default NULL,
  `phonelist` int(11) default NULL,
  `can_login` tinyint(4) NOT NULL default '0',
  `st` varchar(40) default NULL COMMENT 'State',
  PRIMARY KEY  (`id`),
  KEY `UID` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Based on rfc2798';
-- md5 hash of password value 'admin'
INSERT INTO `people` (displayname, uid, userpassword, can_login) VALUES ('Admin user', 'admin', '76a2173be6393254e72ffa4d6df1030a', 1);

--
-- Table structure for table `phone_types`
--

DROP TABLE IF EXISTS `phone_types`;
CREATE TABLE `phone_types` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL COMMENT 'Phone number type',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='Phone number types';

--
-- Table structure for table `phonelists`
--

DROP TABLE IF EXISTS `phonelists`;
CREATE TABLE `phonelists` (
  `id` int(11) NOT NULL auto_increment,
  `list_number` int(11) NOT NULL default '0',
  `number_type` int(11) NOT NULL,
  `telephonenumber` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `LISTNO` (`list_number`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `policies`
--

DROP TABLE IF EXISTS `policies`;
CREATE TABLE `policies` (
  `policy_resource` varchar(128) NOT NULL,
  `policy_type` enum('Member','Non-Member','Owner') NOT NULL,
  `policy_view` tinyint(4) NOT NULL,
  `policy_edit` tinyint(4) NOT NULL,
  `policy_child` tinyint(4) NOT NULL,
  PRIMARY KEY  (`policy_resource`(12),`policy_type`),
  KEY `policy_view` (`policy_view`),
  KEY `policy_edit` (`policy_edit`),
  KEY `policy_child` (`policy_child`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

