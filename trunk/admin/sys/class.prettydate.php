<?php
/**
 * Converts a timestamp to pretty human-readable format.
 * 
 * Original JavaScript Created By John Resig (jquery.com)  Copyright (c) 2008
 * Copyright (c) 2008 John Resig (jquery.com)
 * Licensed under the MIT license.
 * Ported to PHP >= 5.1 by Zach Leatherman (zachleat.com)
 * 
 * Modification By Devan Koshal - www.weareplic.com / www.noise.weareplic.com
 * Improved 'Yesterday' handling and variables created to edit outputed text, faster.      
 */ 

/*
    // Demo... Date must be given in MySQL timestamp format.
    echo prettyDate::getStringResolved('2004-03-27 00:35:20');
*/ 
class prettyDate 
{
      public static function getStringResolved($date, $compareTo = null)
      {
          if (!is_null($compareTo)) {
              $compareTo = new DateTime($compareTo);
          }
          return self::getString(new DateTime($date), $compareTo);
      }
      
      public static function getString(DateTime $date, DateTime $compareTo = null)
      {
          // Messages
          $justNow = 'just now';
          $secondsAgo = ' seconds ago';
          $oneMinuteAgo = 'one minute ago';
          $minutesAgo = ' minutes ago';
          $oneHourAgo = 'one hour ago';
          $hoursAgo = ' hours ago';
          $yesterday = 'yesterday';
          $daysAgo = ' days ago';
          $oneWeekAgo = 'one week ago';
          $weeksAgo = ' weeks ago';
          $monthsAgo = ' months ago';
          $justAYear = 'one year ago';
          $moreThanAYear = ' years ago';
 
          // If there is no date to compare to, compare it to the todays date.
          if (is_null($compareTo)) {
              $compareTo = new DateTime('now');
          }
          // Set date variables
          $diff = $compareTo->format('U') - $date->format('U');
          $dayDiff = floor($diff / 86400);
          $daySent = $date->format('d');
          $dayYesterday = date('d', mktime(0, 0, 0, date('m'), date('d') - 1, date('y')));
          // If the timestamp set is later than the actually time, return nothing.
          if (is_nan($dayDiff) || $dayDiff < 0) {
              return $justNow;
          }
          // If the timestamp is less than 24 hours from now, do the following...
          if ($dayDiff == 0) {
              if ($diff < 5) {
                  return $justNow;
              } elseif ($diff < 60) {
                  return $diff . $secondsAgo;
              } elseif ($diff < 120) {
                  return $oneMinuteAgo;
              } elseif ($diff < 3600) {
                  return floor($diff / 60) . $minutesAgo;
              } elseif ($diff < 7200) {
                  return $oneHourAgo;
              } elseif ($diff < 86400 && $daySent !== $dayYesterday) {
                  return floor($diff / 3600) . $hoursAgo;
              }
          }
          // If the timestamp is over a day old, do the following...
          if ($daySent == $dayYesterday) {
              return $yesterday;
          } elseif ($dayDiff < 7) {
              return $dayDiff . $daysAgo;
          } elseif ($dayDiff == 7) {
              return $oneWeekAgo;
          } elseif ($dayDiff < (7 * 6)) {
              return ceil($dayDiff / 7) . $weeksAgo;
          } elseif ($dayDiff < 365) {
              return ceil($dayDiff / (365 / 12)) . $monthsAgo;
          } else {
              $years = round($dayDiff / 365);
              return($years != 1 ? $years . $moreThanAYear : $justAYear);
          }
      }
}
?>