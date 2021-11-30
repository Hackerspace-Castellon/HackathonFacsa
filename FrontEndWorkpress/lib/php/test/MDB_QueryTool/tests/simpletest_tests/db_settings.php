<?php
// $Id: db_settings.php 322046 2012-01-11 03:22:44Z danielc $

$dbtype = isset($_GET['type']) ? $_GET['type'] : 'mysql';
$valid_dbms = array(
    'mysql', 'pgsql', 'ibase',
);
if (!in_array($dbtype, $valid_dbms)) {
    $dbtype = 'mysql';
}
define('DB_TYPE',        $dbtype);
define('TABLE_USER',     'mdb_querytool_user');
define('TABLE_ADDRESS',  'mdb_querytool_address');
define('TABLE_QUESTION', 'mdb_querytool_question');
define('TABLE_ANSWER',   'mdb_querytool_answer');
define('TABLE_TRANSLATION',  'mdb_querytool_tr');

switch ($dbtype) {
    case 'pgsql':
        define('DB_DSN', 'pgsql://user:pwd@host/dbname');
        $GLOBALS['DB_OPTIONS'] = array();
        break;
    case 'ibase':
        define('DB_DSN', 'ibase://user:pwd@host/dbname');
        $GLOBALS['DB_OPTIONS'] = array(
            'DatabasePath'      => 'path/to/db/dir/',
            'DatabaseExtension' => '.FDB',
            'optimize'          => 'portability',
        );
        break;
    case 'mysql':
    default:
        define('DB_DSN', 'mysql://user:pwd@host/dbname');
        $GLOBALS['DB_OPTIONS'] = array();
}
$allTables = array(TABLE_USER, TABLE_ADDRESS, TABLE_QUESTION, TABLE_ANSWER);
?>
