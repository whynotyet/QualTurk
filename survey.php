<?php

if($_GET["l"]!="c"){
	die("Invalid link.");
}

include 'functions.php';

connectDB();
$error=false;
$completionCode=false;
$timestamp=date('Y-m-d H:i:s');

if(isset($_GET['workerid']) and !empty($_GET['workerid']) and isset($_GET['surveyid']) and !empty($_GET['surveyid'])){
	$WID = mysql_real_escape_string($_GET['workerid']);
	$SID = mysql_real_escape_string($_GET['surveyid']);
	$GUID = getGUID();
	$result=mysql_query("SELECT worker_id FROM `hits` WHERE survey_id='$SID' and worker_id='$WID'");
	if(mysql_num_rows($result)==0){ //woker id not in DB
		
		$query="INSERT INTO `hits` (survey_id,time_created,guid,worker_id,`status`) VALUES('$SID','$timestamp','$GUID','$WID','in_progress')";
		$result = mysql_query($query);
		if(!$result){
			die('Can\'t insert data :' . mysql_error());
		}
		header('Location: https://stanforduniversity.qualtrics.com/SE/?SID='.$SID.'&workerid='.$WID);

	}else{ //worker id IS in DB
		$error="duplicate";
	}
	
}elseif(isset($_GET['partid']) and !empty($_GET['partid']) and isset($_GET['surveyid']) and !empty($_GET['surveyid'])){
	$WID = mysql_real_escape_string($_GET['partid']);
	$SID = mysql_real_escape_string($_GET['surveyid']);
	$time=mysql_fetch_assoc(mysql_query("SELECT TIMESTAMPDIFF(MINUTE,time_created,'$timestamp') as `duration`, (select `min_time` FROM `surveys` WHERE survey_id='$SID') as `minT` FROM `hits` WHERE survey_id='$SID' and worker_id='$WID'"));
	$long = $_GET['long'];
	$comm = $_GET['comm'];
	
	$reload_q=mysql_query("SELECT `status` FROM `hits` WHERE survey_id='$SID' and worker_id='$WID'");
	$reload=mysql_fetch_assoc($reload_q);
	if($reload['status']=='pre_problem' or $reload['status']=='end_problem' or $reload['status']=='time_problem'){ 
		$error=$reload['status'];//if someone tries to reload the page to be clever, he gets the same message
	}elseif($long=='' and $comm=='' and $reload['status']!='done'){
		mysql_query("UPDATE `hits` SET `status`='pre_problem',date_returned='$timestamp' WHERE survey_id='$SID' and worker_id='$WID'");
		$error="pre_problem";
	}elseif(($long!="No answer" or levenshtein("I read the instructions",$comm)>6)  and $reload['status']!='done'){
		mysql_query("UPDATE `hits` SET `status`='end_problem',date_returned='$timestamp' WHERE survey_id='$SID' and worker_id='$WID'");
		$error="end_problem";
	}elseif( $time['minT']>0 and $time['duration']<$time['minT'] and $reload['status']!='done'){
		mysql_query("UPDATE `hits` SET `status`='time_problem',date_returned='$timestamp' WHERE survey_id='$SID' and worker_id='$WID'");
		$error="time_problem";
	}else{
		$result=mysql_query("SELECT guid FROM `hits` WHERE survey_id='$SID' and worker_id='$WID'");
		if(mysql_num_rows($result)==1){ //woker id not in DB
			$row=mysql_fetch_assoc($result);
			$completionCode = $row['guid'];
			mysql_query("UPDATE `hits` SET `status`='done',date_returned='$timestamp' WHERE survey_id='$SID' and worker_id='$WID'");
			$result=mysql_query("SELECT `debrief` FROM `surveys` WHERE survey_id='$SID'");
			$row=mysql_fetch_assoc($result);
			$debrief = $row['debrief'];
		}else{
			$error="invalid";		
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
		        	<h1>Stanford Survey</h1>
					<hr /><br /><br />	
					<?
					if($error){
		            	echo '<h2 style="font-size: large">Not eligible</h2>';
						if($error=="duplicate"){?>
		        			<p>We are sorry, but you are not eligible to participate, because you have already started or completed this survey.</p>
					<?	}elseif($error=="pre_problem"){?>
			       			<p>We are sorry, but you are not eligible for this study.</p>
							<p>You did not carefully read the questions during the pre-survey.</p>
							<p>This has disqualified you from this study.</p>
					<?	}elseif($error=="end_problem"){?>
		        			<p>We are sorry, but you are not eligible for this study.</p>
							<p>You did not carefully read the instructions during the survey. The final instructions directed you to do the following:</p>
							<p><small>You have almost completed the research survey and we appreciate your time and effort. However, we have to make sure that our data are valid and not biased. Specifically, we are interested in whether you actually take the time to read instructions closely; if not, our data based on your responses will be invalid. In order to demonstrate that you have read instructions, please select the option "No answer" for the next question that asks about the length of the study and simply write "I read instructions" in the box labeled "Any other comments or questions?" Thank you very much.</small></p>
							<p>These tasks were not done correctly. This has disqualified you from this study.</p>
					<?	}elseif($error=="time_problem"){?>
			       			<p>We are sorry, but you are not eligible for this study.</p>
							<p> Timely completion of the task is essential for this study. You took less than the absolute minimum task completion time (set to <?=$time['minT']?> minutes).</p>
							<p>This has disqualified you from this study.</p>
					<?	}
					}elseif($completionCode){ ?>			
			            <h2 style="font-size: large">Almost done!</h2>
			        	<p>You have answered all questions in the survey.<br /><br />To complete this HIT, please copy&amp;paste the <strong>completion code</strong> into the appropriate box on Mechanical Turk.</p>
						<p>Completion code: <strong><?=$completionCode?></strong></p>
						<br />
						<? if(!empty($debrief)){ ?>
							<br />
							<h2 style="font-size: large">Study debrief</h2>
							<p><small><?=nl2br($debrief)?></small></p>
						<? }else{ ?>
							<p>Thank you!</p>
						<? } ?>
						<br />
					<? } ?>
					<br /><br /><br /><br />
				</div>
				<div class="clear"></div>
	        </div>
	    </div>
    </form>
</body>
</html>
