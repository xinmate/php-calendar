<?php
include_once("calendar.inc");
include_once("config.inc");

top();

if(empty($_GET['day'])) $day = date("j");
else $day = $_GET['day'];

if(empty($_GET['month'])) $month = date("n");
else $month = $_GET['month'];

if(empty($_GET['year'])) $year = date("Y");
else $year = $_GET['year'];

$tablename = date('Fy', mktime(0,0,0,$month,1,$year));
$monthname = date('F', mktime(0,0,0,$month,1,$year));
$lastmonthname = $monthname;
$nextmonthname = $monthname;

$lasttime = mktime(0,0,0,$month,$day-1,$year);
$lastday = date("j", $lasttime);
$lastmonth = date("n", $lasttime);
$lastyear = date("Y", $lasttime);
$lastmonthname = date("F", $lasttime);

$nexttime = mktime(0,0,0,$month,$day+1,$year);
$nextday = date("j", $nexttime);
$nextmonth = date("n", $nexttime);
$nextyear = date("Y", $nexttime);
$nextmonthname = date('F', $nexttime);

echo <<<END
<table id="navbar">
  <thead>
  <tr>
    <th colspan="3">$day $monthname $year</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>
      <a href="display.php?month=$lastmonth&amp;day=$lastday&amp;year=$lastyear">$lastmonthname $lastday</a>
    </td>
	<td>
      <a href=".?month=$month&amp;day=$day&amp;year=$year">Back to Calendar</a>
    </td>
	<td>
      <a href="display.php?month=$nextmonth&amp;day=$nextday&amp;year=$nextyear">$nextmonthname $nextday</a>
    </td>
  </tr>
  </tbody>
</table>
<form action="operate.php">
<table id="display">
  <colgroup>
    <col width="48" />
  </colgroup>
  <colgroup>
    <col width="96" />
    <col width="160" />
    <col width="160" />
    <col width="128" />
  </colgroup>
  <thead>
  <tr>
    <th>Select</th>
    <th>Username</th>
    <th>Time</th>
    <th>Duration</th>
    <th>Subject</th>
    <th>Description</th>
  </tr>
  </thead>
  <tfoot>
  <tr>
    <td colspan="6">
      <input type="hidden" name="day" value="$day" />
      <input type="hidden" name="month" value="$month" />
      <input type="hidden" name="year" value="$year" />
      <input type="submit" name="action" value="Delete Selected" />
      <input type="submit" name="action" value="Modify Selected" />
    </td>
  </tr>
  </tfoot>
  <tbody>
END;

$database = mysql_connect($mysql_hostname, $mysql_username, $mysql_password)
     or die("could not connect to database");
mysql_select_db($mysql_database)
     or die("could not select database");

$query = "SELECT * FROM $mysql_tablename WHERE stamp >= \"$year-$month-$day 00:00:00\" AND stamp <= \"$year-$month-$day 23:59:59\" ORDER BY stamp";
$result = mysql_query($query)
     or die("could not run query");

while ($row = mysql_fetch_array($result)) {
    $name = stripslashes($row['username']);
    $subject = stripslashes($row['subject']);
    $desc = nl2br(stripslashes($row['description']));
    $typeofevent = $row['eventtype'];
    $temp_time = strtotime($row['stamp']);
    if($typeofevent == 3) $time = date("j F Y, ??:?? ??", $temp_time);
    else if($typeofevent == 2) $time = date("j F Y, \F\U\L\L \D\A\Y", $temp_time);
    else $time = date("j F Y, h:i A", $temp_time);
    $durtime = strtotime($row['duration']);
    $durmin = date("i", $durtime) - date("i", $temp_time);
    $durhr = date("H", $durtime) - date("H", $temp_time);
    $durday = date("j", $durtime) - date("j", $temp_time);
    $durmon = date("n", $durtime) - date("n", $temp_time);
    if($durmin < 0) {
        $durmin = $durmin + 60;
        $durhr = $durhr - 1;
    }
    if($durhr < 0) {
        $durhr = $durhr + 24;
        $durday = $durday - 1;
    }
    if($durmon > 0) $durday = $durday + date("t", $temp_time);
    if($typeofevent == 2) $temp_dur = "FULL DAY";
    else $temp_dur = "$durday days, $durhr hours, $durmin minutes";
    if(isold()) {
        if(empty($name)) $name = "&nbsp;";
        if(empty($subject)) $subject = "&nbsp";
        if(empty($desc)) $desc = "&nbsp;";
    }
    echo "
  <tr>
    <td><input type=\"radio\" name=\"id\" value=\"$row[id]\" /></td>
	  <td>$name</td>
    <td>$time</td>
    <td>$temp_dur</td>
	  <td>$subject</td>
    <td class=\"description\">$desc</td>
  </tr>";
}

echo "
  </tbody>
</table>
<div>
  <a class=\"box\" href=\"operate.php?month=$month&amp;year=$year&amp;day=$day&amp;action=Add+Item\">Add Item</a>
</div>
</form>";

bottom();
?>