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

function event_defaults ($data, $event) {
	global $text, $tngdomain;

	$type = array_key_exists('display', $data)
		? $data['display']
		: $text[$event . 'date'];

	$date = array_key_exists($event . 'date', $data)
		? $data[$event . 'date']
		: $data['eventdate'];

	$datetr = array_key_exists($event . 'datetr', $data)
		? $data[$event . 'datetr']
		: $data['eventdatetr'];

	$location = array_key_exists($event . 'place', $text)
		? $text[$event . 'place']
		: $type . ' ' . $text['place'];

	$place = array_key_exists($event . 'place', $data)
		? $data[$event . 'place']
		: $data['eventplace'];

	$dtstart = date('Ymd', strtotime($datetr));
	$dtend = date('Ymd', strtotime($datetr . ' + 1 day'));

	if (array_key_exists('personID', $data)) {
		$link = $tngdomain . 'getperson.php?personID=' . $data['personID'] . '&amp;tree=' . $data['gedcom'];
	} elseif (array_key_exists('familyID', $data)) {
		$link = $tngdomain . 'familygroup.php?familyID=' . $data['familyID'] . '&amp;tree=' . $data['gedcom'];
	} else {
		$link = $tngdomain . 'getperson.php?personID=' . $data['persfamID'] . '&amp;tree=' . $data['gedcom'];
	}

	return array($type, $date, $datetr, $location, $place, $dtstart, $dtend, $link);
}

function hvevent ($name, $data, $event = null) {
	global $text;

	list ($type, $date, $datetr, $location, $place, $dtstart, $dtend, $link) = event_defaults($data, $event);

return sprintf('<table class="calTip">
<tr>
<td valign="top" class="fieldnameback"><span class="fieldname">%s</span></td>
<td valign="top" class="databack"><span class="normal"><span class="summary"><a href="%s" class="url">%s - %s</a></span></span></td>
</tr>
<tr>
<td valign="top" class="fieldnameback"><span class="fieldname">%s</span></td>
<td valign="top" class="databack"><span class="normal"><abbr class="dtstart" title="VALUE=DATE:%s">%s</abbr><abbr class="dtend" title="VALUE=DATE:%s">&nbsp;</abbr><abbr class="rrule" title="FREQ=YEARLY">&nbsp;</abbr></span>
</td>
<tr>
<td valign="top" class="fieldnameback"><span class="fieldname">%s</span></td>
<td valign="top" class="databack"><span class="normal location">%s</span>
</td>
</tr>
</table>', $text['event'], $link, $name, $type, $text['date'], $dtstart, $date, $dtend, $location, $place);
}

function vevent ($name, $data, $event = null) {
	list ($type, $date, $datetr, $location, $place, $dtstart, $dtend, $link) = event_defaults($data, $event);

return sprintf('
BEGIN:VEVENT
URL:%s
SUMMARY:%s - %s
DTSTART;VALUE=DATE:%s
DTEND;VALUE=DATE:%s
RRULE:FREQ=YEARLY
LOCATION:%s
END:VEVENT', $link, $name, $type, $dtstart, $dtend, $place);
}

?>
