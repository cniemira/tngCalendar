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


// Load the base configuration
include("begin.php");
include($cms['tngpath'] . "genlib.php");
$textpart = "reports";
include($cms['tngpath'] . "getlang.php");
include($cms['tngpath'] . "$mylanguage/text.php");
tng_db_connect($database_host,$database_name,$database_username,$database_password) or exit;
include($cms['tngpath'] . "log.php" );

// Load the config file and library file
include_once($rootpath . "calendar/config.php");
include_once($rootpath . "calendar/callib.php");

// Output the text header
header('Content-type: text/plain');

// Find the magic key
if (!array_key_exists('key', $_GET) || !array_key_exists($_GET['key'], $iCalKeys)) {
	echo $text['missingKey'];
	exit;
}

// Get the configuration
$showLiving	= $iCalKeys[ $_GET['key'] ]['living'];
$showEvents	= $iCalKeys[ $_GET['key'] ]['events'];
$thisTree	= isset($_GET['tree']) ? $_GET['tree'] : $defaulttree;

// Begin iCalendary syntax
$evt = implode(',', $showEvents);
$pid = '-//siege.org//tngCalendar 2.0//EN';
echo "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:$pid\nMETHOD:PUBLISH\nX-TNGEVENTS:$evt";

// Query for individual/person events
$select = array();
foreach ($calIndEvent as $key => $val) {
	if (!in_array($key, $showEvents))
		continue;
	$select[] = $key . "date";
	$select[] = $key . "datetr";
	$select[] = $key . "place";
}

if (! empty($select)) {
$sql = "SELECT personID, gedcom, firstname, nickname, lnprefix, lastname, suffix, living, " . implode (', ', $select) . "
	FROM $people_table
	WHERE 1=1";

if ($showLiving == '1') {
	$sql .= ' AND living = 1';
} elseif ($showLiving == '0') {
	$sql .= ' AND living = 0';
}

if ($thisTree != '-x--all--x-')
	$sql .= " AND gedcom = '$thisTree'";

$result = mysql_query($sql);
# BREAK
if (!$result) {
	echo "Err 1<br/>$sql<br/>";
echo mysql_error();
	exit;
}


// Make sure data is normalized
if (mysql_num_rows($result) > 0) {
	while ($row = mysql_fetch_assoc($result)) {
		$longname = getName($row);
		foreach ($calIndEvent as $key => $val) {
			if ($val == null)
				continue;

			$field = $key . 'date';
			if (isset($row[$field]) &! empty($row[$field]))
				print vevent($longname, $row, $key);
		}
	}
}
}


// Query for family events this month
$select = array();
foreach ($calFamEvent as $key => $val) {
	if (!in_array($key, $showEvents))
		continue;
	$select[] = $families_table . '.' . $key . 'date';
	$select[] = $families_table . '.' . $key . 'datetr';
	$select[] = $families_table . '.' . $key . 'place';
}

if (! empty($select)) {
$sql = "SELECT familyID, gedcom, husband, wife, " . implode (', ', $select) . "
	FROM $families_table
	WHERE 1=1";

if ($showLiving == '1') {
	$sql .= ' AND living = 1';
} elseif ($showLiving == '0') {
	$sql .= ' AND living = 0';
}

if ($thisTree != '-x--all--x-')
	$sql .= " AND gedcom = '$thisTree'";

$result = mysql_query($sql);
# BREAK
if (!$result) {
	echo "Err 2<br/>";
echo mysql_error();
	exit;
}


// Make sure data is normalized
if (mysql_num_rows($result) > 0) {
	while ($row = mysql_fetch_assoc($result)) {

		$longname = getFamilyName($row);

		foreach ($calFamEvent as $key => $val) {
			if ($val == null)
				continue;

			$field = $key . 'date';
			if (isset($row[$field]) &! empty($row[$field]))
				print vevent($longname, $row, $key);
		}
	}
}
}


// Query for custom events this month
$where = array();
foreach ($calEvent as $key => $val) {
	if (!in_array($key, $showEvents))
		continue;
	$where[] = "$eventtypes_table.tag = '$key'";
}

if (! empty($where)) {
$sql = "SELECT gedcom, persfamID, tag, display, eventdate, eventdatetr, eventplace
	FROM $events_table, $eventtypes_table
	WHERE (" . implode(' OR ', $where) . ") AND $eventtypes_table.eventtypeID = $events_table.eventtypeID";

if ($thisTree != '-x--all--x-')
	$sql .= " AND gedcom = '$thisTree'";

$result = mysql_query($sql);
# BREAK
if (!$result) {
	echo "Err 3<br/>";
echo mysql_error();
	exit;
}


// Make sure the data is normalized
if (mysql_num_rows($result) > 0) {
	while ($row = mysql_fetch_assoc($result)) {

		// Ugh... who did this happen to?
		$isFam = 0;

		if ($row['persfamID']{0} == 'I') {
			$sql = "SELECT * FROM $people_table WHERE personID = '" . $row['persfamID'] . "'";
			if ($showLiving == '1') {
				$sql .= ' AND living = 1';
			} elseif ($showLiving == '0') {
				$sql .= ' AND living = 0';
			}

			if ($thisTree != '-x--all--x-')
				$sql .= " AND gedcom = '$thisTree'";

			$result2 = mysql_query($sql);

			# BREAK
			if (!$result2) {
				echo "Err 4<br/>";
echo mysql_error();
				exit;
			}

			if (mysql_num_rows($result2) < 1)
				continue;
			$longname = getName(mysql_fetch_assoc($result2));

		} elseif ($row['persfamID']{0} == 'F') {
			$sql = "SELECT * FROM $families_table WHERE familyID = '" . $row['persfamID'] . "'";
			if ($showLiving == '1') {
				$sql .= ' AND living = 1';
			} elseif ($showLiving == '0') {
				$sql .= ' AND living = 0';
			}

			if ($thisTree != '-x--all--x-')
				$sql .= " AND gedcom = '$thisTree'";

			$result3 = mysql_query($sql);

			# BREAK
			if (!$result3) {
				echo "Err 5<br/>";
echo mysql_error();
				exit;
			}

			if (mysql_num_rows($result3) < 1)
				continue;
			$longname = getFamilyName(mysql_fetch_assoc($result3));
			$isFam = 1;

		} else {
			continue;
		}

		if (isset($row['eventdate']) &! empty($row['eventdate'])) {
			$tag = $row['tag'];
			print vevent($longname, $row, $tag);
		}
	}
}
}

// End iCalendar syntax
echo "\nEND:VCALENDAR";
