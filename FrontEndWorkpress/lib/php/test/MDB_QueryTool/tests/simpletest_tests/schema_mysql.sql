-- =====================
-- Table: answer
-- =====================

-- DROP TABLE IF EXISTS answer;
CREATE TABLE answer (
  id int(11) NOT NULL default '0',
  answer varchar(255) NOT NULL default '',
  question_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- =====================
-- Table: querytool_user
-- =====================

-- DROP TABLE IF EXISTS querytool_user;
CREATE TABLE querytool_user (
  id int(11) NOT NULL default '0',
  login varchar(255) NOT NULL default '',
  qt_password varchar(255) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  address_id int(11) NOT NULL default '0',
  company_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
);

-- =====================
-- Table: question
-- =====================

-- DROP TABLE IF EXISTS question;
CREATE TABLE question (
  id int(11) NOT NULL default '0',
  question varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
);


-- =====================
-- Table: tr
-- =====================

-- DROP TABLE IF EXISTS tr;
CREATE TABLE tr (
  string varchar(10) NOT NULL default '',
  translation varchar(10) NOT NULL default '',
  PRIMARY KEY  (string)
);