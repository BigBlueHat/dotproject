#
# dotproject-upgrade.sql 
#     Database Schema Update Script
#     original update by Leo West (09 June 2002) 	
#     edited by John Pritchard (03 July 2002)
# 
# Use this schema for updating an existing dotproject
# database installation.
#

CREATE TABLE eventlog (
  objecturl varchar(30) NOT NULL default 'unknown',
  actiontype varchar(30) default 'unknown',
  status int(11) NOT NULL default '0',
  userid int(11) NOT NULL default '0',
  dt datetime NOT NULL default '0000-00-00 00:00:00'
) TYPE=MyISAM;

CREATE TABLE localization (
  lang varchar(5) NOT NULL,
  name varchar(255) NOT NULL,
  value varchar(255) default NULL,
  PRIMARY KEY (lang,name)
) TYPE=MyISAM;
    
ALTER TABLE `dotproject`.`contacts` ADD `contact_country` VARCHAR(30) default NULL;
ALTER TABLE `dotproject`.`contacts` CHANGE `contact_title` `contact_title` VARCHAR(50) default NULL;
ALTER TABLE `dotproject`.`contacts` CHANGE `contact_company` `contact_company` VARCHAR(100) default NULL;

ALTER TABLE `dotproject`.`contacts` ADD  `contact_icon` VARCHAR(20) DEFAULT 'obj/contact';
ALTER TABLE `dotproject`.`events` ADD  `event_icon` VARCHAR(20) DEFAULT 'obj/event';
ALTER TABLE `dotproject`.`files` ADD  `event_icon` VARCHAR(20) DEFAULT 'obj/';

CREATE TABLE attendees (
  event_id int(11) NOT NULL,
  attendee_id int(11) NOT NULL,
  attendee_status smallint,
  attendee_reminder smallint,
  PRIMARY KEY(event_id,attendee_id),
  FOREIGN KEY(event_id) REFERENCES events(event_id)
);

CREATE TABLE logs (
 objecturl varchar(50) NOT NULL,
 actiontype varchar(20) NOT NULL,
 status int NOT NULL,
 userid varchar(10) DEFAULT NULL,
 dt datetime
);

INSERT INTO localization VALUES 
('fr','Calendar','Agenda'),
('fr','Projects','Projets'),
('fr','Files','Documents'),
('fr','Forums','Forums'),
('fr','Localization','Traductions'),
('fr','Module','Module'),
('fr','Permission Type','Type de permission'),
('fr','Select user','Choisir utilisateur'),
('fr','Tasks','Tâches'),
('fr','Tickets','Tickets'),
('fr','Translation','Traduction'),
('fr','User Admin','Admin utilisateurs'),
('fr','deny','interdire'),
('fr','read-only','lecture seule'),
('fr','read-write','lecture-écriture'),
('fr','Locale key','Clé'),
('fr','sort by','trier par'),
('fr','Admin','Administration'),
('fr','All','Tout'),
('fr','Clients & Companies','Clients & Sociétés'),
('fr','Companies','Sociétés'),
('fr','Contacts','Contacts'),
('fr','No permissions for this User','Pas de permissions pour cet utilisateur');


