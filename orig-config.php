<?
/*
 * tngCalendar - An addon calendar for TNG
 * http://siege.org/projects/tngCalendar/
 *
 * @author CJ Niemira <siege (at) siege (dot) org>
 * @copyright 2006,2008
 * @license GPL
 * @version 2.0
 */


/* Make sure we're running under TNG */
if (! isset($tngconfig)) {
        die ("Sorry!");
}


/* Include the calendar language file (defaults is same as TNG) */
include_once($rootpath . "calendar/$mylanguage.php");


/* We only show one type of seal date */
$text['sealdate'] = $text['ssealdate'];


/* What is the first day of the week? */
$startOfWeek = '0';		# 6=Saturday, 0=Sunday, 1=Monday, 2=Tuesday ...


/* How many characters of a name can I display? */
$truncateNameAfter = '16';


/* How many events can I show for a single day? */
$truncateDateAfter = '4';


/* Select which INDIVIDUAL events you'd like to show by setting an icon */
$calIndEvent['birth']		= 'calendar/birth.png';
$calIndEvent['death']		= 'calendar/death.png';
$calIndEvent['altbirth']	= 'calendar/altbirth.png';
$calIndEvent['burial']		= 'calendar/burial.png';
$calIndEvent['bapt']		= 'calendar/bapt.png';
$calIndEvent['endl']		= 'calendar/endl.png';


/* Select which FAMILY events you'd like to show by setting an icon */
$calFamEvent['div']		= 'calendar/div.png';
$calFamEvent['marr']		= 'calendar/marr.png';
$calFamEvent['seal']		= 'calendar/seal.png';


/* To show CUSTOM events, enter the GEDCOM TAG and set an icon */
$calEvent['EDUC']		= 'calendar/EDUC.png';
$calEvent['EMIG']		= 'calendar/EMIG.png';
$calEvent['ENGA']		= 'calendar/ENGA.png';
$calEvent['EVEN']		= 'calendar/EVEN.png';


/* You can hide certain events by default by entering the keys here */
$defaultHide			= array('altbirth', 'burial', 'bapt', 'endl', 'div', 'seal', 'EDUC', 'ENGA');


/* Make an array of all the event types */
$calAllEvents = array_merge($calIndEvent, $calFamEvent, $calEvent);


/* Set magic keys for iCalendar mode */
$iCalKeys			= array(

	// Birthdays for all living individuals
	'birthdays' => array(
		'living' => 1,
		'events' => array('birth'),
	),

	// All deaths and burrial dates
	'deaths' => array(
		'living' => 0,
		'events' => array('death', 'burial'),
	),

	// Birthdays and Anniversaries for all living individuals
	'birthaniv' => array(
		'living' => 1,
		'events' => array('birth', 'marr'),
	),

	// All Family events, for everyone
	'families' => array(
		'living' => 2,
		'events' => array('div', 'marr', 'seal'),
	),

	// Show everything
	'everything' => array(
		'living' => 2,
		'events' => array_keys($calAllEvents),
	),

);
