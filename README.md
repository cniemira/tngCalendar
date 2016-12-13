# tngCalendar

Archived for posterity

## An addon calendar for TNG

This is a calendar addon for a piece of commercial geneology software. It is now included _with_ said software.

## Original README

	tngCalendar is an addon to Darrin Lythgoe's excellent genealogy software:
	The Next Generation of Genealogy Sitebuilding" (c)
	http://lythgoes.net/genealogy/software.php

	This package provides a suppliment to the built-in "Anniveraries" search engine, and a friendlier user interface. It is read-only, and cannot alter records.

	tngCalendar is meant for use with version 7.0.0+ of TNG


	****************************
	INSTALLATION, BASIC CALENDAR
	****************************

	tngCalendar must be installed under an existing TNG installation


	1) Extract the archive file.


	2) Extract the archive file and upload the entire 'calendar' directory as a
	   subfolder of your TNG installation.

		For example:

		/genealogy/calendar/


	3) Move or copy 'calendar.php' into the same folder as TNG.

		For example:

		/genealogy/calendar.php


	4) Add the URLs for the calendar css and tooltip.js files to your meta.php or meta.html

		For example:

		<link href="/genealogy/calendar/calstyle.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/genealogy/calendar/tooltip.js"></script>


	5) Move calendar/orig-config.php to calendar/config.php and edit it if you would like to customize anything.


	6) Enjoy.



	***********************
	INSTALLATION, ICALENDAR
	***********************

	WARNING:
	The iCalendar mechanism WILL BYPASS TNG's security system!

	You should ONLY install icalendar.php if you FULLY UNDERSTAND what it is, what it does, and what using it means.

	iCalendar must be installed under an existing TNG installation, with tngCalendar


	1) Install tngCalendar as directed above.


	2) Move or copy 'icalendar.php' into the same folder as TNG.

		For example:

		/genealogy/icalendar.php


	4) Edit calendar/config.php to set up your "iCalKeys".

	Because you will most likely use iCalendar files to synchronize events to software that is not a web browser, and cannot complete a normal login (for example iCal or Outlook), we have to enable a special access method.  tngCalendar's icalendar.php uses "shared secrets" in the form of "keys" to enable access. This access level is pre-configured, and is independant of the normal TNG authentication system.

	To create a key, use the examples to genereate an access level that matches your needs.

	IMPORTANT: Be sure to re-name your keys! Anyone who knows what your key is can view events in your database.

	To use a key, access tngCalendar like so:

		http://yourdomain.com/genealogy/calendar/icalendar.php?key=YOURKEY
