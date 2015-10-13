<?php

if($_GET["l"]!="c"){
	die("Invalid link.");
}

include 'functions.php';

connectDB();
$error = false;
$completionCode = false;
$timestamp = date('Y-m-d H:i:s');

if (isset($_GET['workerid']) and !empty($_GET['workerid']) and 
	isset($_GET['surveyid']) and !empty($_GET['surveyid'])) {
	$WID = mysql_real_escape_string($_GET['workerid']);
	$SID = mysql_real_escape_string($_GET['surveyid']);
	$GUID = getGUID();
	$result = mysql_query("SELECT worker_id FROM `hits` WHERE survey_id='$SID' and worker_id='$WID'");

	if (mysql_num_rows($result) == 0) { //if woker id not in DB, create entry
		
		$query = "INSERT INTO `hits` (survey_id, time_created, guid, worker_id, `status`) 
		          VALUES('$SID','$timestamp','$GUID','$WID','in_progress')";

		$result = mysql_query($query);

		if (!$result) {
			die('Can\'t insert data :' . mysql_error());
		}

		// send to survey
		header('Location: https://stanforduniversity.qualtrics.com/SE/?SID='.$SID.'&workerid='.$WID);

	} else { //if worker id is already in DB; throw duplicate error
		$error = "duplicate";
	}
	
} elseif (isset($_GET['partid']) and !empty($_GET['partid']) and 
		isset($_GET['surveyid']) and !empty($_GET['surveyid'])) {
	$WID = mysql_real_escape_string($_GET['partid']);
	$SID = mysql_real_escape_string($_GET['surveyid']);
	
	# Responses to final check questions
	$long = $_GET['long'];
	$comm = $_GET['comm'];

	# Get time spent and minimum required time
	$time = mysql_fetch_assoc(mysql_query(
		"SELECT TIMESTAMPDIFF(MINUTE, time_created, '$timestamp') AS `duration`, 
		(SELECT `min_time` FROM `surveys` WHERE survey_id='$SID') AS `minT` 
		FROM `hits` WHERE survey_id='$SID' AND worker_id='$WID'"
	));
	
	# Load definitive status
	$reload = mysql_fetch_assoc(mysql_query(
		"SELECT `status` FROM `hits` WHERE survey_id='$SID' AND worker_id='$WID'"
	));

	if ($reload['status'] == 'pre_problem' or 
		$reload['status'] == 'end_problem' or 
		$reload['status'] == 'time_problem') { 
		//if someone tries to reload the page to be clever, he gets the same message
		$error = $reload['status'];
	
	} elseif ($long == '' and $comm == '' and $reload['status'] != 'done') {
		$completionCode = "PRE0" . substr(getGUID(), 0, 6);
		$error = "pre_problem";
		mysql_query("UPDATE `hits` SET `status` = '$error', date_returned = '$timestamp', 
			guid = '$completionCode' WHERE survey_id = '$SID' and worker_id = '$WID'");

	} elseif ((
		strtolower($long) != "no answer" or 
		levenshtein("i read the instructions", strtolower($comm)) > 6
		) and $reload['status']!='done') {
		$completionCode = "POST0" . substr(getGUID(), 0, 5);
		$error = "end_problem";
		mysql_query("UPDATE `hits` SET `status` = '$error', date_returned = '$timestamp', 
			guid = '$completionCode' WHERE survey_id = '$SID' and worker_id = '$WID'");

	} elseif ($time['minT'] > 0 and 
		$time['duration'] < $time['minT'] and 
		$reload['status'] != 'done') {
		$completionCode = "POST0" . substr(getGUID(), 0, 5);
		$error = "time_problem";
		mysql_query("UPDATE `hits` SET `status` = '$error', date_returned = '$timestamp', 
			guid = '$completionCode' WHERE survey_id = '$SID' and worker_id = '$WID'");

	} else {
		$result = mysql_query("SELECT guid FROM `hits` WHERE survey_id = '$SID' and worker_id = '$WID'");

		if (mysql_num_rows($result) == 1) { //woker id not in DB
			mysql_query("UPDATE `hits` SET `status` = 'done', date_returned = '$timestamp' 
				WHERE survey_id='$SID' and worker_id='$WID'");

			$row = mysql_fetch_assoc($result);
			$completionCode = $row['guid'];

			$row = mysql_fetch_assoc(mysql_query(
				"SELECT `debrief` FROM `surveys` WHERE survey_id='$SID'"
			));
			$debrief = $row['debrief'];

		} else {
			$error = "invalid";		
		}
	}
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Consent</title>
	<link rel="stylesheet" href="style.css" type="text/css" />
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-32828578-1']);
	  _gaq.push(['_trackPageview']);
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
</head>
<body>
    <form name="continue" method="post" action="" id="continue">

    	<div id="wrap">
	        <div id="page">
	            <img alt="" src="header.gif" width="850" />
				<div id="main-content">
		        	<h1>End of Survey</h1>
					<hr /><br /><br />	
					<? if ($error == "duplicate") { ?>
						<h2 style="font-size: large">There is a r</h2>
		        		<p>You are unfortunately not eligible to participate, 
		        			because you have already started or completed this survey.<br />
		        			The instructions in the HIT clearly stated that 
		        			each person can only complete the HIT once.</p>

		        	<? } else { ?>

					<h2 style="font-size: large">Almost done!</h2>
					<p>To complete this HIT, you need to copy &amp; paste the 
						<strong>completion code</strong> into the text box on Mechanical Turk.</p>

						<? if ($completionCode) { ?>
			       			<p>Completion code: <strong><?=$completionCode?></strong></p>
						<? } ?>
						
						<br />
						
						<? if (!empty($debrief)) { ?>
							<br />
							<h2 style="font-size: large">Study Debriefing</h2>
							<p><small>
								<?=nl2br($debrief)?>
							</small></p>
							<br /><br />
						<? } ?>

						<p>Thank you!</p>

					<? } ?>
					<br /><br /><br /><br />
				</div>
				<div class="clear"></div>
	        </div>
	    </div>
    </form>
</body>
</html>
