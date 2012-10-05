<?php
session_start();
if(!isset($_SESSION['username']) or !isset($_SESSION['user_id']) or !isset($_GET['survey_id'])){
	header('Location: http://www.qualturk.com/?logout=1');
	die("ERROR");
}

include 'functions.php';

connectDB();
$query=sprintf("SELECT hit_id,`status`,time_created,date_returned,worker_id,guid FROM `hits` WHERE survey_id='%s' ORDER BY `status`,worker_id",
	mysql_real_escape_string($_GET['survey_id']));
$result = mysql_query($query);

$result || die(mysql_error());
if(!mysql_num_rows($result)){
	die("No records available for this Survey ID.");
}

echo '"hit_id","status","time_started","time_ended","worker_id","GUID"'."\n";
while($row = mysql_fetch_row($result)) {
  $comma = false;
  foreach ($row as $item) {
    #make it comma separated
    if ($comma) {
      echo ',';
    } else {
      $comma = true;
    }
    #quiote the quiotes
    $quoted = str_replace("\"", "\"\"", $item);

    #quiote the string
    echo "\"$quoted\"";
  }
    echo "\n";
}

?>