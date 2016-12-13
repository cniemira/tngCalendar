<?php
/*
 * tngCalendar - An addon calendar for TNG
 * http://siege.org/projects/tngCalendar/
 *
 * @author CJ Niemira <siege (at) siege (dot) org>
 * @copyright 2006,2008
 * @license GPL
 * @version 2.0
 */

/*
 ********************** DO NOT EDIT BELOW THIS LINE ***************************
 */


include("begin.php");
include($cms['tngpath'] . "genlib.php");
$textpart = "reports";
include($cms['tngpath'] . "getlang.php");
include($cms['tngpath'] . "$mylanguage/text.php");
tng_db_connect($database_host,$database_name,$database_username,$database_password) or exit;
include($cms['tngpath'] . "checklogin.php");
include($cms['tngpath'] . "log.php" );

tng_header('Calendar', $flags);
echo "<center style=\"clear:both;\">\n";
include($cms['tngpath'] . "calendar/index.php" );
echo "\n</center>";
tng_footer($flags);
?>
