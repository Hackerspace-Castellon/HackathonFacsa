<?php
    // $Id: sql.php 322098 2012-01-11 21:20:03Z danielc $

    $dbStructure = array(
        'mysql' => array(
            'setUp' => array(
                    'DROP TABLE IF EXISTS '.TABLE_USER.';',
                    'DROP TABLE IF EXISTS '.TABLE_ADDRESS.';',
                    'DROP TABLE IF EXISTS '.TABLE_USER.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_ADDRESS.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_QUESTION.';',
                    'DROP TABLE IF EXISTS '.TABLE_QUESTION.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_ANSWER.';',
                    'DROP TABLE IF EXISTS '.TABLE_ANSWER.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_TRANSLATION.';',
                    'DROP TABLE IF EXISTS '.TABLE_TRANSLATION.'_seq;',

                    "CREATE TABLE ".TABLE_ADDRESS." (
                        id int(11) NOT NULL default '0',
                        city varchar(100) NOT NULL default '',
                        zip varchar(5) NOT NULL default '',
                        street varchar(100) NOT NULL default '',
                        phone varchar(100) NOT NULL default '',
                        PRIMARY KEY  (id)
                    );",

                    "CREATE TABLE ".TABLE_USER." (
                        id int(11) NOT NULL default '0',
                        login varchar(255) NOT NULL default '',
                        password varchar(255) NOT NULL default '',
                        name varchar(255) NOT NULL default '',
                        address_id int(11) NOT NULL default '0',
                        company_id int(11) NOT NULL default '0',
                        PRIMARY KEY  (id)
                    );",

                    "CREATE TABLE ".TABLE_QUESTION." (
                        id int(11) NOT NULL default '0',
                        ".TABLE_QUESTION." varchar(255) NOT NULL default '',
                        PRIMARY KEY  (id)
                    );",

                    "CREATE TABLE ".TABLE_ANSWER." (
                        id int(11) NOT NULL default '0',
                        ".TABLE_ANSWER." varchar(255) NOT NULL default '',
                        ".TABLE_QUESTION."_id int(11) NOT NULL default '0',
                        PRIMARY KEY  (id)
                    );",

                    "CREATE TABLE ".TABLE_TRANSLATION." (
                        string varchar(5) NOT NULL,
                        translation varchar(10) NOT NULL,
                        PRIMARY KEY  (string)
                    );",

                ),

            'tearDown'  =>  array(
                    'DROP TABLE IF EXISTS '.TABLE_USER.';',
                    'DROP TABLE IF EXISTS '.TABLE_USER.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_ADDRESS.';',
                    'DROP TABLE IF EXISTS '.TABLE_ADDRESS.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_QUESTION.';',
                    'DROP TABLE IF EXISTS '.TABLE_QUESTION.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_ANSWER.';',
                    'DROP TABLE IF EXISTS '.TABLE_ANSWER.'_seq;',
                    'DROP TABLE IF EXISTS '.TABLE_TRANSLATION.';',
                    'DROP TABLE IF EXISTS '.TABLE_TRANSLATION.'_seq;',
            )
        ),

        'pgsql' =>  array(
            'setUp'    => array(),
            'tearDown' => array()
        )
    );

    $dbStructure['mysqli'] = $dbStructure['mysql'];
