-- $Id: install.sql,v 1.18 2005/11/20 18:45:01 blindman1344 Exp $

CREATE TABLE mod_wiki_pages (
  id int NOT NULL default '0',
  owner varchar(20) default NULL,
  editor varchar(20) default NULL,
  ip text,
  label text NOT NULL,
  created int NOT NULL default '0',
  updated int NOT NULL default '0',
  pagetext text NOT NULL,
  hits int NOT NULL default '0',
  version int NOT NULL default '0',
  allow_edit smallint NOT NULL default 1,
  PRIMARY KEY (id)
);

CREATE TABLE mod_wiki_settings (
  show_on_home smallint NOT NULL default 1,
  allow_anon_view smallint NOT NULL default 1,
  allow_page_edit smallint NOT NULL default 0,
  allow_image_upload smallint NOT NULL default 0,
  allow_bbcode smallint NOT NULL default 0,
  ext_chars_support smallint NOT NULL default 0,
  add_to_title smallint NOT NULL default 1,
  format_title smallint NOT NULL default 0,
  show_modified_info smallint NOT NULL default 1,
  monitor_edits smallint NOT NULL default 0,
  admin_email text NOT NULL,
  email_text text NOT NULL,
  default_page text NOT NULL,
  ext_page_target varchar(7) NOT NULL default '_blank',
  immutable_page smallint NOT NULL default 1,
  raw_text smallint NOT NULL default 0,
  print_view smallint NOT NULL default 1,
  discussion smallint NOT NULL default 1,
  discussion_anon smallint NOT NULL default 0
);
INSERT INTO mod_wiki_settings VALUES (1, 1, 0, 0, 0, 0, 1, 0, 1, 0, '', '[page] has been updated.  Go to [url] to view it.', 'FrontPage', '_blank', 1, 0, 1);

CREATE TABLE mod_wiki_versions (
  id int NOT NULL default '0',
  page text NOT NULL,
  version int NOT NULL default '0',
  editor varchar(20) default NULL,
  updated int NOT NULL default '0',
  pagetext text NOT NULL,
  comment text NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE mod_wiki_images (
  id int NOT NULL default '0',
  owner varchar(20) default NULL,
  ip text,
  created int NOT NULL default '0',
  filename text NOT NULL,
  size int NOT NULL default '0',
  type varchar(255) NOT NULL,
  summary text NOT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_wiki_interwiki (
  id int NOT NULL default '0',
  owner varchar(20) default NULL,
  editor varchar(20) default NULL,
  ip text,
  label text NOT NULL,
  created int NOT NULL default '0',
  updated int NOT NULL default '0',
  url text NOT NULL,
  PRIMARY KEY  (id)
);