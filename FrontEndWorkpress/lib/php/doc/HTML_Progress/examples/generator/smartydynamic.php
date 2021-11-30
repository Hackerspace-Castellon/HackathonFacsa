<?php
/**
 * Generator usage example using Array QF renderer
 * for Smarty template engine.
 *
 * @version    $Id: smartydynamic.php,v 1.2 2005/07/25 12:52:15 farell Exp $
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @package    HTML_Progress
 * @subpackage Examples
 */

require_once 'HTML/Progress/generator.php';
require_once 'HTML/Progress/generator/SmartyDynamic.php';

session_start();

$tabbed = new HTML_Progress_Generator();
$tabbed->run();
?>