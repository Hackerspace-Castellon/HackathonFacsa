<?php
// $Id: simple_include.php 180765 2005-02-25 14:15:59Z quipo $

set_time_limit(0);

if (!defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', dirname(__FILE__).'/path/to/simpletest/');
}

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once(SIMPLE_TEST . 'mock_objects.php');
?>