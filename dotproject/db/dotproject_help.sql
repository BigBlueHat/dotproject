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

INSERT INTO dhlp_xpages VALUES("1", "en", "dotproject", "0", "<TABLE height=\"100%\" width=\"100%\"><TBODY><TR><TD height=\"50%\">&nbsp;</TD></TR><TR><TD align=middle><IMG src=\"http://buran.toowoomba.qld.gov.au/Projects/dothelp/uploads/dp.gif\"></TD></TR><TR><TR><TD>&nbsp;</TD></TR><TR><TD align=middle><strong>dotproject</strong></TD></TR></TR><TR><TD>&nbsp;</TD></TR><TR><TD align=middle><strong>Version 1.0</strong></TD></TR></TR><TR><TD>&nbsp;</TD></TR><TR><TD align=middle>an open source <br />project management <br />application </TD></TR><TR><TD height=\"50%\">&nbsp;</TD></TR></TBODY></TABLE>", "", "20021219155347");
INSERT INTO dhlp_xpages VALUES("2", "en", "About dotproject", "1", "<p><strong>dotproject</strong> is a PHP+SQL web based project management application.&nbsp; dotproject was originally conceived by dotmarketing in December 2000.&nbsp; Since then an international team of developers spanning the globe have put their time and effort into building dotproject into an robust but easy-to-use product.</p>", "", "20021219155431");
INSERT INTO dhlp_xpages VALUES("3", "en", "What\'s new in this version", "1", "<p><strong>Version 1.0</p><p></strong>A great deal of work has been done to dotproject since version 0.2.2 both in the underlying code-base and in additional functionality and bug-fixes.&nbsp; We now feel it is at a stage where it can be &#39;birthed&#39; into Version 1.0 status.\r\n<p>The changes in Version 1.0 include, but are not limited to, the following:\r\n<p><strong>Locale Support</strong> \r\n<ul><li>We are really excited about providing multi-language support in this version.&nbsp; Translations are easily managed with an editor within dotproject itself.&nbsp; The base language (that is, the language you see o&shy;n the login screen) is configurable as is the desired langauge for each user.</li><li>Other system and user locale preferences (date, time, etc) are also supported in this version.</li></ul><p><strong>Code-base and Interface</strong><ul><li>The code-base has been radically upgraded.&nbsp; Persistence and security is now handled by sessions and there has been a move towards providing abstraction and encapsulation where possible.</li><li>The user-interface has also had a cosmetic upgrade through the use of a presentation layer and stylesheets.&nbsp; This paves the way for future &#39;skinning&#39; for those who get bored looking at the same colours all the time.</li></ul><p><strong>Database Support</strong></p><ul><li>With the change in the code base comes the abstraction of the database layer.&nbsp; dotproject now has the ability to provide database support to any platform (only MySQL is provided at this stage).</li></ul><p><strong>On-line Help</strong></p><ul><li>Another major inclusion is the context-sensitve o&shy;nline help.&nbsp;&nbsp;It supports a table of contents, keywords list and is searchable (much like MS HtmlHelp) and also support multiple languages.</li></ul><p><strong>User Interface Styles</strong></p><ul><li>HTML formatting has been moved as much as possible to stylesheets which has enabled us to include user definable styles.&nbsp; A demo style is included to give you an idea of what you can do.</li></ul><p><strong>Calendar Upgrades</strong></p><ul><li>All date formatting is now based o&shy;n the locale setting to further our multi-language support.&nbsp; The first days of the week can also be set to either Sunday or Monday.&nbsp; The tasks shown o&shy;n the Monthly Calendar are a little truncated if they are too long to help unclutter this view.&nbsp; However, the project and full task name are shown in a tool-tip when you hover over the link.</li></ul><p><strong>Modules</strong></p><ul><li>A new hierarchial company departments module has been added</li><li>You now have the ability to watch forum topics or even whole forums\r\n<li>Filtering has been improved o&shy;n most screens\r\n<li>User types have been added\r\n<li>A new module for system administration has been added\r\n<li>Graphical Gantt charts are available</li></ul>", "", "20030113111028");
INSERT INTO dhlp_xpages VALUES("4", "en", "Features", "1", "Dotproject features:\r\n<ul><li>Client/Company Management</li><li>Detailed project information\r\n<li>An hierarchial task lists\r\n<li>Graphical Gantt charts\r\n<li>A repository for project related files\r\n<li>A contacts list\r\n<li>Calendar with monthly, weekly and daily views\r\n<li>A project based discussion forum\r\n<li>Detailed user management with resource based permissions</li></ul>", NULL, "20021219154738");
INSERT INTO dhlp_xpages VALUES("6", "en", "Installing dotproject", "1", "Here&#39;s how to install it.&nbsp; Hopefully this will just say run the install script. But if you need to build it by hand then here&#39;s how:\r\n<ol><li>Download the most recent dotproject package from <A href=\"http://sourceforge.net/projects/dotproject\" target=sourceforge.net/projects/dotproject>http://sourceforge.net/projects/dotproject</A>.<br />&nbsp;</li><li>Unpack the package into a directory in your web server&#39;s hierarchy.<br />&nbsp;\r\n<li>Create a new MySQL database (for example, called <strong>dotproject</strong>).<br /><br />Initially you may connect using a system account, but we strongly recommend you create a new user (for example, called dotproject) to access just this database.<br /><br />Grant the new user access to the new database, for example:<br /><br />mysql&gt; CREATE DATABASE dotproject;<br />mysql&gt; GRANT ALL PRIVILEGES o&shy;n dotproject.*&nbsp;<br />&nbsp;&nbsp;&nbsp; TO <A href=\"mailto:dotproject@localhost\">dotproject@localhost</A> IDENTIFIED BY<br />&nbsp;&nbsp;&nbsp; \"yourpassword\" WITH GRANT OPTION;<br />&nbsp;</li><li>Apply the following scripts (in the /db directory) to your new database:<br /><br />dotproject.sql<br />dotproject_help.sql</li></ol><p><strong>dotproject</strong> is now installed but it&#39;s likely not going to work yet.&nbsp; You now need to edit a couple of configuration files to customise your local settings.</p>", "installation", "20021220124259");
INSERT INTO dhlp_xpages VALUES("7", "en", "System Requirements", "1", "To support the dotproject code and data requirements, you need the following components:\r\n<ul><li>PHP 4.1 or higher</li><li>MySQL 3.? or higher</li></ul><p>Please refer to the requirements for these applications specifically for the platform you are installing them o&shy;ne.</p><p>The following browsers will support dotproject but the interface may vary slightly due to the differences in the implemtation of javascript and style sheets:\r\n<ul><li>Internet Explorer 5.5 or higher</li><li>Mozilla 1.2 or higher</li></ul>", "", "20021220120831");
INSERT INTO dhlp_xpages VALUES("8", "en", "Windows specific", "1", "<p>This stuff is specific to windows.</p>", NULL, "20021220120627");
INSERT INTO dhlp_xpages VALUES("9", "en", "Unix-Linux Specific", "1", "This is the stuff specific to *nix platforms.", NULL, "20021220120713");
INSERT INTO dhlp_xpages VALUES("10", "en", "Getting Started", "1", "Now that you have installed dotproject and have been able to login, where do you go from here.", NULL, "20021220121053");
INSERT INTO dhlp_xpages VALUES("11", "en", "Configuration", "1", "<p>Now that the code and data is installed you need to customise a number of files.</p><H3>Configuring the Main Application</H3><p>Open up includes/config.php. Adjust the settings for <strong>dbhost</strong>, <strong>db</strong>, <strong>dbuser</strong> and <strong>dbpass</strong> to suite your existing configuration or the o&shy;ne you just created.&nbsp; Note that o&shy;nly a dbtype of \"mysql\" is supported at present.\r\n<p><p>Change the value of the <strong>host_locale</strong> variable to the base language you want to work in (that is, the language that the login screen will be shown in).\r\n<p>Adjust the value of the <strong>root_dir</strong> to where dotproject is installed o&shy;n your file system.\r\n<p>Change the values of <strong>company_name</strong> (this is shown in the top-left of the screen when you are log in) <strong>page_title</strong> (this is the browser page title).\r\n<p>Change the <strong>base_url</strong> to the full browser address that would point to the dotproject directory.\r\n<p>Change the <strong>site_domain</strong> to your domain.</p><H3>Configuring Help</H3><p>Open up modules/help/framed/includes/config.php.\r\n<p>Change the values for <strong>dbhost</strong>, <strong>db</strong>, <strong>dbuser</strong> and <strong>dbpass</strong> to those you entered above.\r\n<p>Adjust the root_dir variable so that the path to the dotproject directory suits your local installation.&nbsp; ENSURE that the modules/help/framed remains intact as this will cause errors if it is not correct.</p>", "", "20021220130600");
INSERT INTO dhlp_xpages VALUES("12", "en", "Upgrading an Existing Version", "1", "This is what you need to do to upgrade from an existing version.&nbsp; The FIRST thing to do is <FONT color=red>BACKUP YOU CODE AND DATA</FONT>.\r\n<p><strong>Upgrading to Version 1.0</strong><p>Version 1.0 incorporates many additions and modifications to existing database structure.&nbsp; You need to apply the script dotproject_upgrade_1.0.sql (currently called dotproject_022_023.sql) to your MySQL database.</p>", "", "20021220122431");
INSERT INTO dhlp_xpages VALUES("13", "en", "Modules", "1", "All of the function areas of <strong>dotproject</strong> are broken into modules.", NULL, "20021220130717");
INSERT INTO dhlp_xpages VALUES("14", "en", "Companies", "1", "The Companies Module is the backbone for assigning Projects and Users to a particular company and keeping track of your clients.", "Companies", "20021220131536");
INSERT INTO dhlp_xpages VALUES("15", "en", "Company List", "1", "The Companies list shows you all of your companies/clients and some statistics about projects assigned to them.\r\n<p>Click o&shy;n the Company Name to view more information about the company.\r\n<p>Click o&shy;n the \"new company\" button to add a new company to your database.</p>", NULL, "20021220132203");
INSERT INTO dhlp_xpages VALUES("16", "en", "Viewing a Company", "1", "This page shows you&nbsp;the individual detail about a company as well as a a range of related information from other areas of the database.", NULL, "20021220133545");
INSERT INTO dhlp_xpages VALUES("17", "en", "Editting Companies", "1", "To edit a company, select the Companies &amp; Clients icon in the left navigation bar, select the desired company, then select the breadcrumb link to \"edit this company\".\r\n<p>Fill out the form.</p>", NULL, "20021220133843");
INSERT INTO dhlp_xpages VALUES("19", "en", "Top Menu", "1", "The \"Top Menu\" shows you the title of the particular dotproject implementation and&nbsp;who the current user is.\r\n<p>Click o&shy;n the \"My Info\"&nbsp;link to see the information of the user you are logged in as.\r\n<p>Click o&shy;n the \"Logout\" button to log out of your session.\r\n<p>Click o&shy;n the \"About\" to bring up the dotproject help system.\r\n<p>The last thing o&shy;n the Top Menu is a drop-down selection box o&shy;n the right-hand side.&nbsp; This provides you a convient quick link to add a new item to dotproject.<br /><br />Please note this is for the Classic dotproject style.&nbsp; Other styles provided by users may not necessarily include these elements.</p>", "", "20030113104825");
INSERT INTO dhlp_xpages VALUES("20", "en", "Left Navigation", "1", "The \"Left Navigation\" list gives you links to the modules in dotproject.<br /><br />Please note this is for the Classic dotproject style.&nbsp; Other styles provided by users may not necessarily include this element.", "", "20030113104853");
INSERT INTO dhlp_xpages VALUES("21", "en", "Breadcrumbs", "1", "Breadcrumbs are the links under the title of a page (they are usually separated by a colon : ).\r\n<p>The breadcrumbs provide you a convenient method of forward and backward navigation within a module.</p>", NULL, "20021220134836");
INSERT INTO dhlp_xpages VALUES("22", "en", "Saving Your Place", "1", "When you select an edit link or icon, your \"place\" is saved.<br /><br />When have finished adding or editting an item, you are returned to place where you started.", "", "20030113105042");
INSERT INTO dhlp_xpages VALUES("23", "en", "Tabbed Boxes", "1", "dotproject uses a system of tabbed boxes (much like tabbed property pages in a dialog box) to present many areas of related information in a convenient way.&nbsp; Click o&shy;n the title in each tab to make that tab active. \r\n<p>You are able to flip between a \"tabbed\" format and a \"flat\" format with the \"tabbed : flat\" breadcrumbs. \r\n<p>You are able to configure for your user whether you see the breadcrumbs or whether you always see information in a tabbed or flat configuration o&shy;nly.&nbsp; Select &#39;My Info&#39; from the top menu and then select &#39;edit preferences&#39;.&nbsp; See Edit User Preferences for information o&shy;n how to change your user preferences.</p>", "", "20030113124729");
INSERT INTO dhlp_xpages VALUES("24", "en", "Getting Help", "1", "The is a small help icon o&shy;n each page, usually to the far right of the page title, that will link you to help in the context of the page you are currently o&shy;n.<br /><br />You can also access the help viewer by selecting the About link o&shy;n the top menu.", "", "20030113105002");
INSERT INTO dhlp_xpages VALUES("25", "en", "Add a Company", "1", "<p>The first thing you need to do is to add a company.&nbsp; This will generally be the company that you will be doing your work for (that is, your client).&nbsp; </p><p>Select <strong>Companies &amp; Clients</strong> from the left navigation menu.&nbsp; o&shy;n your first use all you will see is a table with no results.\r\n<p>Click o&shy;n the <strong>new company</strong> button.&nbsp; You will be presented with an input form.&nbsp; Fill in at least the required fields.\r\n<p>Click o&shy;n&nbsp;the <strong>submit</strong> button at the bottom of the form.&nbsp; You will be taken back to the Company Index page where you started.&nbsp; There should now be o&shy;ne entry in the list.&nbsp; It will show that your new company has no active or archived projects.<br /><br /><strong><FONT color=darkblue>Tip</FONT></strong>:&nbsp; You can create a new company at any time by selecting the &#39;company&#39; option from the &#39;New Item&#39; drop-down list shown in the top menu.</p>", "", "20030113130153");
INSERT INTO dhlp_xpages VALUES("26", "en", "Add a Project", "1", "<p>Well done, you&#39;ve created your first company.&nbsp; Now we need to create&nbsp;your first&nbsp;project.</p><p>Select <strong>Projects</strong> from the left navigation menu.&nbsp; You will see a tabbed view for &#39;Active&#39; and &#39;Archived Projects&#39;.&nbsp; Neither of these tabs will display any results.</p><p>Select the &#39;<em>project</em>&#39; option from the &#39;<em>New Item</em>&#39; drop-down list that is displayed in the top menu.&nbsp; You will be presented with an input form.\r\n<p>Fill in the Project Name.\r\n<p>Select your new company from the Company drop-down list.\r\n<p>Click o&shy;n the calendar pop-up icon (that&#39;s the left-point arrow with a grid box next to it) and select a Start Date.&nbsp; Select a Target Finish Date in the same fashion, maybe a month or so from now.\r\n<p>Now click o&shy;n the &#39;<strong>change color</strong>&#39; link (or the empty box to it&#39;s right) and select a color for your project.\r\n<p>Click o&shy;n the <strong>submit</strong> button at the bottom of the screen.&nbsp; You will be taken back to the Projects Index screen.&nbsp; If you are o&shy;n the &#39;Active Projects&#39; tab then you&#39;ll be wondering where your new project is.&nbsp; Click o&shy;n the &#39;Archived Projects&#39; tab.&nbsp; There it is.&nbsp; We need to activate this project do to anything further.\r\n<p>Click o&shy;n the <strong>project name</strong>.&nbsp; You will be presented with a&nbsp;page showing the details about the project.&nbsp; Below is another set of tabbed pages that give you details about other items associated with this projects.&nbsp; They are obviously all empty at this stage.</p><p>Click o&shy;n the &#39;<strong>edit this project</strong>&#39; link in the breadcrumbs just under the title.&nbsp; Look for a checkbox just under or next to the word &#39;<strong>Active?</strong>&#39;.&nbsp; Select this checkbox and the click o&shy;n the <strong>submit</strong> button again.&nbsp; You will have come back to the Project View page.&nbsp; Click o&shy;n the &#39;<strong>projects list</strong>&#39; link in the breadcrumbs.\r\n<p>You are now back to the Projects&nbsp;Index page.&nbsp; You should be o&shy;n the &#39;Archived Projects&#39; tab and you will find this is empty (dotproject remembers what tab you where o&shy;n last).&nbsp; Click o&shy;n the &#39;<strong>Active Projects</strong>&#39; tab.&nbsp; You should see your new project as the o&shy;nly entry in the list.\r\n<p>Now it&#39;s time to create some tasks.</p>", "", "20030113132141");
INSERT INTO dhlp_xpages VALUES("27", "en", "Add a Task", "1", "<p>If you haven&#39;t already, click o&shy;n the &#39;Projects&#39; link in the left navigation menu, click o&shy;n the &#39;Active Projects&#39; tab and then view your new project by selecting the project name link.&nbsp; You will be back o&shy;n the Project View page.&nbsp; Notice that the &#39;Active&#39; entry now shows &#39;Yes&#39;.</p><p>Click o&shy;n the &#39;<strong>new task</strong>&#39; button to the right of the page title.&nbsp; You will be presented with an input form.&nbsp; Fill in the Task Name.&nbsp; Your name will already be shown as the Task Creator.&nbsp; Using the pop-up calendar, select a Start Date and a Finish Date.&nbsp; Fill in the duration and select whether that amount in hours or days.&nbsp; Put some words in the Description area and then select the submit button.\r\n", "", "20030113132936");
INSERT INTO dhlp_xpages VALUES("28", "en", "User Mods", "1", "A new sourceforge project has been started at <A href=\"http://sourceforge.net/project/dotmods\">http://sourceforge.net/project/dotmods<br /></A><br />This has been&nbsp;created for users to&nbsp;contribute additional modules and user interface styles to dotproject.", "customisation", "20030113111435");
INSERT INTO dhlp_xpages VALUES("29", "en", "System", "1", "The <strong>System Admin</strong> module provides:\r\n<ul><li> a utility to assist in the translation of text</li><li>maintenace of system and user preferences</li></ul>", "", "20030113124943");
INSERT INTO dhlp_xpages VALUES("30", "en", "Edit User Preferences", "1", "The <strong>Edit User Preferences</strong> page allows you to customise particular parts of dotproject.\r\n<p>You acces this page by either selecting:</p><ul><li>the System Admin module link, then Default User Preferences; or</li><li>the &#39;My Info&#39; link in the top menu, then &#39;edit preferences&#39; from the breadcrumbs; or\r\n<li>the User Admin module link, then selecting a login name to view the user information, then &#39;edit preferences&#39; from the breadcrumbs</li></ul><p>You are able to configure settings for each individual user.&nbsp; You are also able to configure the &#39;default&#39; system settings.&nbsp; These are the settings that will be in effect when a new user is created or a new preference is added to the core system.</p><p><strong>Locale</strong>: This is the language setting that the user&nbsp;or system will present in the user interface.\r\n<p><strong>Tabbed Box View</strong>:&nbsp; Tabbed boxes may be viewed in a &#39;flat&#39; configuration.&nbsp; This settings allows you&nbsp;the option or displaying the bread-crumbs to&nbsp;toggle between the flat and tabbed styles (select the value &#39;<em>either</em>&#39;), or you may select to have o&shy;nly &#39;<em>tabbed</em>&#39; or o&shy;nly &#39;<em>flat</em>&#39; views all of the time.\r\n<p><strong>Short Date Format</strong>:&nbsp; This settings gives you a choice of short date formats that comprises the day number, the abbreviated month name and the year.\r\n<p><strong>Time Format</strong>:&nbsp; This setting gives you a choice of 12 and 24 hour time formats.\r\n<p><strong>User Interface Style</strong>:&nbsp; This setting allow you to configure which user interface style you wish to use.</p>", "user preferences\r\npreferences", "20030113121312");
