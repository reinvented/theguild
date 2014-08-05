#!/usr/bin/php
<?php
/**
  * harvest-guild-calendar.php
  *
  * A PHP script to harvest a JSON-encoded calendar data from The Guild
  * (http://www.theguildpei.com/) and convert it into an iCalendar file.
	*
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.
  *
  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  * 
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  * USA
  *
  * @version 0.1, August 5, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2014, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

// Set the default time zone
date_default_timezone_set("America/Halifax");

// Right now, as a unixtime value
$date_start = mktime();

// 90 days from now, as a unixtime value
$date_end = $date_start + (86400 * 90);

// Parameters to pass for an event search
$data  = "action=get_events&readonly=true&categories=0&excluded=0&start=" . $date_start . "&end=" . $date_end;

// Build a cURL POST
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"http://www.theguildpei.com/wp-admin/admin-ajax.php");
curl_setopt($ch, CURLOPT_HTTPGET, 1);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch,CURLOPT_POSTFIELDS, $data);

// Execute and get response
$response = curl_exec($ch); 

// Decode the received JSON into a PHP object.
$cal = json_decode($response);

// Open a file to output the iCalendar-format data into
$fp = fopen("theguild.ics","w");

// Output the iCalendar file header.
fwrite($fp,"BEGIN:VCALENDAR\n");
fwrite($fp,"CALSCALE:GREGORIAN\n");
fwrite($fp,"PRODID:-//Reinvented Inc.\, //TheGuild 1.1//EN\n");
fwrite($fp,"X-WR-CALNAME;VALUE=TEXT:The Guild Theatre\n");
fwrite($fp,"X-WR-TIMEZONE;VALUE=TEXT:Canada/Atlantic\n");
fwrite($fp,"VERSION:2.0\n");

// For each event in the JSON calendar, output a VEVENT.
foreach ($cal as $key => $e) {
	// Exclude gallery events
	if (strpos($e->className,"cat1") === false) {
		fwrite($fp,"BEGIN:VEVENT\n");
		fwrite($fp,"SUMMARY:" . $e->title . "\n");
		fwrite($fp,"DTSTART;TZID=Canada/Atlantic:" . strftime("%Y%m%dT%H%M%S",strtotime($e->start)) . "\n");
		fwrite($fp,"DTEND;TZID=Canada/Atlantic:" . strftime("%Y%m%dT%H%M%S",strtotime($e->end)) . "\n");
		fwrite($fp,"END:VEVENT\n");
	}
}
fwrite($fp,"END:VCALENDAR\n");

// Close the output file.
fclose($fp);