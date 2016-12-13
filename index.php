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


// Make sure we're running under TNG
if (! isset($tngconfig)) {
	header("Location: ../calendar.php");
	exit;
}


// Load the config file and library file
include_once($rootpath . "calendar/config.php");
include_once($rootpath . "calendar/callib.php");

// Make an array of all the event types
$calAllEvents = array_merge($calIndEvent, $calFamEvent, $calEvent);

// Start by getting the date to display for
$current = getdate(time());

$thisMonth = ( is_numeric($_GET['m']) && ($_GET['m'] < 13) )
	? sprintf("%02d", $_GET['m'])
	: sprintf("%02d", $current['mon']);

$thisYear = ( is_numeric($_GET['y']) && ($_GET['y'] > 1000) && ($_GET['y'] < 3000) )
	? $_GET['y']
	: $current['year'];

$dateString	= "$thisYear-$thisMonth-01 00:00:00";
$time		= strtotime($dateString);

$startDay	= date('w', $time);

$daysInMonth	= date('t', $time);
$daysOfWeek	= array($text['sunday'], $text['monday'], $text['tuesday'], $text['wednesday'], $text['thursday'], $text['friday'], $text['saturday']);

$thisMonthName	= $text[strtolower(date('F', $time))];

$nextMonth	= date('n', strtotime($dateString . " + 1 month"));
$nextMonthYear	= $nextMonth == 1 ? $thisYear + 1 : $thisYear;
$nextYear	= $thisYear + 1;

$lastMonth	= date('n', strtotime($dateString . " - 1 month"));
$lastMonthYear	= $lastMonth == 12 ? $thisYear - 1 : $thisYear;
$lastYear	= $thisYear - 1;

$showLiving	= $allow_living ? (isset($_GET['living']) ? $_GET['living'] : 2) : 0;
$hideEvents	= isset($_GET['hide']) ? explode(',', $_GET['hide']) : $defaultHide;

$thisTree	= isset($_GET['tree']) ? $_GET['tree'] : $defaulttree;

$events		= array();
$ttips		= array();


// Set the default tree
$sql = "SELECT gedcom, treename FROM $trees_table";
$result = mysql_query($sql);
# BREAK
if (!$result) {
	echo "Err 0<br/>";
echo mysql_error();
	exit;
}

$numberOfTrees = mysql_num_rows($result);
$treeOptions =  treeDropdown($result);


// Query for individual/person events this month
$select = array(); $where = array();
foreach ($calIndEvent as $key => $val) {
	if (in_array($key, $hideEvents))
		continue;
	$select[] = $key . "date";
	$select[] = $key . "datetr";
	$select[] = $key . "place";
	$where[] = $key . "datetr LIKE '%-$thisMonth-%'";
}

if (! empty($where)) {
$sql = "SELECT personID, gedcom, firstname, nickname, lnprefix, lastname, suffix, living, " . implode (', ', $select) . "
	FROM $people_table
	WHERE (" . implode(' OR ', $where) . ")";

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
		$name = (strlen($longname) > $truncateNameAfter)
			? substr($longname, 0, $truncateNameAfter) . '...'
			: $longname;

		foreach ($calIndEvent as $key => $val) {
			if ($val == null)
				continue;

			$field = $key . 'datetr';
			if (isset($row[$field])) {
				$tipn = sizeof($ttips);
				$html = '<span id="event_' . $tipn . '"><img src="' . $tngdomain . $val . '" class="calIcon" /><a href="' . $tngdomain . 'getperson.php?personID=' . $row['personID'] . '&amp;tree=' . $row['gedcom'] . '" class="calEvent">' . $name . '</a></span>';

				$date = substr($row[$field], 5);
				$events[$date][$key][$row['gedcom']][$row['personID']] = $html;
				$ttips[$tipn] = hvevent($longname, $row, $key);
			}
		}
	}
}
}


// Query for family events this month
$select = array(); $where = array();
foreach ($calFamEvent as $key => $val) {
	if (in_array($key, $hideEvents))
		continue;
	$select[] = $families_table . '.' . $key . 'date';
	$select[] = $families_table . '.' . $key . 'datetr';
	$select[] = $families_table . '.' . $key . 'place';
	$where[] = $key . "datetr LIKE '%-$thisMonth-%'";
}

if (! empty($where)) {
$sql = "SELECT familyID, gedcom, husband, wife, " . implode (', ', $select) . "
	FROM $families_table
	WHERE (" . implode(' OR ', $where) . ")";

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
		$name = (strlen($longname) > $truncateNameAfter)
			? substr($longname, 0, $truncateNameAfter) . '...'
			: $longname;

		foreach ($calFamEvent as $key => $val) {
			if ($val == null)
				continue;

			$field = $key . 'datetr';
			if (isset($row[$field])) {
				$tipn = sizeof($ttips);
				$html = '<span id="event_' . $tipn . '"><img src="' . $tngdomain . $val . '" class="calIcon" /><a href="' . $tngdomain . 'familygroup.php?familyID=' . $row['familyID'] . '&amp;tree=' . $row['gedcom'] . '" class="calEvent">' . $name . '</a></span>';

				$date = substr($row[$field], 5);
				$events[$date][$key][$row['gedcom']][$row['familyID']] = $html;
				$ttips[$tipn] = hvevent($longname, $row, $key);
			}
		}
	}
}
}


// Query for custom events this month
$where = array();
foreach ($calEvent as $key => $val) {
	if (in_array($key, $hideEvents))
		continue;
	$where[] = "$eventtypes_table.tag = '$key'";
}

if (! empty($where)) {
$sql = "SELECT gedcom, persfamID, tag, display, eventdate, eventdatetr, eventplace
	FROM $events_table, $eventtypes_table
	WHERE (" . implode(' OR ', $where) . ") AND $eventtypes_table.eventtypeID = $events_table.eventtypeID AND eventdatetr LIKE '%-$thisMonth-%'";

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

		$name = (strlen($longname) > $truncateNameAfter)
			? substr($longname, 0, $truncateNameAfter) . '...'
			: $longname;

		if (isset($row['eventdatetr'])) {
			$tag = $row['tag'];

			$tipn = sizeof($ttips);
			if ($isFam) {
				$html = '<span id="event_' . $tipn . '"><img src="' . $tngdomain . $calEvent[$tag] . '" class="calIcon" /><a href="' . $tngdomain . 'familygroup.php?familyID=' . $row['persfamID'] . '&amp;tree=' . $row['gedcom'] . '" class="calEvent">' . $name . '</a></span>';
			} else {
				$html = '<span id="event_' . $tipn . '"><img src="' . $tngdomain . $calEvent[$tag] . '" class="calIcon" /><a href="' . $tngdomain . 'getperson.php?personID=' . $row['persfamID'] . '&amp;tree=' . $row['gedcom'] . '" class="calEvent">' . $name . '</a></span>';
			}

			$date = substr($row['eventdatetr'], 5);
			$events[$date][$tag][$row['gedcom']][$row['persfamID']] = $html;
			$ttips[$tipn] = hvevent($longname, $row, $tag);
		}
	}
}
}

$args = "?living=$showLiving&amp;hide=" . implode(',', $hideEvents) . "&amp;tree=$thisTree&amp;";

// Write the calendar
echo "<div id=\"calWrapper\">\n";

	if ($numberOfTrees > 1) {
		echo '<div class="calTrees">';

		echo getFORM('calendar', 'GET', 'treeform', 'treeform');
		echo "<input name=\"m\" value=\"$thisMonth\" type=\"hidden\" />\n";
		echo "<input name=\"y\" value=\"$thisYear\" type=\"hidden\" />\n";
		echo "<input name=\"living\" value=\"$showLiving\" type=\"hidden\" />\n";
		echo "<input name=\"hide\" value=\"" . implode(',', $hideEvents) . "\" type=\"hidden\" />\n";
		echo $treeOptions;
		echo "</form></div>\n";
	}

?>

<div id="calHeader">
	<a href="<?=$args?>m=<?=$thisMonth?>&amp;y=<?=$lastYear?>"><img src="<?=$tngdomain?>ArrowLeft.gif" alt="&lt;" border=0 /><img src="<?=$tngdomain?>ArrowLeft.gif" alt="&lt;" border=0 /></a>
	&nbsp;
	<a href="<?=$args?>m=<?=$lastMonth?>&amp;y=<?=$lastMonthYear?>"><img src="<?=$tngdomain?>ArrowLeft.gif" alt="&lt;" border=0 /></a>
	&nbsp;
	<?=$thisMonthName?> <?=$thisYear?>
	&nbsp;
	<a href="<?=$args?>m=<?=$nextMonth?>&amp;y=<?=$nextMonthYear?>"><img src="<?=$tngdomain?>ArrowRight.gif" alt="&gt;" border=0 /></a>
	&nbsp;
	<a href="<?=$args?>m=<?=$thisMonth?>&amp;y=<?=$nextYear?>"><img src="<?=$tngdomain?>ArrowRight.gif" alt="&gt;" border=0 /><img src="<?=$tngdomain?>ArrowRight.gif" alt="&gt;" border=0 /></a>
</div>

<div style="text-align: right;">
<div style="float: left;">
<?
	echo $text['currentfilter'] . ': ';
	echo $showLiving ? ($showLiving > 1 ? $text['all'] : $text['living']) : $text['notliving'];
?>
</div>
<?
	echo $text['filter'] . ': ';
	$args = "&amp;hide=" . implode(',', $hideEvents) . "&amp;tree=$thisTree&amp;m=$thisMonth&amp;year=$thisYear";
	if ($showLiving == '1') {
		echo '<a href="?living=2' . $args . '">' . $text['all'] . '</a> | <a href="?living=0' . $args . '">' . $text['notliving'] . '</a>';
	} elseif ($showLiving == '0') {
		echo '<a href="?living=2' . $args . '">' . $text['all'] . '</a> | <a href="?living=1' . $args . '">' . $text['living'] . '</a>';
	} else {
		echo '<a href="?living=1' . $args . '">' . $text['living'] . '</a> | <a href="?living=0' . $args . '">' . $text['notliving'] . '</a>';
	}
?>
</div>

<table align="center" class="calendar">
<tr>
<?
// Weekday name headers
for ($i = $startOfWeek; $i < $startOfWeek + 7; $i++) {
	echo "<th class=\"calDay\">" . $daysOfWeek[($i % 7)] . "</th>\n";
}

echo "</tr><tr>\n";

if ($startOfWeek > $startDay)
	$startOfWeek -= 7;

$dayInWeek = 0;

for ($i = $startOfWeek; $i < ($daysInMonth + $startDay); $i++) {
	$dayInWeek++;
	$dayInMonth = $i - $startDay;

	if ($dayInMonth >= $daysInMonth || $dayInMonth < 0) {
		echo "<td class=\"calSkip\"><div>\n";

	} else {
		$thisDay = $dayInMonth + 1;

		$class = ($thisYear == $current['year'] && $thisMonth == $current['mon'] && $thisDay == $current['mday']) ? 'calToday' : 'calDay';
		echo "<td class=\"$class\">\n";
		echo "<a href=\"" . $tngdomain . "anniversaries.php?tngdaymonth=$thisDay&amp;tngmonth=$thisMonth&amp;tngneedresults=1\" class=\"calDate\">$thisDay</a><br/>\n<div class=\"calEvents\">\n";

		$thisDate = "$thisMonth-" . sprintf("%02d", $thisDay);
		if (array_key_exists($thisDate, $events)) {
			$j = 0;
			foreach ( array_keys($events[$thisDate]) as $event) {
				if ($j > $truncateDateAfter)
					continue;
				foreach ( array_keys($events[$thisDate][$event]) as $ged ) {
					foreach ( array_keys($events[$thisDate][$event][$ged]) as $id ) {
						if ($j >= $truncateDateAfter) {
							echo "<a href=\"" . $tngdomain . "anniversaries.php?tngdaymonth=$thisDay&amp;tngmonth=$thisMonth&amp;tngneedresults=1\" class=\"calMore\">" . $text['more'] . "...</a>\n";
							$j++;
							continue 3;
						}

						// Print events
						echo $events[$thisDate][$event][$ged][$id] . "<br/>\n";
						$j++;
					}
				}
			}
		}
	}
	echo "</div>\n</td>\n";

	if (($dayInWeek % 7) == 0) { echo "</tr><tr>\n"; }
}
?>

</tr><tr>
<td colspan="7">
<div class="calKey"><?=$text['nodayevents']?></div>

<ul class="flat">
<?
$thisDate = "$thisMonth-00";
if (array_key_exists($thisDate, $events)) {
	foreach ( array_keys($events[$thisDate]) as $event) {
		foreach ( array_keys($events[$thisDate][$event]) as $ged ) {
			foreach ( array_keys($events[$thisDate][$event][$ged]) as $id ) {
				echo '<li class="flat">' . $events[$thisDate][$event][$ged][$id] . "</li>\n";
			}
		}
	}
} else {
	echo $text['none'];
}
?>
</ul>

</td>
</tr></table>

<div id="calLegend">
<ul class="flat">
<?
	// make sure the custom text key is set
	$where = array();
	foreach ($calEvent as $key => $val)
		$where[] = "$eventtypes_table.tag = '$key'";

	$sql = "SELECT tag, display
		FROM $eventtypes_table
		WHERE " . implode(' OR ', $where);

	$result = mysql_query($sql);
	# BREAK
	if (!$result) {
		echo "Err 6<br/>";
echo mysql_error();
		exit;
	}

	if (mysql_num_rows($result) > 0)
		while ($row = mysql_fetch_assoc($result))
			$text[$row['tag'] . 'date'] = $row['display'];

	foreach ($calAllEvents as $key => $val) {
		if ($val == null || empty($text[$key . 'date']))
			continue;

		if (in_array($key, $hideEvents)) {
			$class = 'hidden';
			$toHide = array_diff($hideEvents, array($key));
		} else {
			$class = 'nothidden';
			$toHide = $hideEvents;
			$toHide[] = $key;
		}

		$args = "?living=$showLiving&amp;hide=" . implode(',', $toHide) . "&amp;tree=$thisTree&amp;m=$thisMonth&amp;year=$thisYear";
		echo '<li class="flat"><img src="' . $tngdomain . $val . '" class="calIcon" /><a href="' . $args . '" class="' . $class . '">' . $text[$key . 'date'] . '</a></li>' . "\n";
	}

?>
</ul>
</div>

</div>

<div class="vcalendar">
<?
foreach ($ttips as $tid => $dat)
	echo '<div id="tip_' . $tid . '" class="vevent">' . $dat . '</div>' . "\n";
?>
</div>

<script type="text/javascript">
<?
foreach ($ttips as $tid => $dat)
	echo "try { new Tooltip('event_$tid', 'tip_$tid'); } catch (e) { $('tip_$tid').hide }\n";
?>
</script>
