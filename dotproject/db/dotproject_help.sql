# MySQL-Front Dump 2.5
#
# Host: localhost   Database: dotproject
# --------------------------------------------------------
# Server version 3.23.52-nt


#
# Table structure for table 'dhlp_entries'
#

DROP TABLE IF EXISTS dhlp_entries;
CREATE TABLE dhlp_entries (
  entry_id int(10) unsigned NOT NULL auto_increment,
  entry_prev int(10) unsigned NOT NULL default '0',
  entry_next int(10) unsigned NOT NULL default '0',
  entry_indent tinyint(3) unsigned NOT NULL default '0',
  entry_link varchar(24) default NULL,
  entry_type enum('label','page','class','method') NOT NULL default 'label',
  entry_icon tinyint(3) unsigned default NULL,
  PRIMARY KEY  (entry_id),
  UNIQUE KEY page_id (entry_id),
  KEY page_id_2 (entry_id)
) TYPE=MyISAM;



#
# Dumping data for table 'dhlp_entries'
#

INSERT INTO dhlp_entries VALUES("1", "0", "2", "0", "", "page", NULL);
INSERT INTO dhlp_entries VALUES("2", "1", "4", "1", "ID_HELP_ABOUT", "page", NULL);
INSERT INTO dhlp_entries VALUES("4", "2", "3", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("3", "4", "0", "1", NULL, "page", NULL);


#
# Table structure for table 'dhlp_xclasses'
#

DROP TABLE IF EXISTS dhlp_xclasses;
CREATE TABLE dhlp_xclasses (
  class_entry int(10) unsigned NOT NULL default '0',
  class_id int(10) unsigned NOT NULL default '0',
  class_lang char(2) NOT NULL default '',
  class_title varchar(128) NOT NULL default '',
  class_content longtext,
  class_keywords mediumtext,
  class_modified timestamp(14) NOT NULL,
  KEY class_entry (class_entry)
) TYPE=MyISAM COMMENT='OOP Classes';



#
# Dumping data for table 'dhlp_xclasses'
#



#
# Table structure for table 'dhlp_xlabels'
#

DROP TABLE IF EXISTS dhlp_xlabels;
CREATE TABLE dhlp_xlabels (
  label_entry int(10) unsigned NOT NULL default '0',
  label_lang char(2) NOT NULL default '',
  label_title varchar(128) NOT NULL default '',
  label_modified timestamp(14) NOT NULL,
  KEY label_id_2 (label_entry),
  KEY label_id (label_entry)
) TYPE=MyISAM;



#
# Dumping data for table 'dhlp_xlabels'
#



#
# Table structure for table 'dhlp_xmethods'
#

DROP TABLE IF EXISTS dhlp_xmethods;
CREATE TABLE dhlp_xmethods (
  method_entry int(10) unsigned NOT NULL default '0',
  method_lang char(2) NOT NULL default '',
  method_class int(6) unsigned default '0',
  method_type enum('Constructor','Property','Method') default NULL,
  method_returns enum('','integer','float','string','boolean','array','object','resource','NULL') default NULL,
  method_retbyref tinyint(1) default '0',
  method_name varchar(50) default NULL,
  method_desc varchar(128) default NULL,
  method_params longtext,
  method_remarks longtext,
  method_example longtext,
  method_output longtext,
  method_modified timestamp(14) NOT NULL,
  PRIMARY KEY  (method_entry),
  UNIQUE KEY member_id (method_entry),
  KEY member_id_2 (method_entry)
) TYPE=MyISAM;



#
# Dumping data for table 'dhlp_xmethods'
#



#
# Table structure for table 'dhlp_xpages'
#

DROP TABLE IF EXISTS dhlp_xpages;
CREATE TABLE dhlp_xpages (
  page_entry tinyint(3) unsigned NOT NULL auto_increment,
  page_lang char(2) NOT NULL default '',
  page_title varchar(128) NOT NULL default '',
  page_show_title tinyint(3) unsigned NOT NULL default '1',
  page_content longtext NOT NULL,
  page_keywords mediumtext,
  page_modified timestamp(14) NOT NULL,
  KEY page_id_2 (page_entry),
  KEY page_id (page_entry)
) TYPE=MyISAM;



#
# Dumping data for table 'dhlp_xpages'
#

INSERT INTO dhlp_xpages VALUES("1", "en", "dotproject", "0", "<TABLE height=\"100%\" width=\"100%\"><TBODY><TR><TD height=\"50%\">&nbsp;</TD></TR><TR><TD align=middle><IMG src=\"http://buran.toowoomba.qld.gov.au/Projects/dothelp/uploads/dp.gif\"></TD></TR><TR><TR><TD>&nbsp;</TD></TR><TR><TD align=middle><STRONG>dotproject</STRONG></TD></TR></TR><TR><TD>&nbsp;</TD></TR><TR><TD align=middle><STRONG>Version 1.0</STRONG></TD></TR></TR><TR><TD>&nbsp;</TD></TR><TR><TD align=middle>an open source <BR>project management <BR>application </TD></TR><TR><TD height=\"50%\">&nbsp;</TD></TR></TBODY></TABLE>", "", "20021219155347");
INSERT INTO dhlp_xpages VALUES("2", "en", "About dotproject", "1", "<P><STRONG>dotproject</STRONG> is a PHP+SQL web based project management application.&nbsp; dotproject was originally conceived by dotmarketing in December 2000.&nbsp; Since then an international team of developers spanning the globe have put their time and effort into building dotproject into an robust but easy-to-use product.</P>", "", "20021219155431");
INSERT INTO dhlp_xpages VALUES("3", "en", "What\'s new in this version", "1", "<P><STRONG>Version 1.0</P><P></STRONG>A great deal of work has been done to dotproject since version 0.2.2 both in the underlying code-base and in additional functionality and bug-fixes.&nbsp; We now feel it is at a stage where it can be &#39;birthed&#39; into Version 1.0 status.\r\n<P>The changes in Version 1.0 include, but are not limited to, the following:\r\n<P><STRONG>Locale Support</STRONG> \r\n<UL><LI>We are really excited about providing multi-language support in this version.&nbsp; Translations are easily managed with an editor within dotproject itself.&nbsp; The base language (that is, the language you see o&shy;n the login screen) is configurable as is the desired langauge for each user.</LI><LI>Other system and user locale preferences (date, time, etc) are also supported in this version.</LI></UL><P><STRONG>Code-base and Interface</STRONG><UL><LI>The code-base has been radically upgraded.&nbsp; Persistence and security is now handled by sessions and there has been a move towards providing abstraction and encapsulation where possible.</LI><LI>The user-interface has also had a cosmetic upgrade through the use of a presentation layer and stylesheets.&nbsp; This paves the way for future &#39;skinning&#39; for those who get bored looking at the same colours all the time.</LI></UL><P><STRONG>Database Support</STRONG></P><UL><LI>With the change in the code base comes the abstraction of the database layer.&nbsp; dotproject now has the ability to provide database support to any platform (only MySQL is provided at this stage).</LI></UL><P><STRONG>On-line Help</STRONG></P><UL><LI>Another major inclusion is the context-sensitve o&shy;nline help.&nbsp;&nbsp;It supports a table of contents, keywords list and is searchable (much like MS HtmlHelp) and also support multiple languages.</LI></UL><P><STRONG>Modules</STRONG></P><UL><LI>A new hierarchial company departments module has been added</LI><LI>You now have the ability to watch forum topics or even whole forums\r\n<LI>Filtering has been improved o&shy;n most screens\r\n<LI>User types have been added\r\n<LI>A new module for system administration has been added\r\n<LI>Graphical Gantt charts are available</LI></UL>", "", "20021219161327");
INSERT INTO dhlp_xpages VALUES("4", "en", "Features", "1", "Dotproject features:\r\n<UL><LI>Client/Company Management</LI><LI>Detailed project information\r\n<LI>An hierarchial task lists\r\n<LI>Graphical Gantt charts\r\n<LI>A repository for project related files\r\n<LI>A contacts list\r\n<LI>Calendar with monthly, weekly and daily views\r\n<LI>A project based discussion forum\r\n<LI>Detailed user management with resource based permissions</LI></UL>", NULL, "20021219154738");
