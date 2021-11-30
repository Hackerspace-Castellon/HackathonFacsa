<?php
/**
 * Basic usage example for HTML_Menu, showing all available menu types
 *
 * @category    HTML
 * @package     HTML_Menu
 * @author      Alexey Borzov <avb@php.net>
 * @version     CVS: $Id: types.php,v 1.3 2007/05/18 20:54:33 avb Exp $
 * @ignore
 */

require_once 'HTML/Menu.php';
require_once './data/menu.php';

$types = array('tree', 'urhere', 'prevnext', 'rows', 'sitemap');

$menu =& new HTML_Menu($data);
$menu->forceCurrentUrl('/item1.2.2.php');

foreach ($types as $type) {
    echo "\n<h1>Trying menu type &quot;{$type}&quot;</h1>\n";
    $menu->show($type);
}
?>
