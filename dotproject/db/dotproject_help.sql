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
INSERT INTO dhlp_entries VALUES("3", "4", "28", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("5", "28", "12", "0", NULL, "label", NULL);
INSERT INTO dhlp_entries VALUES("6", "7", "11", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("7", "12", "6", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("8", "11", "9", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("9", "8", "18", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("10", "23", "25", "0", "ID_HELP_TUTORIAL", "page", NULL);
INSERT INTO dhlp_entries VALUES("11", "6", "8", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("12", "5", "7", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("13", "27", "14", "0", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("14", "13", "15", "1", "ID_HELP_COMPANIES", "page", NULL);
INSERT INTO dhlp_entries VALUES("15", "14", "16", "2", "ID_HELP_COMP_IDX", "page", NULL);
INSERT INTO dhlp_entries VALUES("16", "15", "17", "2", "ID_HELP_COMP_VIEW", "page", NULL);
INSERT INTO dhlp_entries VALUES("17", "16", "29", "2", "ID_HELP_COMP_EDIT", "page", NULL);
INSERT INTO dhlp_entries VALUES("18", "9", "19", "0", NULL, "label", NULL);
INSERT INTO dhlp_entries VALUES("19", "18", "20", "1", "ID_HELP_GEN_TOPMENU", "page", NULL);
INSERT INTO dhlp_entries VALUES("20", "19", "24", "1", "ID_HELP_GEN_LEFTNAV", "page", NULL);
INSERT INTO dhlp_entries VALUES("21", "22", "23", "1", "ID_HELP_GEN_CRUMBS", "page", NULL);
INSERT INTO dhlp_entries VALUES("22", "24", "21", "1", "ID_HELP_GEN_SAVING", "page", NULL);
INSERT INTO dhlp_entries VALUES("23", "21", "10", "1", "ID_HELP_GEN_TABS", "page", NULL);
INSERT INTO dhlp_entries VALUES("24", "20", "22", "1", "ID_HELP_GEN_HELP", "page", NULL);
INSERT INTO dhlp_entries VALUES("25", "10", "26", "1", "ID_HELP_TUT_COMP", "page", NULL);
INSERT INTO dhlp_entries VALUES("26", "25", "27", "1", "ID_HELP_TUT_PROJ", "page", NULL);
INSERT INTO dhlp_entries VALUES("27", "26", "13", "1", "ID_HELP_TUT_TASK", "page", NULL);
INSERT INTO dhlp_entries VALUES("28", "3", "5", "1", NULL, "page", NULL);
INSERT INTO dhlp_entries VALUES("29", "17", "30", "1", "ID_HELP_SYS_IDX", "page", NULL);
INSERT INTO dhlp_entries VALUES("30", "29", "0", "2", "ID_HELP_SYS_PREFS", "page", NULL);


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
INSERT INTO dhlp_xpages VALUES("3", "en", "What\'s new in this version", "1", "<P><STRONG>Version 1.0</P><P></STRONG>A great deal of work has been done to dotproject since version 0.2.2 both in the underlying code-base and in additional functionality and bug-fixes.&nbsp; We now feel it is at a stage where it can be &#39;birthed&#39; into Version 1.0 status.\r\n<P>The changes in Version 1.0 include, but are not limited to, the following:\r\n<P><STRONG>Locale Support</STRONG> \r\n<UL><LI>We are really excited about providing multi-language support in this version.&nbsp; Translations are easily managed with an editor within dotproject itself.&nbsp; The base language (that is, the language you see o&shy;n the login screen) is configurable as is the desired langauge for each user.</LI><LI>Other system and user locale preferences (date, time, etc) are also supported in this version.</LI></UL><P><STRONG>Code-base and Interface</STRONG><UL><LI>The code-base has been radically upgraded.&nbsp; Persistence and security is now handled by sessions and there has been a move towards providing abstraction and encapsulation where possible.</LI><LI>The user-interface has also had a cosmetic upgrade through the use of a presentation layer and stylesheets.&nbsp; This paves the way for future &#39;skinning&#39; for those who get bored looking at the same colours all the time.</LI></UL><P><STRONG>Database Support</STRONG></P><UL><LI>With the change in the code base comes the abstraction of the database layer.&nbsp; dotproject now has the ability to provide database support to any platform (only MySQL is provided at this stage).</LI></UL><P><STRONG>On-line Help</STRONG></P><UL><LI>Another major inclusion is the context-sensitve o&shy;nline help.&nbsp;&nbsp;It supports a table of contents, keywords list and is searchable (much like MS HtmlHelp) and also support multiple languages.</LI></UL><P><STRONG>User Interface Styles</STRONG></P><UL><LI>HTML formatting has been moved as much as possible to stylesheets which has enabled us to include user definable styles.&nbsp; A demo style is included to give you an idea of what you can do.</LI></UL><P><STRONG>Calendar Upgrades</STRONG></P><UL><LI>All date formatting is now based o&shy;n the locale setting to further our multi-language support.&nbsp; The first days of the week can also be set to either Sunday or Monday.&nbsp; The tasks shown o&shy;n the Monthly Calendar are a little truncated if they are too long to help unclutter this view.&nbsp; However, the project and full task name are shown in a tool-tip when you hover over the link.</LI></UL><P><STRONG>Modules</STRONG></P><UL><LI>A new hierarchial company departments module has been added</LI><LI>You now have the ability to watch forum topics or even whole forums\r\n<LI>Filtering has been improved o&shy;n most screens\r\n<LI>User types have been added\r\n<LI>A new module for system administration has been added\r\n<LI>Graphical Gantt charts are available</LI></UL>", "", "20030113111028");
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
INSERT INTO dhlp_xpages VALUES("19", "en", "Top Menu", "1", "The \"Top Menu\" shows you the title of the particular dotproject implementation and&nbsp;who the current user is.\r\n<P>Click o&shy;n the \"My Info\"&nbsp;link to see the information of the user you are logged in as.\r\n<P>Click o&shy;n the \"Logout\" button to log out of your session.\r\n<P>Click o&shy;n the \"About\" to bring up the dotproject help system.\r\n<P>The last thing o&shy;n the Top Menu is a drop-down selection box o&shy;n the right-hand side.&nbsp; This provides you a convient quick link to add a new item to dotproject.<BR><BR>Please note this is for the Classic dotproject style.&nbsp; Other styles provided by users may not necessarily include these elements.</P>", "", "20030113104825");
INSERT INTO dhlp_xpages VALUES("20", "en", "Left Navigation", "1", "The \"Left Navigation\" list gives you links to the modules in dotproject.<BR><BR>Please note this is for the Classic dotproject style.&nbsp; Other styles provided by users may not necessarily include this element.", "", "20030113104853");
INSERT INTO dhlp_xpages VALUES("21", "en", "Breadcrumbs", "1", "Breadcrumbs are the links under the title of a page (they are usually separated by a colon : ).\r\n<P>The breadcrumbs provide you a convenient method of forward and backward navigation within a module.</P>", NULL, "20021220134836");
INSERT INTO dhlp_xpages VALUES("22", "en", "Saving Your Place", "1", "When you select an edit link or icon, your \"place\" is saved.<BR><BR>When have finished adding or editting an item, you are returned to place where you started.", "", "20030113105042");
INSERT INTO dhlp_xpages VALUES("23", "en", "Tabbed Boxes", "1", "dotproject uses a system of tabbed boxes (much like tabbed property pages in a dialog box) to present many areas of related information in a convenient way.&nbsp; Click o&shy;n the title in each tab to make that tab active. \r\n<P>You are able to flip between a \"tabbed\" format and a \"flat\" format with the \"tabbed : flat\" breadcrumbs. \r\n<P>You are able to configure for your user whether you see the breadcrumbs or whether you always see information in a tabbed or flat configuration o&shy;nly.&nbsp; Select &#39;My Info&#39; from the top menu and then select &#39;edit preferences&#39;.&nbsp; See Edit User Preferences for information o&shy;n how to change your user preferences.</P>", "", "20030113124729");
INSERT INTO dhlp_xpages VALUES("24", "en", "Getting Help", "1", "The is a small help icon o&shy;n each page, usually to the far right of the page title, that will link you to help in the context of the page you are currently o&shy;n.<BR><BR>You can also access the help viewer by selecting the About link o&shy;n the top menu.", "", "20030113105002");
INSERT INTO dhlp_xpages VALUES("25", "en", "Add a Company", "1", "<P>The first thing you need to do is to add a company.&nbsp; This will generally be the company that you will be doing your work for (that is, your client).&nbsp; </P><P>Select <STRONG>Companies &amp; Clients</STRONG> from the left navigation menu.&nbsp; o&shy;n your first use all you will see is a table with no results.\r\n<P>Click o&shy;n the <STRONG>new company</STRONG> button.&nbsp; You will be presented with an input form.&nbsp; Fill in at least the required fields.\r\n<P>Click o&shy;n&nbsp;the <STRONG>submit</STRONG> button at the bottom of the form.&nbsp; You will be taken back to the Company Index page where you started.&nbsp; There should now be o&shy;ne entry in the list.&nbsp; It will show that your new company has no active or archived projects.<BR><BR><STRONG><FONT color=darkblue>Tip</FONT></STRONG>:&nbsp; You can create a new company at any time by selecting the &#39;company&#39; option from the &#39;New Item&#39; drop-down list shown in the top menu.</P>", "", "20030113130153");
INSERT INTO dhlp_xpages VALUES("26", "en", "Add a Project", "1", "<P>Well done, you&#39;ve created your first company.&nbsp; Now we need to create&nbsp;your first&nbsp;project.</P><P>Select <STRONG>Projects</STRONG> from the left navigation menu.&nbsp; You will see a tabbed view for &#39;Active&#39; and &#39;Archived Projects&#39;.&nbsp; Neither of these tabs will display any results.</P><P>Select the &#39;<EM>project</EM>&#39; option from the &#39;<EM>New Item</EM>&#39; drop-down list that is displayed in the top menu.&nbsp; You will be presented with an input form.\r\n<P>Fill in the Project Name.\r\n<P>Select your new company from the Company drop-down list.\r\n<P>Click o&shy;n the calendar pop-up icon (that&#39;s the left-point arrow with a grid box next to it) and select a Start Date.&nbsp; Select a Target Finish Date in the same fashion, maybe a month or so from now.\r\n<P>Now click o&shy;n the &#39;<STRONG>change color</STRONG>&#39; link (or the empty box to it&#39;s right) and select a color for your project.\r\n<P>Click o&shy;n the <STRONG>submit</STRONG> button at the bottom of the screen.&nbsp; You will be taken back to the Projects Index screen.&nbsp; If you are o&shy;n the &#39;Active Projects&#39; tab then you&#39;ll be wondering where your new project is.&nbsp; Click o&shy;n the &#39;Archived Projects&#39; tab.&nbsp; There it is.&nbsp; We need to activate this project do to anything further.\r\n<P>Click o&shy;n the <STRONG>project name</STRONG>.&nbsp; You will be presented with a&nbsp;page showing the details about the project.&nbsp; Below is another set of tabbed pages that give you details about other items associated with this projects.&nbsp; They are obviously all empty at this stage.</P><P>Click o&shy;n the &#39;<STRONG>edit this project</STRONG>&#39; link in the breadcrumbs just under the title.&nbsp; Look for a checkbox just under or next to the word &#39;<STRONG>Active?</STRONG>&#39;.&nbsp; Select this checkbox and the click o&shy;n the <STRONG>submit</STRONG> button again.&nbsp; You will have come back to the Project View page.&nbsp; Click o&shy;n the &#39;<STRONG>projects list</STRONG>&#39; link in the breadcrumbs.\r\n<P>You are now back to the Projects&nbsp;Index page.&nbsp; You should be o&shy;n the &#39;Archived Projects&#39; tab and you will find this is empty (dotproject remembers what tab you where o&shy;n last).&nbsp; Click o&shy;n the &#39;<STRONG>Active Projects</STRONG>&#39; tab.&nbsp; You should see your new project as the o&shy;nly entry in the list.\r\n<P>Now it&#39;s time to create some tasks.</P>", "", "20030113132141");
INSERT INTO dhlp_xpages VALUES("27", "en", "Add a Task", "1", "<P>If you haven&#39;t already, click o&shy;n the &#39;Projects&#39; link in the left navigation menu, click o&shy;n the &#39;Active Projects&#39; tab and then view your new project by selecting the project name link.&nbsp; You will be back o&shy;n the Project View page.&nbsp; Notice that the &#39;Active&#39; entry now shows &#39;Yes&#39;.</P><P>Click o&shy;n the &#39;<STRONG>new task</STRONG>&#39; button to the right of the page title.&nbsp; You will be presented with an input form.&nbsp; Fill in the Task Name.&nbsp; Your name will already be shown as the Task Creator.&nbsp; Using the pop-up calendar, select a Start Date and a Finish Date.&nbsp; Fill in the duration and select whether that amount in hours or days.&nbsp; Put some words in the Description area and then select the submit button.\r\n", "", "20030113132936");
INSERT INTO dhlp_xpages VALUES("28", "en", "User Mods", "1", "A new sourceforge project has been started at <A href=\"http://sourceforge.net/project/dotmods\">http://sourceforge.net/project/dotmods<BR></A><BR>This has been&nbsp;created for users to&nbsp;contribute additional modules and user interface styles to dotproject.", "customisation", "20030113111435");
INSERT INTO dhlp_xpages VALUES("29", "en", "System", "1", "The <STRONG>System Admin</STRONG> module provides:\r\n<UL><LI> a utility to assist in the translation of text</LI><LI>maintenace of system and user preferences</LI></UL>", "", "20030113124943");
INSERT INTO dhlp_xpages VALUES("30", "en", "Edit User Preferences", "1", "The <STRONG>Edit User Preferences</STRONG> page allows you to customise particular parts of dotproject.\r\n<P>You acces this page by either selecting:</P><UL><LI>the System Admin module link, then Default User Preferences; or</LI><LI>the &#39;My Info&#39; link in the top menu, then &#39;edit preferences&#39; from the breadcrumbs; or\r\n<LI>the User Admin module link, then selecting a login name to view the user information, then &#39;edit preferences&#39; from the breadcrumbs</LI></UL><P>You are able to configure settings for each individual user.&nbsp; You are also able to configure the &#39;default&#39; system settings.&nbsp; These are the settings that will be in effect when a new user is created or a new preference is added to the core system.</P><P><STRONG>Locale</STRONG>: This is the language setting that the user&nbsp;or system will present in the user interface.\r\n<P><STRONG>Tabbed Box View</STRONG>:&nbsp; Tabbed boxes may be viewed in a &#39;flat&#39; configuration.&nbsp; This settings allows you&nbsp;the option or displaying the bread-crumbs to&nbsp;toggle between the flat and tabbed styles (select the value &#39;<EM>either</EM>&#39;), or you may select to have o&shy;nly &#39;<EM>tabbed</EM>&#39; or o&shy;nly &#39;<EM>flat</EM>&#39; views all of the time.\r\n<P><STRONG>Short Date Format</STRONG>:&nbsp; This settings gives you a choice of short date formats that comprises the day number, the abbreviated month name and the year.\r\n<P><STRONG>Time Format</STRONG>:&nbsp; This setting gives you a choice of 12 and 24 hour time formats.\r\n<P><STRONG>User Interface Style</STRONG>:&nbsp; This setting allow you to configure which user interface style you wish to use.</P>", "user preferences\r\npreferences", "20030113121312");
