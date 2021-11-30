-- =====================
-- Table: answer
-- =====================

-- DROP TABLE answer;
CREATE TABLE answer
(
  id int8 NOT NULL DEFAULT (0)::bigint,
  answer varchar(255) NOT NULL DEFAULT ''::character varying,
  question_id int8 NOT NULL DEFAULT (0)::bigint,
  CONSTRAINT pk_answer PRIMARY KEY (id)
);

-- =====================
-- Table: querytool_user
-- =====================

-- DROP TABLE querytool_user;
CREATE TABLE querytool_user
(
  id int8 NOT NULL DEFAULT (0)::bigint,
  login varchar(20) NOT NULL DEFAULT ''::character varying,
  name varchar(100) NOT NULL DEFAULT ''::character varying,
  address_id int4 NOT NULL DEFAULT 0,
  qt_password varchar(32),
  company_id int4,
  CONSTRAINT pk_querytool_user PRIMARY KEY (id)
);

-- =====================
-- Table: question
-- =====================

-- DROP TABLE question;
CREATE TABLE question
(
  id int8 NOT NULL DEFAULT (0)::bigint,
  question varchar(255) NOT NULL DEFAULT ''::character varying,
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