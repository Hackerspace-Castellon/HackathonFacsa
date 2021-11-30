-- =====================
-- Table: answer
-- =====================

-- DROP TABLE answer;
CREATE TABLE answer
(
  id integer not null,
  answer varchar(255) NOT NULL,
  question_id integer NOT NULL,
  CONSTRAINT pk_answer PRIMARY KEY (id)
);

-- =====================
-- Table: querytool_user
-- =====================

-- DROP TABLE querytool_user;
CREATE TABLE querytool_user
(
  id integer NOT NULL,
  login varchar(20) NOT NULL,
  name varchar(100) NOT NULL,
  address_id integer NOT NULL DEFAULT 0,
  qt_password varchar(32),
  company_id integer,
  CONSTRAINT pk_querytool_user PRIMARY KEY (id)
);

-- =====================
-- Table: question
-- =====================

-- DROP TABLE question;
CREATE TABLE question
(
  id integer NOT NULL,
  question varchar(255) NOT NULL,
  CONSTRAINT pk_question PRIMARY KEY (id)
);

-- =====================
-- Table: tr
-- =====================

-- DROP TABLE tr;
CREATE TABLE tr
(
  string varchar(5) NOT NULL,
  translation varchar(10) NOT NULL,
  CONSTRAINT pk_tr PRIMARY KEY (string)
);