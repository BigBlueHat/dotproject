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
INSERT INTO dhlp_entries VALUES("3", "4", "5", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("5", "3", "12", "0", NULL, "label", NULL);
INSERT INTO dhlp_entries VALUES("6", "7", "11", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("7", "12", "6", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("8", "11", "9", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("9", "8", "18", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("10", "23", "13", "0", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("11", "6", "8", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("12", "5", "7", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("13", "10", "14", "0", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("14", "13", "15", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("15", "14", "16", "2", "ID_HELP_COMP_IDX", "page", NULL);
INSERT INTO dhlp_entries VALUES("16", "15", "17", "2", "ID_HELP_COMP_VIEW", "page", NULL);
INSERT INTO dhlp_entries VALUES("17", "16", "0", "2", "ID_HELP_COMP_EDIT", "page", NULL);
INSERT INTO dhlp_entries VALUES("18", "9", "19", "0", NULL, "label", NULL);
INSERT INTO dhlp_entries VALUES("19", "18", "20", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("20", "19", "24", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("21", "22", "23", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("22", "24", "21", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("23", "21", "10", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("24", "20", "22", "1", NULL, "page", NULL);


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

INSERT INTO dhlp_xlabels VALUES("5", "en", "Installation", "20021220115959");
INSERT INTO dhlp_xlabels VALUES("18", "en", "General Concepts", "20021220134124");


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
INSERT INTO dhlp_xpages VALUES("6", "en", "Installing dotproject", "1", "Here&#39;s how to install it.&nbsp; Hopefully this will just say run the install script. But if you need to build it by hand then here&#39;s how:\r\n<OL><LI>Download the most recent dotproject package from <A href=\"http://sourceforge.net/projects/dotproject\" target=sourceforge.net/projects/dotproject>http://sourceforge.net/projects/dotproject</A>.<BR>&nbsp;</LI><LI>Unpack the package into a directory in your web server&#39;s hierarchy.<BR>&nbsp;\r\n<LI>Create a new MySQL database (for example, called <STRONG>dotproject</STRONG>).<BR><BR>Initially you may connect using a system account, but we strongly recommend you create a new user (for example, called dotproject) to access just this database.<BR><BR>Grant the new user access to the new database, for example:<BR><BR>mysql&gt; CREATE DATABASE dotproject;<BR>mysql&gt; GRANT ALL PRIVILEGES o&shy;n dotproject.*&nbsp;<BR>&nbsp;&nbsp;&nbsp; TO <A href=\"mailto:dotproject@localhost\">dotproject@localhost</A> IDENTIFIED BY<BR>&nbsp;&nbsp;&nbsp; \"yourpassword\" WITH GRANT OPTION;<BR>&nbsp;</LI><LI>Apply the following scripts (in the /db directory) to your new database:<BR><BR>dotproject.sql<BR>dotproject_help.sql</LI></OL><P><STRONG>dotproject</STRONG> is now installed but it&#39;s likely not going to work yet.&nbsp; You now need to edit a couple of configuration files to customise your local settings.</P>", "installation", "20021220124259");
INSERT INTO dhlp_xpages VALUES("7", "en", "System Requirements", "1", "To support the dotproject code and data requirements, you need the following components:\r\n<UL><LI>PHP 4.1 or higher</LI><LI>MySQL 3.? or higher</LI></UL><P>Please refer to the requirements for these applications specifically for the platform you are installing them o&shy;ne.</P><P>The following browsers will support dotproject but the interface may vary slightly due to the differences in the implemtation of javascript and style sheets:\r\n<UL><LI>Internet Explorer 5.5 or higher</LI><LI>Mozilla 1.2 or higher</LI></UL>", "", "20021220120831");
INSERT INTO dhlp_xpages VALUES("8", "en", "Windows specific", "1", "<P>This stuff is specific to windows.</P>", NULL, "20021220120627");
INSERT INTO dhlp_xpages VALUES("9", "en", "Unix-Linux Specific", "1", "This is the stuff specific to *nix platforms.", NULL, "20021220120713");
INSERT INTO dhlp_xpages VALUES("10", "en", "Getting Started", "1", "Now that you have installed dotproject and have been able to login, where do you go from here.", NULL, "20021220121053");
INSERT INTO dhlp_xpages VALUES("11", "en", "Configuration", "1", "<P>Now that the code and data is installed you need to customise a number of files.</P><H3>Configuring the Main Application</H3><P>Open up includes/config.php. Adjust the settings for <STRONG>dbhost</STRONG>, <STRONG>db</STRONG>, <STRONG>dbuser</STRONG> and <STRONG>dbpass</STRONG> to suite your existing configuration or the o&shy;ne you just created.&nbsp; Note that o&shy;nly a dbtype of \"mysql\" is supported at present.\r\n<P><P>Change the value of the <STRONG>host_locale</STRONG> variable to the base language you want to work in (that is, the language that the login screen will be shown in).\r\n<P>Adjust the value of the <STRONG>root_dir</STRONG> to where dotproject is installed o&shy;n your file system.\r\n<P>Change the values of <STRONG>company_name</STRONG> (this is shown in the top-left of the screen when you are log in) <STRONG>page_title</STRONG> (this is the browser page title).\r\n<P>Change the <STRONG>base_url</STRONG> to the full browser address that would point to the dotproject directory.\r\n<P>Change the <STRONG>site_domain</STRONG> to your domain.</P><H3>Configuring Help</H3><P>Open up modules/help/framed/includes/config.php.\r\n<P>Change the values for <STRONG>dbhost</STRONG>, <STRONG>db</STRONG>, <STRONG>dbuser</STRONG> and <STRONG>dbpass</STRONG> to those you entered above.\r\n<P>Adjust the root_dir variable so that the path to the dotproject directory suits your local installation.&nbsp; ENSURE that the modules/help/framed remains intact as this will cause errors if it is not correct.</P>", "", "20021220130600");
INSERT INTO dhlp_xpages VALUES("12", "en", "Upgrading an Existing Version", "1", "This is what you need to do to upgrade from an existing version.&nbsp; The FIRST thing to do is <FONT color=red>BACKUP YOU CODE AND DATA</FONT>.\r\n<P><STRONG>Upgrading to Version 1.0</STRONG><P>Version 1.0 incorporates many additions and modifications to existing database structure.&nbsp; You need to apply the script dotproject_upgrade_1.0.sql (currently called dotproject_022_023.sql) to your MySQL database.</P>", "", "20021220122431");
INSERT INTO dhlp_xpages VALUES("13", "en", "Modules", "1", "All of the function areas of <STRONG>dotproject</STRONG> are broken into modules.", NULL, "20021220130717");
INSERT INTO dhlp_xpages VALUES("14", "en", "Companies", "1", "The Companies Module is the backbone for assigning Projects and Users to a particular company and keeping track of your clients.", "Companies", "20021220131536");
INSERT INTO dhlp_xpages VALUES("15", "en", "Company List", "1", "The Companies list shows you all of your companies/clients and some statistics about projects assigned to them.\r\n<P>Click o&shy;n the Company Name to view more information about the company.\r\n<P>Click o&shy;n the \"new company\" button to add a new company to your database.</P>", NULL, "20021220132203");
INSERT INTO dhlp_xpages VALUES("16", "en", "Viewing a Company", "1", "This page shows you&nbsp;the individual detail about a company as well as a a range of related information from other areas of the database.", NULL, "20021220133545");
INSERT INTO dhlp_xpages VALUES("17", "en", "Editting Companies", "1", "To edit a company, select the Companies &amp; Clients icon in the left navigation bar, select the desired company, then select the breadcrumb link to \"edit this company\".\r\n<P>Fill out the form.</P>", NULL, "20021220133843");
INSERT INTO dhlp_xpages VALUES("19", "en", "Top Menu", "1", "The \"Top Menu\" shows you the title of the particular dotproject implementation and&nbsp;who the current user is.\r\n<P>Click o&shy;n the \"My Info\"&nbsp;link to see the information of the user you are logged in as.\r\n<P>Click o&shy;n the \"Logout\" button to log out of your session.\r\n<P>Click o&shy;n the \"About\" to bring up the dotproject help system.\r\n<P>The last thing o&shy;n the Top Menu is a drop-down selection box o&shy;n the right-hand side.&nbsp; This provides you a convient quick link to add a new item to dotproject.</P>", NULL, "20021220134609");
INSERT INTO dhlp_xpages VALUES("20", "en", "Left Navigation", "1", "The \"Left Navigation\" list gives you links to the modules in dotproject.", NULL, "20021220134653");
INSERT INTO dhlp_xpages VALUES("21", "en", "Breadcrumbs", "1", "Breadcrumbs are the links under the title of a page (they are usually separated by a colon : ).\r\n<P>The breadcrumbs provide you a convenient method of forward and backward navigation within a module.</P>", NULL, "20021220134836");
INSERT INTO dhlp_xpages VALUES("22", "en", "Saving Your Place", "1", "When you select an edit link or icon, you \"place\" is saved so that when have added or editted an item, you are returned to lpace where you started.", NULL, "20021220134944");
INSERT INTO dhlp_xpages VALUES("23", "en", "Tabbed Boxes", "1", "dotproject uses a system of tabbed boxes (much like tabbed property pages in a dialog box) to present many areas of related information in a convenient way.&nbsp; Click o&shy;n the title in each tab to make that tab active.\r\n<P>You are able to flip between a \"tabbed\" format and a \"flat\" format with the \"tabbed : flat\" breadcrumbs.\r\n<P>You are able to configure for your user whether you see the breadcrumbs or whether you always see information in a tabbed or flat configuration o&shy;nly.</P>", NULL, "20021220135352");
INSERT INTO dhlp_xpages VALUES("24", "en", "Getting Help", "1", "The is (or will be) a small help icon o&shy;n each page that will link you to help in the context of the page you are currently o&shy;n.", NULL, "20021220135528");
